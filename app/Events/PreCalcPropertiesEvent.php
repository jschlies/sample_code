<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcPropertiesEvent extends EventBase implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @param Client $ClientObj
     * @throws GeneralException
     */
    public function __construct($ClientObj = null, $options = [])
    {
        parent::__construct($ClientObj, $options, self::class, get_class($this));
        $this->model_arr['client_id'] = $ClientObj->id;

        if (isset($options['launch_job_property_id_arr']))
        {
            $this->model_arr['launch_job_property_id_arr'] = $options['launch_job_property_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_id_arr'] = $ClientObj->properties->pluck('id')->toArray();
        }

        if ( ! isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'properties' => [],
                ];
        }
    }
}

