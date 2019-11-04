<?php

namespace App\Waypoint\Listeners;

use App;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Repositories\NotificationLogRepository;
use Carbon\Carbon;
use Exception;
use function json_encode;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class NotificationLogListener extends Listener
{
    /**
     * @var NotificationLogRepository
     */
    protected $NotificationLogRepositoryObj;

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param null $NotificationEventObj
     * @throws ListenerException
     */
    public function handle($NotificationEventObj = null)
    {
        try
        {
            $this->NotificationLogRepositoryObj = App::make(NotificationLogRepository::class);
            $this->NotificationLogRepositoryObj->create(
                [
                    'notification_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'notification_uuid' => $NotificationEventObj->notification->id,
                    'user_id'           => $NotificationEventObj->notifiable->id,
                    'user_json'         => $NotificationEventObj->notifiable ? json_encode($NotificationEventObj->notifiable->toArray()) : '{}',
                    'channel'           => $NotificationEventObj->channel,
                    'queue'             => $NotificationEventObj->notification->queue,
                    'response'          => $NotificationEventObj->response,
                    'data_json'         => json_encode(
                        $NotificationEventObj->notification->toMail(
                            $NotificationEventObj->notifiable
                        )
                    ),
                ]
            );
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
