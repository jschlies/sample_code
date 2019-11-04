<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\Client;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class ClientCreatedEvent extends RepositoryEventBase
{
    /**
     * @var Client
     */
    protected $ClientObj;

    use SerializesModels;

    /**
     * @param Client $ClientObj
     * @throws GeneralException
     */
    public function __construct(Client $ClientObj, $options = [])
    {
        parent::__construct($ClientObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $ClientObj->id;
    }
}
