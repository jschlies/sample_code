<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\Property;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PropertyUpdatedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param Property $PropertyObj
     * @throws GeneralException
     */
    public function __construct(Property $PropertyObj, $options = [])
    {
        parent::__construct($PropertyObj, $options, self::class, get_class($this));

        $this->model_arr['property_id'] = $PropertyObj->id;
        $this->model_arr['client_id']   = $PropertyObj->client_id;

        if (isset($options['launch_job_property_group_id_arr']))
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $options['launch_job_property_group_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $PropertyObj->propertyGroups->pluck('id')->toArray();
        }

        if (isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] = $options['wipe_out_list'];
        }
        else
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'users'          =>
                        [
                            'accessiblePropertyGroups_user_',
                        ],
                    'propertyGroups' => [],
                ];
        }
        $this->model_arr['client_id'] = $PropertyObj->client_id;
    }
}
