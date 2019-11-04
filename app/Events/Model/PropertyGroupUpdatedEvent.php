<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\PropertyGroup;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PropertyGroupUpdatedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
     * PropertyGroupUpdatedEvent constructor.
     * @param PropertyGroup $PropertyGroupObj
     * @throws GeneralException
     */
    public function __construct(PropertyGroup $PropertyGroupObj, $options = [])
    {
        parent::__construct($PropertyGroupObj, $options, self::class, get_class($this));
        $this->model_arr['client_id'] = $PropertyGroupObj->client_id;
    }
}
