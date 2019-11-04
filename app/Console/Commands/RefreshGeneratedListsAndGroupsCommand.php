<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Models\Client;

/**
 * Class RefreshAllPropertyAccessListsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshGeneratedListsAndGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:refresh_generated_lists_and_groups  
                        {--client_ids= : Comma separated list client IDs or \'All\' } 
                        {--soil_all_objects=1 : Default=true } ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the various all generated list(s)';

    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messses with code generator
         */
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('---  This job can run for several minutes for large clients or all clients  ----------------------------');
        $this->alert('---  This job can be killed at anytime if so desired ---------------------------------------------------');
        $this->alert('--------------------------------------------------------------------------------------------------------');
        parent::handle();

        $soil_all_objects = true;
        if ($this->hasOption('soil_all_objects'))
        {
            $soil_all_objects = $this->option('soil_all_objects');
        }
        $this->RefreshGeneratedListsAndGroups($this->option('client_ids'), $soil_all_objects);

        return true;
    }

    public function RefreshGeneratedListsAndGroups($client_ids_string, $soil_all_objects)
    {
        if ($soil_all_objects)
        {
            $wipe_out_list = [];
        }
        else
        {
            $wipe_out_list = ['skip-soiling'];
        }

        /** @var Client $ClientObj */
        foreach ($this->getClientsFromArray($client_ids_string) as $ClientObj)
        {
            if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
            {
                continue;
            }

            event(
                new CalculateVariousPropertyListsEvent(
                    $ClientObj,
                    [
                        'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ClientObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );

            if (
                $ClientObj->suppress_pre_calc_events() ||
                $ClientObj->suppress_pre_calc_usage()
            )
            {
                $this->alert('Client ' . $ClientObj->name . ' not processed. suppress_pre_calc_events or suppress_pre_calc_usage is true');
                continue;
            }

            /**
             * these soil the pre_calc's in question
             * these observes the SUPPRESS_PRE_CALC_EVENTS. however
             * if set to true, pre-calcs are
             * wiped out but no job is launched
             */
            event(
                new PreCalcClientEvent(
                    $ClientObj,
                    [
                        'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
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
                                'clients' => $wipe_out_list,
                            ],
                    ]
                )
            );
            event(
                new PreCalcUsersEvent(
                    $ClientObj,
                    [
                        'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
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
                                'users' => $wipe_out_list,
                            ],
                    ]
                )
            );
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
                                'properties' => $wipe_out_list,
                            ],
                    ]
                )
            );
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
                                'property_groups' => $wipe_out_list,
                            ],
                    ]
                )
            );
        }
    }
}