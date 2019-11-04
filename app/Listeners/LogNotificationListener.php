<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\ListenerException;
use Exception;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class LogNotificationListener extends Listener
{
    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param \App\Waypoint\Events\EventBase|null $NotificationEventObj
     * @throws ListenerException
     */
    public function handle($NotificationEventObj = null)
    {
        try
        {
            $this->model_arr = $NotificationEventObj->getModelArr();

            parent::handle($NotificationEventObj);
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($NotificationEventObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
