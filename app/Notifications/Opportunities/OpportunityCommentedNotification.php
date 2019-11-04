<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use function in_array;

class OpportunityCommentedNotification extends Notification
{
    use Queueable;
    public $OpportunityObj;
    public $comment;
    public $commenter_id;
    public $mentioned_user_id_arr;

    /**
     * @return void
     */
    public function __construct(Opportunity $OpportunityObj, $comment, $commenter_id, $recipient_user_id_arr = null, $mentioned_user_id_arr = null)
    {
        /** @var  $UserRepositoryObj */
        $UserRepositoryObj = App::make(UserRepository::class);
        /** @var App\Waypoint\Collection $RecipientUserObjArr */
        if ($recipient_user_id_arr == null)
        {
            $RecipientUserObjArr = collect_waypoint([]);
        }
        else
        {
            $RecipientUserObjArr = $UserRepositoryObj->findWhereIn('id', $recipient_user_id_arr);
        }

        $this->OpportunityObj        = $OpportunityObj;
        $this->comment               = $comment;
        $this->commenter_id          = $commenter_id;
        $this->mentioned_user_id_arr = $mentioned_user_id_arr;

        /**
         * createdByUser and assignedToUser should get copies
         */
        if ($this->OpportunityObj->createdByUser)
        {
            $RecipientUserObjArr[] = $this->OpportunityObj->createdByUser;
        }
        if ($this->OpportunityObj->assignedToUser)
        {
            $RecipientUserObjArr[] = $this->OpportunityObj->assignedToUser;
        }

        $RecipientUserObjArr = $RecipientUserObjArr->merge(
            $UserRepositoryObj->findWhereIn('id', $this->mentioned_user_id_arr)
        );

        $this->setRecipientUserObjArr($RecipientUserObjArr);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * @param User $UserObj
     * @return bool
     */
    protected function isMentionsNotificationsTurnedOn(User $UserObj)
    {
        return (
            (bool) $UserObj->getConfigValue(User::OPPORTUNITIES_MENTIONED_NOTIFICATIONS_FLAG)
            &&
            (bool) $UserObj->getConfigValue(User::VARIANCE_MENTIONED_NOTIFICATIONS_FLAG)
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     * @return MailMessage
     * @throws GeneralException
     * @throws JobException
     */
    public function toMail(User $UserObj)
    {
        try
        {
            $MailMessageObj           = new MailMessage;
            $MailMessageObj->viewData = ['opportunities' => true];
            $isCommentor              = $this->commenter_id == $UserObj->id;
            $isMentionee              = ! $isCommentor && in_array($UserObj->id, $this->mentioned_user_id_arr);
            $UserRepositoryObj        = App::make(UserRepository::class);
            $CommentedUserObj         = $UserRepositoryObj->find($this->commenter_id);
            $styles['blockquote']     = 'line-height: 30px;';

            if (
                $isMentionee
                &&
                $this->isMentionsNotificationsTurnedOn($UserObj)
            )
            {
                $subject = $CommentedUserObj->firstname . ' ' . $CommentedUserObj->lastname .
                           ' mentioned you in a comment for ' . $this->OpportunityObj->property->display_name;
                $heading = 'You were mentioned on ' . $this->OpportunityObj->property->display_name . "'s " .
                           $this->OpportunityObj->name . ' by ' . $CommentedUserObj->firstname . ' ' . $CommentedUserObj->lastname;
            }
            else
            {
                $subject = 'New comment at ' . $this->OpportunityObj->property->display_name . "'s Task";
                $heading = $CommentedUserObj->firstname . ' ' . $CommentedUserObj->lastname .
                           ' commented on an task regarding ' . $this->OpportunityObj->clientCategory->name .
                           ' at ' . $this->OpportunityObj->property->display_name;
            }

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($heading)
                ->line('<blockquote style="' . $styles['blockquote'] . '" >' . $this->replaceUserIdsWithNames($this->comment) . '</blockquote>')
                ->action(
                    'View Task',
                    $this->getBaseNotificationUrl() . '#/property/opportunity/' . $this->OpportunityObj->id . '?pureid=' . $this->OpportunityObj->property_id . '&uuid=' . $this->id
                );
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
        catch (Exception $e)
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
            'opportunity_id'        => $this->OpportunityObj->id,
            'comment'               => $this->comment,
            'commenter_id'          => $this->commenter_id,
            'recipient_id_arr'      => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'mentioned_user_id_arr' => $this->mentioned_user_id_arr ?: [],
        ];
    }
}
