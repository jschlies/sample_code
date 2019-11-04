<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PropertyGroupDeletedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param PropertyGroup $PropertyGroupObj
     * @throws GeneralException
     */
    public function __construct(PropertyGroup $PropertyGroupObj, $options = [])
    {
        parent::__construct($PropertyGroupObj, $options, self::class, get_class($this));

        $this->model_arr['property_group_id'] = $PropertyGroupObj->id;
        $this->model_arr['client_id']         = $PropertyGroupObj->client_id;

        if (isset($options['launch_job_property_id_arr']))
        {
            $this->model_arr['launch_job_property_id_arr'] = $options['launch_job_property_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_id_arr'] = $PropertyGroupObj->properties->pluck('id')->toArray();
        }
        if (isset($options['launch_job_user_id_arr']))
        {
            $this->model_arr['launch_job_user_id_arr'] = $options['launch_job_user_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_user_id_arr'] =
                $PropertyGroupObj
                    ->client
                    ->users
                ->whereNotIn('active_status', [User::ACTIVE_STATUS_INACTIVE])
                ->whereNotIn('user_invitation_status', [User::USER_INVITATION_STATUS_PENDING])
                ->sortByDesc('last_login_date')
                ->pluck('id')->toArray();
        }

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] = $options['wipe_out_list'];
        }
        else
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'users' =>
                        [
                            'accessiblePropertyGroups_user_',
                        ],
                ];
        }
        $this->model_arr['client_id'] = $PropertyGroupObj->client_id;
    }
}
