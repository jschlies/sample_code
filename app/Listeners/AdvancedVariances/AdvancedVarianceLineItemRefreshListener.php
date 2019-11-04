<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Events\AdvancedVarianceLineItemCreatedEvent;
use App\Waypoint\Events\AdvancedVarianceLineItemUpdatedEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob;
use Exception;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceLineItemRefreshListener extends Listener
{
    /**
     * AdvancedVarianceLineItemRefreshListener constructor.
     * @throws ListenerException
     */
    public function __construct()
    {
        $this->job_class = AdvancedVarianceLineItemRefreshJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param AdvancedVarianceLineItemCreatedEvent|AdvancedVarianceLineItemUpdatedEvent $AdvancedVarianceLineItemEventObj
     * @throws ListenerException
     */
    public function handle($AdvancedVarianceLineItemEventObj = null)
    {
        try
        {
            $this->model_arr = $AdvancedVarianceLineItemEventObj->getModelArr();

            parent::handle($AdvancedVarianceLineItemEventObj);
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException(__CLASS__ . ' Event ' . get_class($AdvancedVarianceLineItemEventObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
