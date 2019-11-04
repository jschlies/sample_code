<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Collection;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Testing\Fakes\NotificationFake as NotificationFakeBase;
use function waypoint_generate_uuid;

class NotificationFake extends NotificationFakeBase
{
    /**
     * Send the given notification immediately.
     *
     * @param \Illuminate\Support\Collection|array|mixed $notifiables
     * @param mixed $notification
     * @return void
     */
    public function sendNow($notifiables, $notification)
    {
        if ( ! $notifiables instanceof Collection && ! is_array($notifiables))
        {
            $notifiables = [$notifiables];
        }

        foreach ($notifiables as $notifiable)
        {
            $notification->id = waypoint_generate_uuid();

            $this->notifications[get_class($notifiable)][$notifiable->getKey()][get_class($notification)][] = [
                'notification' => $notification,
                'channels'     => $notification->via($notifiable),
            ];

            if (empty($notification->via($notifiable)))
            {
                continue;
            }

            foreach ((array) $notification->via($notifiable) as $channel)
            {
                event(
                    new NotificationSent($notifiable, $notification, $channel, null),
                    []
                );
            }
        }
    }

    /**
     * @return string
     */
    public static function getNotificationActionLinkRegEx()
    {
        return
            '/' .
            preg_quote(
                config('waypoint.notifications_base_url', 'https://app.waypointbuilding.com/')
                , '/'
            ) .
            'property\/profile\?pureid=\d*&uuid=[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-(8|9|a|b)[a-f0-9]{3}-[a-f0-9]{12}' .
            '/';
    }
}
