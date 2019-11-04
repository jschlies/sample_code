<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\AdvancedVarianceLineItemCreatedEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
//use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use Carbon\Carbon;
use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshAdvancedVariancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:advance_variance:refresh  
                        {--client_id= : client_id} 
                        {--property_id= : property_id} 
                        {--year= : year - YYYY} 
                        {--month= : month - MM} 
                        {--job_only=0 : job_only}
                        {--only_locked_reports=0 : refresh only locked reports} 
                        {--refresh_reviewers=0 : refresh reviewers when REFRESHING an advance variance report} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh advance variances per client and (optionally) per property. If property is given the month and year (used together only) can be used to specify a report';

    /**
     * AlterClientConfigCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        /**
         * please leave this here for use in testing
         */
        //        AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
        parent::handle();

        $this->loadAllRepositories(true);

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }
        if ( ! $property_id_arr = explode(',', $this->option('property_id')))
        {
            $property_id_arr = null;
        }
        if ( ! $year = (int) $this->option('year'))
        {
            $year = null;
        }
        if ( ! $month = (int) $this->option('month'))
        {
            $month = null;
        }
        if ($month || $year)
        {
            if ( ! ($property_id_arr && $month && $year))
            {
                throw new GeneralException("invalid property_id, month, year combo", 404);
            }
        }

        if ( ! $client_id and $property_id_arr)
        {
            throw new GeneralException("no client_id / property_id found", 404);
        }

        $job_only            = (boolean) $this->option('job_only');
        $only_locked_reports = (boolean) $this->option('only_locked_reports');
        $refresh_reviewers   = (boolean) $this->option('refresh_reviewers');

        if ($property_id_arr == null)
        {
            $this->processRefreshAdvancedVariancesCommand(
                $client_id,
                null,
                $month,
                $year,
                $job_only,
                [
                    'only_locked_reports' => $only_locked_reports,
                    'refresh_reviewers'   => $refresh_reviewers,
                ]
            );
        }
        else
        {
            foreach ($property_id_arr as $property_id)
            {
                $this->processRefreshAdvancedVariancesCommand(
                    $client_id,
                    $property_id,
                    $month,
                    $year,
                    $job_only,
                    [
                        'only_locked_reports' => $only_locked_reports,
                        'refresh_reviewers'   => $refresh_reviewers,
                    ]
                );
            }
        }
        return true;
    }

    /**
     * @param null $client_id
     * @param null $property_id
     * @param null $month
     * @param null $year
     * @param $job_only
     * @param $options
     * @throws GeneralException
     */
    public function processRefreshAdvancedVariancesCommand(
        $client_id = null,
        $property_id = null,
        $month = null,
        $year = null,
        $job_only = false,
        $options = []
    ) {
        $only_locked_reports = isset($options['only_locked_reports']) && (bool) $options['only_locked_reports'];
        $refresh_reviewers   = isset($options['refresh_reviewers']) && (bool) $options['refresh_reviewers'];

        if ($client_id && ! $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj
                ->with('properties')
                ->find($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }

            $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
                ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings')
                ->with('reportTemplate.calculatedFields')
                ->findWhereIn('property_id', $ClientObj->properties->pluck('id')->toArray())
                ->filter(function ($AdvancedVarianceObj) use ($year, $month)
                {
                    return $year && $month
                        ? $AdvancedVarianceObj->as_of_month == $month && $AdvancedVarianceObj->as_of_year == $year
                        : true;
                })
                ->filter(function ($AdvancedVarianceObj) use ($only_locked_reports)
                {
                    return $only_locked_reports
                        ? $AdvancedVarianceObj->locked_date != null
                        : true;
                });
        }
        elseif ($client_id && $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }
            if ($month && $year)
            {
                $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
                    ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                    ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings')
                    ->with('reportTemplate.calculatedFields')
                    ->findWhere(
                        [
                            'property_id' => $property_id,
                            'as_of_month' => $month,
                            'as_of_year'  => $year,
                        ]
                    )
                    ->filter(
                        function ($AdvancedVarianceObj) use ($only_locked_reports)
                        {
                            return $only_locked_reports
                                ? $AdvancedVarianceObj->locked_date != null
                                : true;
                        }
                    );
            }
            else
            {
                $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
                    ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                    ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings')
                    ->with('reportTemplate.calculatedFields')
                    ->findWhereIn('property_id', [$property_id])
                    ->filter(
                        function ($AdvancedVarianceObj) use ($only_locked_reports)
                        {
                            return $only_locked_reports
                                ? $AdvancedVarianceObj->locked_date != null
                                : true;
                        }
                    );
            }
        }
        else
        {
            $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj->all();
        }

        /** @var AdvancedVarianceRepository $this ->AdvancedVarianceRepositoryObj */
        /** @var AdvancedVariance $AdvancedVarianceObj */
        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($AdvancedVarianceObjArr as $AdvancedVarianceObj)
        {
            try
            {
                if ($job_only)
                {
                    $this->alert('Calling AdvancedVarianceLineItemRefresh. client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' advanced_variance_id = ' . $AdvancedVarianceObj->id . ' for year/month ' . $year . '' . $month . ' at ' . Carbon::now()
                                                                                                                                                                                                                                                                                       ->format('Y-m-d H:i:s'));
                    /**
                     * @todo See HER-3693
                     */

                    $AdvancedVarianceLineItemObj             = $AdvancedVarianceObj->advancedVarianceLineItems->first();
                    $options['event_trigger_message']        = '';
                    $options['event_trigger_id']             = waypoint_generate_uuid();
                    $options['event_trigger_class']          = self::class;
                    $options['event_trigger_class_instance'] = get_class($this);
                    $options['event_trigger_object_class']   = get_class($AdvancedVarianceLineItemObj);
                    $options['event_trigger_absolute_class'] = __CLASS__;
                    $options['event_trigger_file']           = __FILE__;
                    $options['event_trigger_line']           = __LINE__;

                    $this->post_job_to_queue(
                        [
                            'advanced_variance_id'           => $AdvancedVarianceLineItemObj->advancedVariance->id,
                            'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                            'as_of_month'                    => $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                            'as_of_year'                     => $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                            'recipient_id_arr'               => $AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                        ],
                        App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                        config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
                    );
                    continue;
                }
                elseif ($AdvancedVarianceObj->locked())
                {
                    /**
                     * @see HER-4052
                     */
                    $this->warn("Advanced Variance #$AdvancedVarianceObj->id is locked, skipping");

                    continue;
                }
                else
                {
                    $this->alert('Recreate. client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' advanced_variance_id = ' . $AdvancedVarianceObj->id . ' for year/month ' . $year . '/' . $month . ' at ' . Carbon::now()
                                                                                                                                                                                                                                                    ->format('Y-m-d H:i:s'));

                    /**
                     * since $this->AdvancedVarianceRepositoryObj optimistically  loads
                     * LedgerVarianceDataArr, we need to clear it here so line items do not 'leak'
                     * from one report top the next
                     */
                    $this->AdvancedVarianceRepositoryObj->setSuppressEvents(true);
                    /**
                     * note that even if setSuppressEvents=true, AdvancedVarianceLineItemUpdatedEvent
                     * is triggered
                     */
                    $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create(
                        [
                            "client_id"          => $AdvancedVarianceObj->property->client_id,
                            "period_type"        => $AdvancedVarianceObj->period_type,
                            "property_id"        => $AdvancedVarianceObj->property_id,
                            "report_template_id" => $AdvancedVarianceObj->report_template_id,
                            "as_of_month"        => $AdvancedVarianceObj->as_of_month,
                            "as_of_year"         => $AdvancedVarianceObj->as_of_year,
                            "trigger_mode"       => $AdvancedVarianceObj->trigger_mode,
                        ]
                    );

                    if ($refresh_reviewers)
                    {
                        $this->AdvancedVarianceRepositoryObj->refresh_reviewers($AdvancedVarianceObj);
                    }

                    $this->alert('Finished recreating. client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' advanced_variance_id = ' . $AdvancedVarianceObj->id . ' ' . Carbon::now()
                                                                                                                                                                                                                ->format('Y-m-d H:i:s'));

                    /**
                     * note that even if setSuppressEvents=true, AdvancedVarianceLineItemUpdatedEvent
                     * is triggered
                     */
                    $this->alert('Calling AdvancedVarianceLineItemCreatedEvent. client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' advanced_variance_id = ' . $AdvancedVarianceObj->id . ' ' . Carbon::now()
                                                                                                                                                                                                                                         ->format('Y-m-d H:i:s'));
                    $AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems->first();

                    $this->post_job_to_queue(
                        [
                            'advanced_variance_id'           => $AdvancedVarianceLineItemObj->advancedVariance->id,
                            'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                            'as_of_month'                    => $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                            'as_of_year'                     => $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                            'recipient_id_arr'               => $AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                        ],
                        App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                        config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
                    );
                }
            }
            catch (GeneralException $e)
            {
                $this->alert('Unable to re-create.  $AdvancedVarianceObj->id = ' . $AdvancedVarianceObj->id . ' because ' . $e->getMessage());
                $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' month/year ' . $AdvancedVarianceObj->as_od_month . '/' . $AdvancedVarianceObj->as_of_year . ' has been rolled back');

                throw $e;
            }
            catch (Exception $e)
            {
                $this->alert('Unable to re-create.  $AdvancedVarianceObj->id = ' . $AdvancedVarianceObj->id . ' because ' . $e->getMessage());
                $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' month/year ' . $AdvancedVarianceObj->as_od_month . '/' . $AdvancedVarianceObj->as_of_year . ' has been rolled back');

                throw new GeneralException($e->getMessage(), 500, $e);
            }
            catch (Throwable $e)
            {
                $this->alert('Unable to re-create.  $AdvancedVarianceObj->id = ' . $AdvancedVarianceObj->id . ' because ' . $e->getMessage());
                $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $AdvancedVarianceObj->property_id . ' month/year ' . $AdvancedVarianceObj->as_od_month . '/' . $AdvancedVarianceObj->as_of_year . ' has been rolled back');

                $e = new FatalThrowableError($e);
                throw new GeneralException($e->getMessage(), 500, $e);
            }
        }

        /**
         * note that AdvancedVarianceLineItemCreatedEvent is specifically called in
         * $this->AdvancedVarianceRepositoryObj->create()
         */
        $this->alert('Calling PreCalcPropertyGroupsEvent. client_id = ' . $client_id . ' ' . Carbon::now()->format('Y-m-d H:i:s'));
        event(
            new PreCalcPropertyGroupsEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ClientObj),
                    'event_trigger_object_class_id' => $ClientObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'property_groups' => [
                                'unique_advanced_variance_dates_property_group_',
                                'AdvancedVarianceSummaryByPropertyGroupId_',
                            ],
                        ],
                ]
            )
        );

        $this->alert('Calling PreCalcPropertiesEvent. client_id = ' . $client_id);
        event(
            new PreCalcPropertiesEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ClientObj),
                    'event_trigger_object_class_id' => $ClientObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'properties' => [
                                '^advancedVarianceSummaries_property_.*',
                            ],
                        ],
                ]
            )
        );
    }
}
