<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Notifications\PasswordConfirmationNotification;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PasswordConfirmationEvent extends EventBase
{
    use SerializesModels;

    /**
     * @param PasswordConfirmationNotification $PasswordConfirmationNotificationObj
     * @throws GeneralException
     */
    public function __construct(PasswordConfirmationNotification $PasswordConfirmationNotificationObj, $options = [])
    {
        parent::__construct($PasswordConfirmationNotificationObj, $options, self::class, get_class($this));

        $this->model_arr['password_confirmation_notification_id'] = $PasswordConfirmationNotificationObj->id;
    }
}
