<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\AccessListUser;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AccessListUserUpdatedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * @param AccessListUser $AccessListUserObj
     * @throws GeneralException
     */
    public function __construct(AccessListUser $AccessListUserObj, $options = [])
    {
        parent::__construct($AccessListUserObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AccessListUserObj->accessList->client_id;
    }
}
