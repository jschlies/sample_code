<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\AccessListProperty;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AccessListPropertyUpdatedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param AccessListProperty $AccessListPropertyObj
     * @throws GeneralException
     */
    public function __construct(AccessListProperty $AccessListPropertyObj, $options = [])
    {
        parent::__construct($AccessListPropertyObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AccessListPropertyObj->accessList->client_id;
    }
}
