<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\JobException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Notifications\PasswordConfirmationNotification;
use App\Waypoint\Notifications\Notification;

/**
 * Class PasswordConfirmationNotificationJob
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PasswordConfirmationNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $password_confirmation_notification_arr;

    /**
     * Create a new job instance.
     *
     * PasswordConfirmationNotificationJob constructor.
     *
     * PasswordConfirmationNotificationJob constructor.
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
            /** @var UserRepository $UserRepositoryObj */
            $UserRepositoryObj = App::make(UserRepository::class)->setSuppressEvents(true);
            $UserObj           = $UserRepositoryObj->find($this->password_confirmation_notification_arr['user_id']);
            /** @var Collection $RecipientObjArr */
            $RecipientObjArr = $UserRepositoryObj->findWhereIn('id', $this->password_confirmation_notification_arr['recipient_id_arr']);

            Notification::send(
                $RecipientObjArr,
                new PasswordConfirmationNotification($UserObj, $this->password_confirmation_notification_arr['recipient_id_arr'])
            );
        }
        catch (\Exception $e)
        {
            throw new JobException(__CLASS__, 404, $e);
        }
    }
}
