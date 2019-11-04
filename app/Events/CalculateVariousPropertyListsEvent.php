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
class CalculateVariousPropertyListsEvent extends EventBase implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @param Client $ClientObj
     * @throws GeneralException
     */
    public function __construct(Client $ClientObj = null, $options = [])
    {
        parent::__construct($ClientObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $ClientObj->id;
    }
}
