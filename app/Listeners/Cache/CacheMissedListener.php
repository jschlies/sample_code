<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\ListenerException;
use Exception;

/**
 * Class LogCacheMissedListener
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class CacheMissedListener extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     */
    public function handle($SomeObj = null)
    {
        try
        {
            /**
             * DO NOT CALL PARENT since this listener does not result in a job to the queue
             * @todo - deal with this.
             */
            $x=1;
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($SomeObj) . ' at ' . __FILE__ . ':' . __LINE__, 400, $e);
        }
    }
}
