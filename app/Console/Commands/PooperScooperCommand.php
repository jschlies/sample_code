<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use DB;

/**
 * Class RefreshAllPropertyAccessListsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class PooperScooperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:pooper_scooper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the various all soiled objects';

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
        parent::handle();

        $this->ProcessPooperScooper();

        return true;
    }

    public function ProcessPooperScooper()
    {
        $resultObjArr = DB::select(
            DB::raw(
                "
                    SELECT * FROM pre_calc_status where is_soiled
                "
            )
        );

        foreach ($resultObjArr as $resultObj)
        {
            if ($resultObj->client_id && $ClientObj = Client::find($resultObj->client_id))
            {
                event(
                    new PreCalcClientEvent(
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
                            'wipe_out_list'                =>
                                [
                                    'clients' => ['skip-soiling'],
                                ],
                        ]
                    )
                );
            }
            if ($resultObj->property_id && $PropertyObj = Property::find($resultObj->property_id))
            {
                event(
                    new PreCalcPropertiesEvent(
                        $PropertyObj->client,
                        [
                            'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                            'event_trigger_id'              => waypoint_generate_uuid(),
                            'event_trigger_class'           => self::class,
                            'event_trigger_class_instance'  => get_class($this),
                            'event_trigger_object_class'    => get_class($PropertyObj),
                            'event_trigger_object_class_id' => $PropertyObj->id,
                            'event_trigger_absolute_class'  => __CLASS__,
                            'event_trigger_file'            => __FILE__,
                            'event_trigger_line'            => __LINE__,
                            'wipe_out_list'                 =>
                                [
                                    'properties' => ['skip-soiling'],
                                ],
                            'launch_job_property_id_arr'    => [$PropertyObj->id],
                        ]
                    )
                );
            }
            if ($resultObj->property_group_id && $PropertyGroupObj = PropertyGroup::find($resultObj->property_group_id))
            {
                event(
                    new PreCalcPropertyGroupsEvent(
                        $PropertyGroupObj->client,
                        [
                            'event_trigger_message'            => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                            'event_trigger_id'                 => waypoint_generate_uuid(),
                            'event_trigger_class'              => self::class,
                            'event_trigger_class_instance'     => get_class($this),
                            'event_trigger_object_class'       => get_class($PropertyGroupObj),
                            'event_trigger_object_class_id'    => $PropertyGroupObj->id,
                            'event_trigger_absolute_class'     => __CLASS__,
                            'event_trigger_file'               => __FILE__,
                            'event_trigger_line'               => __LINE__,
                            'wipe_out_list'                    =>
                                [
                                    'property_groups' => ['skip-soiling'],
                                ],
                            'launch_job_property_group_id_arr' => [$PropertyGroupObj->id],
                        ]
                    )
                );
            }
            if ($resultObj->user_id && $UserObj = User::find($resultObj->user_id))
            {
                event(
                    new PreCalcUsersEvent(
                        $UserObj->client,
                        [
                            'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                            'event_trigger_id'              => waypoint_generate_uuid(),
                            'event_trigger_class'           => self::class,
                            'event_trigger_class_instance'  => get_class($this),
                            'event_trigger_object_class'    => get_class($UserObj),
                            'event_trigger_object_class_id' => $UserObj->id,
                            'event_trigger_absolute_class'  => __CLASS__,
                            'event_trigger_file'            => __FILE__,
                            'event_trigger_line'            => __LINE__,
                            'wipe_out_list'                 =>
                                [
                                    'users' => ['skip-soiling'],
                                ],
                            'launch_job_user_id_arr'        => [$UserObj->id],
                        ]
                    )
                );
            }
        }
    }
}