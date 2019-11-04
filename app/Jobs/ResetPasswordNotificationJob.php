<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Notifications\ResetPasswordNotification;
use App\Waypoint\Notifications\Notification;

/**
 * Class ResetPasswordNotificationJob
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class ResetPasswordNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $password_confirmation_notification_arr;

    /**
     * Create a new job instance.
     *
     * ResetPasswordNotificationJob constructor.
     * @param $password_confirmation_notification_arr
     */
    public function __construct($password_confirmation_notification_arr)
    {
        $this->password_confirmation_notification_arr = $password_confirmation_notification_arr;
    }

    /**
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            /** @var App\Waypoint\Repositories\UserRepository $UserRepositoryObj */
            $UserRepositoryObj = App::make(UserRepository::class)->setSuppressEvents(true);
            $UserObj           = $UserRepositoryObj->find($this->password_confirmation_notification_arr['user_id']);
            $RecipientObjArr   = collect_waypoint([$UserObj]);
            Notification::send(
                $RecipientObjArr,
                new ResetPasswordNotification($UserObj, $RecipientObjArr->getArrayOfIDs(), $this->password_confirmation_notification_arr['inviter_user_id'])
            );
        }
        catch (\Exception $e)
        {
            throw new JobException(__CLASS__, 404, $e);
        }
    }
}
