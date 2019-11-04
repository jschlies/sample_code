<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceLineItemFlaggedNotification extends AdvancedVarianceNotificationBase
{
    /**
     * @return void
     */
    public function __construct(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $recipient_user_id_arr = [])
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceLineItemObj($AdvancedVarianceLineItemObj);

        /**
         * who should get copies
         */
        $this->setRecipientUserObjArr(
            waypoint_merge_collections(
                $this->getAdvancedVarianceLineItemObj()->advancedVariance->getExpectedRecipiants(),
                App::make(UserRepository::class)->findWhereIn('id', $recipient_user_id_arr)
            )->unique(
                function (User $UserObj)
                {
                    return $UserObj->id;
                }
            )
        );
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
            $MailMessageObj->viewData = ['advanced_variance' => true];
            $subject                  = $this->getAdvancedVarianceLineItemObj()->advancedVariance->property->name . "'s " .
                                        $this->get_period_text_for_advanced_variance_line_item() . ' Variance Report has a new marked account';
            $action_button_url        = $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->id . '?pureid=' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->property_id . '&uuid=' . $this->id;
            /**
             * this is a temp fix. We have lots of race conditions in our system. In this case, while the Notification job has been sitting on queue, the
             * AdvancedVariance in question has been unlocked - See HER-4003
             * @todo fix me
             */
            $greeting = $this->get_account_name_from_advanced_variance_line_item() . ' has been marked';
            if ($this->getAdvancedVarianceLineItemObj()->flaggerUser)
            {
                $greeting = $this->getAdvancedVarianceLineItemObj()->flaggerUser->getDisplayName() .
                            ' marked ' . $this->get_account_name_from_advanced_variance_line_item();
            }

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($greeting)
                ->action('View Report', $action_button_url)
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
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'advanced_variance_line_item_id' => $this->getAdvancedVarianceLineItemObj()->id,
            'recipient_id_arr'               => $this->getRecipientUserObjArr()->getArrayOfIDs(),
        ];
    }
}
