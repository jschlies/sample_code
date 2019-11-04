<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\Opportunity;
use App\Waypoint\Notifications\OpportunityUpdatedNotification;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityUpdatedEvent extends EventBase
{
    use SerializesModels;

    /**
     * @param Opportunity $OpportunityObj
     * @throws GeneralException
     */
    public function __construct(OpportunityUpdatedNotification $OpportunityUpdatedNotificationObj, $options = [])
    {
        parent::__construct($OpportunityUpdatedNotificationObj, $options, self::class, get_class($this));

        $this->model_arr['client_id']      = $OpportunityUpdatedNotificationObj->OpportunityObj->property->client_id;
        $this->model_arr['property_id']    = $OpportunityUpdatedNotificationObj->OpportunityObj->property_id;
        $this->model_arr['opportunity_id'] = $OpportunityUpdatedNotificationObj->OpportunityObj->id;
    }
}
