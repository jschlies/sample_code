<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Repositories\UserRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class OpportunityOpenedNotification extends Notification
{
    use Queueable;

    public $OpportunityObj;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Opportunity $OpportunityObj, $recipient_user_id_arr = null)
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
     * @param mixed $NotifiableUserObj
     * @return array
     * @noinspection PhpUnusedParameterInspection
     *               Leave this param here for now
     */
    public function via($NotifiableUserObj)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     * @throws GeneralException
     * @throws JobException
     */
    public function toMail($NotifiableUserObj)
    {
        try
        {
            $MailMessageObj           = new MailMessage;
            $MailMessageObj->viewData = ['opportunities' => true];
            $subject                  = 'New Task for ' . $this->OpportunityObj->property->display_name;
            $greeting                 = $this->OpportunityObj->createdByUser->firstname . ' ' . $this->OpportunityObj->createdByUser->lastname .
                                        ' opened an task regarding ' . $this->OpportunityObj->clientCategory->name .
                                        ' at ' . $this->OpportunityObj->property->display_name;
            $action_button_url        = $this->getBaseNotificationUrl() . '#/property/opportunity/' . $this->OpportunityObj->id . '?pureid=' .
                                        $this->OpportunityObj->property_id . '&uuid=' . $this->id;

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($greeting)
                ->action('View Task', $action_button_url)
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
