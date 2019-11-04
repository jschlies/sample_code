<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\PreCalcUsersJob;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use Log;

/**
 * Class PreCalcUsersListener
 * @package App\Waypoint\Listeners
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcUsersListener extends Listener
{
    /** @var array */
    public $model_class = Client::class;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = PreCalcUsersJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.PreCalcUsers', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param PreCalcUsersEvent|\App\Waypoint\Events\EventBase $EventObj
     * @throws GeneralException
     * @throws ListenerException
     *
     * Please take a minute to research, think and understand the environment
     * switches SUPPRESS_PRE_CALC_USAGE, SUPPRESS_PRE_CALC_EVENTS and QUEUE_DRIVER
     * and the client config values SUPPRESS_PRE_CALC_USAGE, SUPPRESS_PRE_CALC_EVENTS.
     * Depending on the which of our environs you'er using, Homestead, Hydra,
     * Staging or prod. Getting these wrong can result in loooooong
     * migration times and/or poor performance and bad moral character
     */
    public function handle($EventObj = null)
    {
        /**
         * this code deals with the fact that this Listener may be listing to Events
         * that pass an object (aka model_arr) that is not the object that the Job that this
         * Listener creates needs. This code resolves that
         */
        $this->populate_model_arr($EventObj);

        if ( ! $ClientObj = Client::find($EventObj->getModelArr()['client_id']))
        {
            Log::error('Unknown client_id = ' . $EventObj->getModelArr()['client_id'] . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        if ($ClientObj->suppress_pre_calc_usage())
        {
            return;
        }

        $wipe_out_list = (
        isset($EventObj->getModelArr()['wipe_out_list']))
            ? $EventObj->getModelArr()['wipe_out_list']
            : ['users' => []];

        $EventObj->model_arr['event_trigger_listener_wipe_out'] = print_r(array_merge($wipe_out_list, ['wipe_out_caller' => self::class]), true);

        if ( ! isset($EventObj->model_arr['launch_job_user_id_arr']))
        {
            $EventObj->model_arr['launch_job_user_id_arr'] =
                $ClientObj
                    ->users
                    ->whereNotIn('active_status', [User::ACTIVE_STATUS_INACTIVE])
                    ->whereNotIn('user_invitation_status', [User::USER_INVITATION_STATUS_PENDING])
                    ->sortByDesc('last_login_date')
                    ->pluck('id')
                    ->toArray();
        }

        $this->wipe_out_pre_calcs($ClientObj, $wipe_out_list, $EventObj);

        if ($ClientObj->suppress_pre_calc_events())
        {
            return;
        }

        parent::handle($EventObj);
    }
}
