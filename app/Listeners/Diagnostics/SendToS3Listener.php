<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ListenerException;
use Exception;

/**
 * Class InvitationNotificationListener
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class SendToS3Listener extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     */
    public function handle($EventObj = null)
    {
        try
        {
            /**
             * look in $this->DiagnosticObj for that data passed via param 1 of event trigger
             * look in $this->options for that data passed via param 2 of event trigger
             *
             */
        }
        catch (ListenerException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage());
        }
    }
}
