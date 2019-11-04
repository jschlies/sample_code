<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\Property;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PropertyCreatedEvent extends RepositoryEventBase
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
    }
}
