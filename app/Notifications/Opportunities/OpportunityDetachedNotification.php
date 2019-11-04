<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Models\Opportunity;

class OpportunityDetachedNotification extends Notification
{
    use Queueable;
    /** @var Opportunity */
    public $OpportunityObj;
    public $detacher_user_id;
    /** @var User */
    public $DetacherUserObj;
    public $attachment_name;

    /**
     * @return void
     */
    public function __construct(Opportunity $OpportunityObj, $attachment_name, $detacher_user_id, array $recipient_user_id_arr = [])
    {
        $UserRepository = App::make(UserRepository::class);
        if ($recipient_user_id_arr == null)
        {
            $RecipientUserObjArr = collect_waypoint([]);
        }
        else
        {
            $RecipientUserObjArr = $UserRepository->findWhereIn('id', $recipient_user_id_arr);
        }

        /**
         * who should get copies
         */
        $this->setRecipientUserObjArr(
            waypoint_merge_collections(
                new App\Waypoint\Collection(),
                App::make(UserRepository::class)->findWhereIn('id', $recipient_user_id_arr)
            )->unique(
                function (User $UserObj)
                {
                    return $UserObj->id;
                }
            )
        );

        $this->OpportunityObj      = $OpportunityObj;
        $this->detacher_user_id    = $detacher_user_id;
        $this->attachment_name     = $attachment_name;
        $this->DetacherUserObj     = User::find($detacher_user_id);
        $this->RecipientUserObjArr = $RecipientUserObjArr;
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
     * Get the mail representation of the notification.
     *
     * @param mixed $NotifiableUserObj
     * @return \Illuminate\Notifications\Messages\MailMessage|array
     */
    public function toMail($NotifiableUserObj)
    {
        $isCreator       = $this->DetacherUserObj->id == $NotifiableUserObj->id;
        $DetacherUserObj = User::find($this->detacher_user_id);

        return (new MailMessage())
            ->subject(
                $DetacherUserObj->firstname . ' ' . $DetacherUserObj->lastname . ' removed an attachment from a Task ' . $this->OpportunityObj->name
            )
            ->greeting('Opportunity has an Attachment Deleted ')
            ->line('Hi ' . $NotifiableUserObj->firstname . ', ')
            ->line(
                ($isCreator ? 'You have' : $this->DetacherUserObj->firstname . ' ' . $this->DetacherUserObj->lastname . ' has') . " removed an attachment from Task " . $this->OpportunityObj->name . ':'
            )
            ->line('<blockquote>' . $this->attachment_name . '</blockquote>')
            ->action(
                'View Task',
                $this->getBaseNotificationUrl() . '#/property/opportunity/' . $this->OpportunityObj->id . '?pureid=' . $this->OpportunityObj->property_id . '&uuid=' . $this->id
            )
            ->salutation(false);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'opportunity_id'   => $this->OpportunityObj->id,
            'recipient_id_arr' => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'attachment_name'  => $this->attachment_name,
            'detacher_user_id' => $this->detacher_user_id,
        ];
    }
}
