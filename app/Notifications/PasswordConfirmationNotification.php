<?php

namespace App\Waypoint\Notifications;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordConfirmationNotification extends Notification
{
    use Queueable;

    private $UserObj;

    /**
     * Create a new notification instance.
     *
     * @return void
     *
     * do not implement a base constructor as it ties our hands re: method signatures
     * of other Notification
     */
    public function __construct(User $UserObj, $recipient_user_id_arr = null)
    {
        $UserRepository = \App::make(UserRepository::class);
        if ($recipient_user_id_arr == null)
        {
            $RecipientUserObjArr = collect_waypoint([]);
        }
        else
        {
            $RecipientUserObjArr = $UserRepository->findWhereIn('id', $recipient_user_id_arr);
        }

        $this->UserObj = $UserObj;

        /**
         * add $UserObj to $RecipientUserObjArr
         *
         * Note that any de-duping needed here is done in Notification::send() as
         * well as $this->setRecipientUserObjArr
         */
        $RecipientUserObjArr[] = $UserObj;
        $this->setRecipientUserObjArr($RecipientUserObjArr);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param User $NotifiableUserObj
     * @return array
     *               Leave this param here for now
     */
    public function via(User $NotifiableUserObj)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $NotifiableUserObj
     * @return \Illuminate\Notifications\Messages\MailMessage
     *               Leave this param here for now
     */
    public function toMail(User $NotifiableUserObj)
    {
        try
        {
            return (new MailMessage)
                ->from('do-not-reply@waypointbuilding.com', 'Waypoint')
                ->subject('Waypoint Password Successfully Changed')
                ->greeting('Your Waypoint password has been successfully reset')
                ->line('You may now log in to the platform using your email and new password.')
                ->action(
                    'Log In',
                    config('waypoint.notifications_base_url', 'https://app.waypointbuilding.com/') . 'login/' . '?uuid=' . $this->id
                )
                ->line(
                    'Didn\'t set up a new password? Please contact us immediately at <a style="color: #43a4dc; font-weight: bold;" href="mailto:support@waypointbuilding.com?subject=New Password Email Help">support@waypointbuilding.com</a>'
                );
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
        catch (\Exception $e)
        {
            throw new JobException(__CLASS__ . 'Notification::send() failed', 404, $e);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id'          => $this->UserObj->id,
            'recipient_id_arr' => $this->getRecipientUserObjArr()->getArrayOfIDs(),
        ];
    }
}
