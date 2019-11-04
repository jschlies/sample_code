<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\OpportunityAttachedNotificationJob;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityAttachedNotificationListener extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = OpportunityAttachedNotificationJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.OpportunityAttachedNotification', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     */
    public function handle($OpportunityAttachedEventObj = null)
    {
        try
        {
            $this->model_arr = $OpportunityAttachedEventObj->getModelArr();

            parent::handle($OpportunityAttachedEventObj);
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($OpportunityAttachedEventObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
