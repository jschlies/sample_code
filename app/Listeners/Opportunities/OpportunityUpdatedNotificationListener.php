<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\OpportunityUpdatedNotificationJob;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityUpdatedNotificationListener extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = OpportunityUpdatedNotificationJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.OpportunityUpdatedNotification', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     */
    public function handle($OpportunityUpdatedEventObj = null)
    {
        try
        {
            $this->model_arr = $OpportunityUpdatedEventObj->getModelArr();

            parent::handle($OpportunityUpdatedEventObj);
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($OpportunityUpdatedEventObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
