<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcUsersEvent extends EventBase implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @param null $ModelObj
     * @throws GeneralException
     */
    public function __construct($ClientObj = null, $options = [])
    {
        parent::__construct($ClientObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $ClientObj->id;

        if (isset($options['launch_job_user_id_arr']))
        {
            $this->model_arr['launch_job_user_id_arr'] = $options['launch_job_user_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_user_id_arr'] =
                $ClientObj
                    ->users
                    ->whereNotIn('active_status', [User::ACTIVE_STATUS_INACTIVE])
                    ->whereNotIn('user_invitation_status', [User::USER_INVITATION_STATUS_PENDING])
                    ->sortByDesc('last_login_date')
                    ->pluck('id')
                    ->toArray();
        }

        if ( ! isset($options['wipe_out_list']))
        {
            $this->model_arr['wipe_out_list'] =
                [
                    'users' => [],
                ];
        }
    }
}
