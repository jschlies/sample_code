<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\AccessList;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AccessListUpdatedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param AccessList $AccessListObj
     * @throws GeneralException
     */
    public function __construct(AccessList $AccessListObj, $options = [])
    {
        parent::__construct($AccessListObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AccessListObj->client_id;
    }
}
