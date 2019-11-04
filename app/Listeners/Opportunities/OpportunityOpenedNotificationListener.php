<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\OpportunityOpenedNotificationJob;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityOpenedNotificationListener extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = OpportunityOpenedNotificationJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.OpportunityOpenedNotification', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     */
    public function handle($OpportunityOpenedEventObj = null)
    {
        try
        {
            $this->model_arr = $OpportunityOpenedEventObj->getModelArr();

            parent::handle($OpportunityOpenedEventObj);
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($OpportunityOpenedEventObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
