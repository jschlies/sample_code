<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AccessListPropertyCreatedEvent extends RepositoryEventBase
{
    /**
     * @var AccessListProperty
     */
    protected $AccessListPropertyObj;

    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * AccessListPropertyCreatedEvent constructor.
     * @param AccessListProperty $AccessListPropertyObj
     * @throws GeneralException
     */
    public function __construct(AccessListProperty $AccessListPropertyObj, $options = [])
    {
        parent::__construct($AccessListPropertyObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AccessListPropertyObj->property->client_id;

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['property_id'] = $AccessListPropertyObj->property_id;
        }
        $this->model_arr['client_id'] = $AccessListPropertyObj->property->client_id;

        if (isset($options['launch_job_user_id_arr']))
        {
            $this->model_arr['launch_job_user_id_arr'] = $options['launch_job_user_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_user_id_arr'] =
                $AccessListPropertyObj
                    ->accessList
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
            $this->model_arr['launch_job_property_id_arr'] = $AccessListPropertyObj->accessList->accessListProperties->pluck('property_id')->toArray();
        }

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] = $options['wipe_out_list'];
        }
        else
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'users'      =>
                        [
                            '^assetTypesOfProperties_user_.*',
                            '^accessible_property_arr_user_.*',
                            '^standardAttributesOfProperties_user_.*',
                            '^customAttributesOfProperties_user_.*',
                            '^AccessiblePropertyObjFormattedArr_user_.*',
                            '^accessiblePropertyGroups_user_.*',
                            '^user_accessable_property_id_arr_.*',
                            '^user_detail_user_.*',
                        ],
                ];
        }
    }
}
