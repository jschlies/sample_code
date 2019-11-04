<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\AdvancedVarianceRepository;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class TriggerAdvancedVariancesJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:advance_variance:trigger_jobs  
                        {--client_id= : client_id} 
                        {--property_id= : property_id} 
                        {--year= : year} 
                        {--month= : month} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger advance variances jobs per client and (optionally) per property. If property is given the month and year (used together only) can be used to specify a report';

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
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }
        if ( ! $property_id = $this->option('property_id'))
        {
            $property_id = null;
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
            if ( ! ($property_id && $month && $year))
            {
                throw new GeneralException("invalid property_id, month, year combo", 500);
            }
        }
        if ( ! $client_id and $property_id)
        {
            throw new GeneralException("no client_id found", 500);
        }
        $this->processTriggerAdvancedVarianceJobsCommand($client_id, $property_id, $month, $year);
        return true;
    }

    /**
     * @param null $client_id
     * @param null $property_id
     * @param null $month
     * @param null $year
     * @throws GeneralException
     */
    public function processTriggerAdvancedVarianceJobsCommand($client_id = null, $property_id = null, $month = null, $year = null)
    {
        if ($client_id && ! $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->findWithoutFail($client_id))
            {
                throw new GeneralException("No client_id found", 500);
            }
            $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj->findWhereIn('property_id', $ClientObj->properties->pluck('id')->toArray());
        }
        elseif ($client_id && $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->findWithoutFail($client_id))
            {
                throw new GeneralException("No client_id found", 500);
            }
            if ($month && $year)
            {
                $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj->findWhere(
                    [
                        'property_id' => $property_id,
                        'as_of_month' => $month,
                        'as_of_year'  => $year,
                    ]
                );
            }
            else
            {
                $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj->findWhereIn('property_id', [$property_id]);
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
            $this->alert('Placing Advance Variance report on job queue property_id=' . $AdvancedVarianceObj->property_id . ' ' . $AdvancedVarianceObj->as_of_month . '/' . $AdvancedVarianceObj->as_of_year);
            if ($AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems->first())
            {
                $this->post_job_to_queue(
                    [
                        'advanced_variance_id'           => $AdvancedVarianceLineItemObj->advancedVariance->id,
                        'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                        'as_of_month'                    => $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                        'as_of_year'                     => $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                    ],
                    App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                    config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
                );
            }
            else
            {
                /**
                 * delete empty $AdvancedVarianceObj
                 */
                $this->AdvancedVarianceRepositoryObj->delete($AdvancedVarianceObj->id);
            }
            $this->alert('Finished placing Advance Variance report on job queue');
        }
    }
}
