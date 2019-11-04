<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Jobs\AdvancedVarianceLockedNotificationJob;
use App\Waypoint\Models\AdvancedVariance;
use Carbon\Carbon;

class LockAdvancedVariancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:advanced_variance:lock
                        {--client_id= : client_id} 
                        {--property_id= : Comma separated property_id list. --property_id=1992 or --property_id=8823,99283,28839} 
                        {--year= : year - YYYY} 
                        {--month= : month - MM} 
                        {--user_id= : User who is locking the advanced variances}
                        {--notify : Flag to trigger Advanced Variance Lock Notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Locks all the advanced variances per client and for property (optional) where there\'s no flagged line items, this command will ignore the variances with forecasting trigger mode';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $user_id         = $this->option('user_id') ? (int) $this->option('user_id') : null;
        $client_id       = $this->option('client_id') ? (int) $this->option('client_id') : null;
        $property_id_arr = $this->option('property_id') ? explode(',', $this->option('property_id')) : [];
        $month           = $this->option('month') ? $this->option('month') : null;
        $year            = $this->option('year') ? $this->option('year') : null;
        $notify          = (bool) $this->option('notify');

        if ($user_id === null)
        {
            throw new GeneralException('Please provide a locker --user_id parameter');
        }
        if ($month !== null && $year === null)
        {
            throw new GeneralException("Please provide the --year parameter", 400);
        }
        if ($client_id === null)
        {
            throw new GeneralException("Please provide --client_id", 404);
        }

        $this->processAdvancedVarianceLocking($user_id, $client_id, $property_id_arr, $month, $year, $notify);
    }

    private function processAdvancedVarianceLocking(
        $user_id,
        $client_id = null,
        $property_id_arr = [],
        $month = null,
        $year = null,
        $notify = false
    ) {
        $AdvancedVarianceObjArr = null;

        if (count($property_id_arr) == 0)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties')->find($client_id))
            {
                throw new GeneralException("Invalid --client_id parameter", 500);
            }

            $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
                ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings')
                ->with('reportTemplate.calculatedFields')
                ->findWhereIn('property_id', $ClientObj->properties->pluck('id')->toArray());

        }
        elseif (count($property_id_arr) > 0)
        {
            $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
                ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings')
                ->with('reportTemplate.calculatedFields')
                ->findWhereIn('property_id', $property_id_arr);
        }

        if ( ! $AdvancedVarianceObjArr)
        {
            throw new GeneralException('Failed to find AdvancedVariance with the provided params', 400);
        }

        $AdvancedVarianceObjArr = $AdvancedVarianceObjArr->where('trigger_mode', '<>', AdvancedVariance::TRIGGER_MODE_FORECAST)
                                                         ->where('advanced_variance_status', '<>', AdvancedVariance::ACTIVE_STATUS_LOCKED)
                                                         ->filter(function ($AdvancedVarianceObj) use ($year, $month)
                                                         {
                                                             if ($month === null && $year !== null)
                                                             {
                                                                 return $AdvancedVarianceObj->as_of_year == $year;
                                                             }
                                                             elseif ($month !== null && $year !== null)
                                                             {
                                                                 return $AdvancedVarianceObj->as_of_year == $year && $AdvancedVarianceObj->as_of_month == $month;
                                                             }
                                                         });

        if ($AdvancedVarianceObjArr->count() == 0)
        {
            $this->warn('No variances to lock with the provided parameters');
            return true;
        }

        $locked_count = 0;
        /**
         * @var AdvancedVariance $AdvancedVarianceObj
         */
        /** @noinspection PhpUndefinedMethodInspection */

        foreach ($AdvancedVarianceObjArr as $AdvancedVarianceObj)
        {
            $advanced_variance_line_items_count = $AdvancedVarianceObj->advancedVarianceLineItems->count();
            $non_flagged_line_items_count       =
                $AdvancedVarianceObj->advancedVarianceLineItems()
                                    ->where(
                                        [
                                            ['flagged_via_policy', '=', 0],
                                            ['flagged_manually', '=', 0],
                                        ])
                                    ->count();

            if ($non_flagged_line_items_count == $advanced_variance_line_items_count)
            {
                $input = [
                    'locker_user_id'           => $user_id,
                    'locked_date'              => Carbon::now()->format('Y-m-d H:i:s'),
                    'advanced_variance_status' => AdvancedVariance::ACTIVE_STATUS_LOCKED,
                ];

                $this->AdvancedVarianceRepositoryObj->update($input, $AdvancedVarianceObj->id);
                $this->info("Advanced Variance #$AdvancedVarianceObj->id locked");
                $locked_count++;
                if ($notify === true)
                {
                    $this->post_job_to_queue(
                        [
                            'advanced_variance_id'           => $AdvancedVarianceObj->advancedVariance->id,
                            'advanced_variance_line_item_id' => $AdvancedVarianceObj->id,
                            'as_of_month'                    => $AdvancedVarianceObj->advancedVariance->as_of_month,
                            'as_of_year'                     => $AdvancedVarianceObj->advancedVariance->as_of_year,
                            'recipient_id_arr'               => $AdvancedVarianceObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                            'commetor_display_name'          => $this->getCurrentLoggedInUserObj()->getDisplayName(),
                            'commetor_text'                  => $input['comment'],
                        ],
                        AdvancedVarianceLockedNotificationJob::class,
                        config('queue.queue_lanes.AdvancedVarianceLockedNotification', false)
                    );
                }
            }
        }

        $this->info('Advanced Variance Locked: ' . $locked_count);

    }
}
