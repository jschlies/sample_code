<?php

namespace App\Waypoint\Notifications;

use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Models\Opportunity;

class OpportunityAttachedNotification extends Notification
{
    use Queueable;

    private $OpportunityObj;
    private $attacher_user_id;
    private $AttacherUserObj;
    private $attachment_name;

    /**
     * @return void
     */
    public function __construct(Opportunity $OpportunityObj, $attachment_name, $attacher_user_id, $recipient_user_id_arr = null)
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

        $RecipientUserObjArr[] = User::find($attacher_user_id);
        $this->setRecipientUserObjArr($RecipientUserObjArr);

        $this->OpportunityObj      = $OpportunityObj;
        $this->attacher_user_id    = $attacher_user_id;
        $this->attachment_name     = $attachment_name;
        $this->AttacherUserObj     = User::find($attacher_user_id);
        $this->RecipientUserObjArr = $RecipientUserObjArr;

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
        $RecipientUserObjArr[] = $this->AttacherUserObj;
        $this->setRecipientUserObjArr($RecipientUserObjArr);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the OpportunityAttachedNotification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|array
     */
    public function toMail($notifiable)
    {
        $MailMessageObj           = new MailMessage;
        $MailMessageObj->viewData = ['opportunities' => true];
        $subject                  = 'New attachment at ' . $this->OpportunityObj->property->display_name . "'s Task";
        $greeting                 = $this->OpportunityObj->createdByUser->firstname . ' ' . $this->OpportunityObj->createdByUser->lastname .
                                    ' added an attachment to an task at ' . $this->OpportunityObj->property->display_name .
                                    ' regarding ' . $this->OpportunityObj->clientCategory->name;
        $action_button_url        = $this->getBaseNotificationUrl() . '#/property/opportunity/' . $this->OpportunityObj->id . '?pureid=' .
                                    $this->OpportunityObj->property_id . '&uuid=' . $this->id;

        return ($MailMessageObj)
            ->subject($subject)
            ->greeting($greeting)
            ->line('<blockquote>' . $this->attachment_name . '</blockquote>')
            ->action('View Task', $action_button_url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray(): array
    {
        return [
            'opportunity_id'   => $this->OpportunityObj->id,
            'recipient_id_arr' => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'attachment_name'  => $this->attachment_name,
            'attacher_user_id' => $this->attacher_user_id,
        ];
    }
}
