<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class ClearDormantUsersEvent extends EventBase
{
    use SerializesModels;

    /**
     * @param Client $ClientObj
     * @throws GeneralException
     */
    public function __construct(Client $ClientObj, $options = [])
    {
        parent::__construct($ClientObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $ClientObj->id;

        if (isset($options['launch_job_user_id_arr']))
        {
            $this->model_arr['launch_job_user_id_arr'] = $options['launch_job_user_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_user_id_arr'] =
                $ClientObj
                    ->users
                ->whereNotIn('active_status', [User::ACTIVE_STATUS_INACTIVE])
                ->whereNotIn('user_invitation_status', [User::USER_INVITATION_STATUS_PENDING])
                ->sortByDesc('last_login_date')
                ->pluck('id')->toArray();
        }
        if (isset($options['launch_job_property_id_arr']))
        {
            $this->model_arr['launch_job_property_id_arr'] = $options['launch_job_property_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_id_arr'] = $ClientObj->properties->pluck('id')->toArray();
        }
        if (isset($options['launch_job_property_group_id_arr']))
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $options['launch_job_property_group_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $ClientObj->propertyGroups->pluck('id')->toArray();
        }

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] = $options['wipe_out_list'];
        }
        else
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'clients' =>
                        [
                            'relatedUserTypes_client_',

                        ],
                    'users'   =>
                        [
                            'assetTypesOfProperties_user_.*',
                            'accessible_property_arr_user_.*',
                            'standardAttributesOfProperties_user_.*',
                            'customAttributesOfProperties_user_.*',
                            'AccessiblePropertyObjFormattedArr_user_.*',
                            'accessiblePropertyGroups_user_.*',
                            'user_accessable_property_id_arr_.*',
                        ],
                ];
        }
    }
}