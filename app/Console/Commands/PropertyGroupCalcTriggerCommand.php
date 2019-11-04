<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\Client;
use Carbon\Carbon;

/**
 * Class PropertyGroupCalcTriggerCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class PropertyGroupCalcTriggerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * You may be tempted to refer to Client::PROPERTY_GROUP_CALC_STATUS_WAITING here
     * but it messes up code generation
     */
    protected $signature = 'waypoint:trigger:property_group_calc  
                        {--client_ids= : Comma separated list of client IDs or \'All\'}
                        {--property_group_ids=  : Comma separated list of property group IDs}
                        {--property_group_calc_status=waiting : \'waiting\' or \'idle\' }
                        {--force_recalc=0 : Force recalc - Values are 0 and 1}
                        {--first_time_calc=0 : Force first_time_calc - Values are 0 and 1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the property group calc trigger flag ';

    /**
     * PropertyGroupCalcTriggerCommand constructor.
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
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        if ( ! config('waypoint.group_calc_on', false))
        {
            $this->alert('GROUP_CALC_ON is off');
            exit;
        }
        if ( ! in_array($this->option('property_group_calc_status'), Client::$property_group_calc_status_values))
        {
            $this->alert('invalid property_group_calc_status');
            exit;
        }

        $ClientObjArr = $this->getClientsFromArray($this->option('client_ids'));

        $property_group_ids = null;
        if ($this->hasOption('property_group_ids'))
        {
            $property_group_ids = $this->option('property_group_ids');
        }
        if ($property_group_ids && $ClientObjArr->count() !== 1)
        {
            $this->alert('invalid property_group_ids. If property_group_ids is indicated, client_ids must be 1 and only 1 client');
            exit;
        }

        $property_group_force_recalc          = $this->option('force_recalc');
        $property_group_force_first_time_calc = $this->option('first_time_calc');
        if ($property_group_ids && $property_group_force_first_time_calc)
        {
            $this->alert('[FAILURE] you cannot both force_recalc and force_peer_group_recalc');
            exit;
        }
        if ($property_group_force_recalc && $property_group_force_first_time_calc)
        {
            $this->alert('[FAILURE] you cannot both first_time_calc and property_group_ids');
            exit;
        }

        /**
         * flip clients to Client::PROPERTY_GROUP_CALC_STATUS_WAITING
         */
        /** @var Client $ClientObj */
        foreach ($ClientObjArr as $ClientObj)
        {
            if (
                $ClientObj->getConfigValue('FEATURE_GROUP_CALC') === false
                ||
                $ClientObj->getConfigValue('FEATURE_GROUP_CALC') === 'false'
            )
            {
                $this->alert('*************************************');
                $this->alert($ClientObj->name  . ' has group calc turned off so will be skipped');
                $this->alert('*************************************');
                continue;
            }
            if (
                $ClientObj->property_group_calc_status == Client::PROPERTY_GROUP_CALC_STATUS_WAITING &&
                (
                    $ClientObj->property_group_force_recalc ||
                    $ClientObj->property_group_force_first_time_calc
                )
            )
            {
                $this->alert('[WARNING] Client ' . $ClientObj->id . ' is already triggered and is thus skipped');
                continue;
            }
            if ($ClientObj->id == 1)
            {
                $this->alert('[WARNING] Client ' . $ClientObj->id . ' is the Dummy Client and is thus skipped');
                continue;
            }
            if ( ! $ClientObj->client_id_old)
            {
                $this->alert('[WARNING] Client ' . $ClientObj->id . ' has no client_id_old and is thus skipped');
                continue;
            }

            /**
             * only update if we're flipping it here, in other words, this should never switch
             * to false
             */
            $this->ClientRepositoryObj->update(
                [
                    'property_group_calc_status'                   => Client::PROPERTY_GROUP_CALC_STATUS_WAITING,
                    'property_group_force_recalc'                  => $property_group_force_recalc ?: $ClientObj->property_group_force_recalc,
                    'property_group_force_first_time_calc'         => $property_group_force_first_time_calc ?: $ClientObj->property_group_force_first_time_calc,
                    'property_group_force_calc_property_group_ids' => $property_group_ids,
                    'property_group_calc_last_requested'           => Carbon::now(),
                ],
                $ClientObj->id
            );
        }
    }
}
