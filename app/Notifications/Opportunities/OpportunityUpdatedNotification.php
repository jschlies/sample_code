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

class OpportunityUpdatedNotification extends Notification
{
    use Queueable;

    /** @var Opportunity */
    public $OpportunityObj;

    /**
     * @return void
     */
    public function __construct(Opportunity $OpportunityObj, $recipient_user_id_arr)
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

        $this->OpportunityObj = $OpportunityObj;

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
     * Get the mail representation of the notification.
     *
     * @param User $NotifiableUserObj
     * @return MailMessage|bool
     * @throws \Exception
     */
    public function toMail(User $NotifiableUserObj)
    {
        try
        {
            $isCreator  = $this->OpportunityObj->created_by_user_id == $NotifiableUserObj->id;
            $isAssignee = $this->OpportunityObj->assigned_to_user_id == $NotifiableUserObj->id;

            if ($this->OpportunityObj->opportunity_status == Opportunity::OPPORTUNITY_STATUS_CLOSED)
            {
                $status  = 'closed';
                $message = ($isCreator ? 'You have' : $this->OpportunityObj->createdByUser->firstname . ' ' . $this->OpportunityObj->createdByUser->lastname . ' has') . " $status the Opportunity " . $this->OpportunityObj->name . ' that you follow. If you feel that this has been done in error, you may reopen ' . $this->OpportunityObj->name . '.';
            }
            elseif ($this->OpportunityObj->opportunity_status == Opportunity::OPPORTUNITY_STATUS_OPEN)
            {
                $status  = 'opened';
                $message = ($isCreator ? 'You have' : $this->OpportunityObj->createdByUser->firstname . ' ' . $this->OpportunityObj->createdByUser->lastname . ' has') . " $status " . $this->OpportunityObj->name . " as an Opportunity. " . ($isAssignee ? 'You have ' : $this->OpportunityObj->assignedToUser->firstname . ' ' . $this->OpportunityObj->assignedToUser->lastname . ' has') . "  been assigned responsponsibility for following up on this Opportunity.";
            }
            else
            {
                throw new JobException('invalid $message');
            }

            return (new MailMessage)
                ->subject(
                    $this->OpportunityObj->createdByUser->firstname . ' ' . $this->OpportunityObj->createdByUser->lastname . " has updated the Task " . $this->OpportunityObj->name
                )
                ->greeting("Task has been updated")
                ->line('Hi ' . $NotifiableUserObj->firstname . ',')
                ->line($message)
                ->action(
                    'View Task',
                    $this->getBaseNotificationUrl() . '#/property/opportunity/' . $this->OpportunityObj->id . '?pureid=' . $this->OpportunityObj->property_id . '&uuid=' . $this->id
                )
                ->salutation(false);
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
            'opportunity_id'   => $this->OpportunityObj->id,
            'recipient_id_arr' => $this->getRecipientUserObjArr()->getArrayOfIDs(),
        ];
    }
}
