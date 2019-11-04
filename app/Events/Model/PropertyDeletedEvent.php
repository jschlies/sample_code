<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PropertyDeletedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param Property $PropertyObj
     * @throws GeneralException
     */
    public function __construct(Property $PropertyObj, $options = [])
    {
        parent::__construct($PropertyObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $PropertyObj->client_id;

        if (isset($options['launch_job_user_id_arr']))
        {
            $this->model_arr['launch_job_user_id_arr'] = $options['launch_job_user_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_user_id_arr'] =
                $PropertyObj
                    ->client
                    ->users
                    ->whereNotIn('active_status', [User::ACTIVE_STATUS_INACTIVE])
                    ->whereNotIn('user_invitation_status', [User::USER_INVITATION_STATUS_PENDING])
                    ->sortByDesc('last_login_date')
                    ->pluck('id')
                    ->toArray();
        }
        if (isset($options['launch_job_property_id_arr']))
        {
            $this->model_arr['launch_job_property_id_arr'] = $options['launch_job_property_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_id_arr'] = $PropertyObj->client->properties->pluck('id')->toArray();
        }
        if (isset($options['launch_job_property_group_id_arr']))
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $options['launch_job_property_group_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $PropertyObj->client->propertyGroups()->pluck('id')->toArray();
        }

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] = $options['wipe_out_list'];
        }
        else
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'clients'        =>
                        [
                            'standard_attribute_unique_values_client_',
                            'custom_attribute_unique_values_client_',
                        ],
                    'users'          =>
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
                    'propertyGroups' => [],
                ];
        }
    }
}
