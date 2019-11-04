<?php

namespace App\Waypoint\Notifications;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Repositories\UserInvitationRepository;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Request;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    private $UserObj;
    private $secret_token;
    private $inviter_user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $UserObj, $recipient_user_id_arr = null, $inviter_user_id)
    {
        $UserRepository              = \App::make(UserRepository::class);
        $UserInvitationRepositoryObj = \App::make(UserInvitationRepository::class);
        if ($recipient_user_id_arr == null)
        {
            $RecipientUserObjArr = collect_waypoint([]);
        }
        else
        {
            $RecipientUserObjArr = $UserRepository->findWhereIn('id', $recipient_user_id_arr);
        }

        $this->UserObj = $UserObj;

        $RecipientUserObjArr[] = $UserObj;
        $this->setRecipientUserObjArr($RecipientUserObjArr);

        $this->inviter_user_id = $inviter_user_id;
        $this->secret_token    = $UserRepository->generatePasswordToken($UserObj->email);
        $UserInvitationRepositoryObj->create(
            [
                'invitee_user_id'       => $UserObj->id,
                'invitation_status'     => UserInvitation::INVITATION_STATUS_PENDING,
                'one_time_token_expiry' => $this->secret_token['one_time_token_expiry'],
                'one_time_token'        => $this->secret_token['one_time_token'],
                'inviter_user_id'       => $UserObj->id,
                'inviter_ip'            => Request::ip(),
            ]
        );
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
                ->subject('Reset Password for Waypoint')
                ->greeting('Forgot Your Password?')
                ->line('We received your request to reset your password. Click the link below to securely reset your password.')
                ->action(
                    'Reset Password',
                    config('waypoint.notifications_base_url', 'https://app.waypointbuilding.com/') . 'reset-password?one_time_token=' . $this->secret_token['one_time_token']
                )
                ->line(
                    'Didn\'t request a password reset? Simply ignore this email or contact us at <a style="color: #43a4dc; font-weight: bold;" href="mailto:support@waypointbuilding.com?subject=Reset Password Email Help">support@waypointbuilding.com</a>'
                )
                ->salutation(true);
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
    public function toArray()
    {
        return [
            'user_id'          => $this->UserObj->id,
            'recipient_id_arr' => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'inviter_user_id'  => $this->inviter_user_id,
        ];
    }
}
