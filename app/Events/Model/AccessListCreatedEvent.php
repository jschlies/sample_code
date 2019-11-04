<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AccessListCreatedEvent extends RepositoryEventBase
{
    /**
     * @var AccessList
     */
    protected $AccessListObj;

    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * AccessListCreatedEvent constructor.
     *
     * @param AccessList $AccessListObj
     * @throws GeneralException
     */
    public function __construct(AccessList $AccessListObj, $options = [])
    {
        parent::__construct($AccessListObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AccessListObj->client_id;
    }
}
