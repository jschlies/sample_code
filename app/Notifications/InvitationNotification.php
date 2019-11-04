<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserAdmin;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Repositories\UserInvitationRepository;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use Carbon\Carbon;

class InvitationNotification extends Notification
{
    use Queueable;

    public $user_invitation_id;
    const ADDITONAL_DAY_TO_ADJUST_FOR_SLIGHLY_DELAYED_EMAIL = 1;

    /**
     * Create a new notification instance.
     *
     * InvitationNotification constructor.
     * @param UserAdmin $InvitedUserObj
     * @param null $recipient_user_id_arr
     */
    public function __construct(UserInvitation $UserInvitationObj, $recipient_user_id_arr = null)
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
        $this->user_invitation_id = $UserInvitationObj->id;
        $RecipientUserObjArr[]    = $UserInvitationObj->inviteeUser;
        $this->setRecipientUserObjArr($RecipientUserObjArr);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     *               Leave this param here for now
     */
    public function via($NotifiableUserObj)
    {
        return ['mail'];
    }

    /**
     * @param User $NotifiableUserObj
     * @return MailMessage
     * @throws GeneralException
     * @throws JobException
     * @noinspection PhpUnusedParameterInspection
     *               Leave this param here for now
     */
    public function toMail(User $NotifiableUserObj)
    {
        try
        {
            $UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);
            $UserInvitationObj           = $UserInvitationRepositoryObj->find($this->user_invitation_id);
            $num_of_days_until_expiry    = $this->getDaysUntilExpiry($UserInvitationObj->one_time_token_expiry) + self::ADDITONAL_DAY_TO_ADJUST_FOR_SLIGHLY_DELAYED_EMAIL;

            $MailMessageObj = new MailMessage;

            // Data to be passed through to the blade template
            // Reference these by using the array key as a variable
            //   e.g. $user_invitation (inside blade template) will be set per the 'user_invitation' key below
            $MailMessageObj->viewData = [
                'user_invitation'    => true,
                'bullets'            => [
                    'Gain actionable insights into your portfolio\'s financials',
                    'Simplify your monthly reporting processes',
                    'Collaborate with your team online',
                ],
                'expiration_message' => 'This link will expire in ' . $num_of_days_until_expiry . ' day(s).',
            ];

            // Here's where the email data is rendered and the above viewData will be folded in
            return ($MailMessageObj)
                ->subject('You are invited to Waypoint')
                ->from('do-not-reply@waypointbuilding.com', 'Waypoint')
                ->greeting('Welcome to Waypoint!')
                ->line('Join your team on the leading performance analytics and management software platform for commercial real estate professionals.')
                ->action(
                    'Accept Invitation',
                    config('waypoint.notifications_base_url', 'https://app.waypointbuilding.com/') . 'setup-password?one_time_token=' . $UserInvitationObj->one_time_token
                )
                ->salutation(false);
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
     * @param Carbon $expiry_timestamp
     * @return int
     */
    private function getDaysUntilExpiry($expiry_timestamp)
    {
        $from = Carbon::now();
        return $from->diffInDays($expiry_timestamp, true);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        $return_me = [
            'user_invitation_id' => $this->user_invitation_id,
        ];
        return $return_me;
    }
}
