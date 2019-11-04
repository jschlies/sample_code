<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Events\EventBase;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\ClearDormantUsersJob;

/**
 * Class ClearDormantUsersListener
 * @package App\Waypoint\Listeners
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class ClearDormantUsersListener extends Listener
{

    /** @var array */
    public $model_class = null;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = ClearDormantUsersJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.ClearDormantUsers', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * @param EventBase $EventObj
     * @throws GeneralException
     * @throws ListenerException
     */
    public function handle($EventObj = null)
    {
        try
        {
            $this->model_arr = $EventObj->getModelArr();

            parent::handle($EventObj);
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            throw new ListenerException(__CLASS__ . ' Event ' . get_class($EventObj) . ' at ' . __FILE__ . ':' . __LINE__, 404, $e);
        }
    }
}
