<?php

namespace App\Waypoint\Events;

use App\Waypoint\Notifications\InvitationNotification;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class InvitationEvent extends EventBase
{
    use SerializesModels;

    /**
     * @param InvitationNotification $InvitationNotificationObj
     * @throws GeneralException
     */
    public function __construct(InvitationNotification $InvitationNotificationObj = null, $options = [])
    {
        $options['event_trigger_event_class']          = self::class;
        $options['event_trigger_event_class_instance'] = get_class($this);
        $this->model_arr['user_invitation_id'] = $InvitationNotificationObj->user_invitation_id;
        parent::__construct($InvitationNotificationObj, $options);
    }
}
