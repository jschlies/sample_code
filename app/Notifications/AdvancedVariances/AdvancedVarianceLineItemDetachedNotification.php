<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceLineItemDetachedNotification extends AdvancedVarianceNotificationBase
{
    use Queueable;

    private $detacher_user_id;
    private $DetacherUserObj;
    private $attachment_name;

    /**
     * @return void
     */
    public function __construct(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $attachment_name, $detacher_user_id, array $recipient_user_id_arr = [])
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceLineItemObj($AdvancedVarianceLineItemObj);
        $this->detacher_user_id = $detacher_user_id;
        $this->attachment_name  = $attachment_name;
        $this->DetacherUserObj  = User::find($detacher_user_id);

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
     * @param  mixed $NotifiableUserObj
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
            $isCreator       = $this->DetacherUserObj->id == $NotifiableUserObj->id;
            $DetacherUserObj = User::find($this->detacher_user_id);

            return (new MailMessage)
                ->subject(
                    $DetacherUserObj->firstname . ' ' . $DetacherUserObj->lastname . ' removed an attachment from ' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->property->name . '\'s ' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month . '/' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year . ' Variance Report'
                )
                ->greeting('Advanced Variance ATTACHMENT has been deleted')
                ->line('Hi ' . $NotifiableUserObj->firstname . ', ')
                ->line(
                    ($isCreator ? 'You have' : $this->DetacherUserObj->firstname . ' ' . $this->DetacherUserObj->lastname . ' has') . 'deleted an Attachment for property ' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->property->name .
                    ' for ' . ($this->getAdvancedVarianceLineItemObj()->advancedVariance->period_type == AdvancedVariance::PERIOD_TYPE_QUARTERLY ? 'quarter beginning ' : 'month ') .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month . '/' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year .
                    ($this->getAdvancedVarianceLineItemObj()->nativeAccount
                        ?
                        ' for account code ' . $this->getAdvancedVarianceLineItemObj()->nativeAccount->native_account_code
                        :
                        ' for report template account group ' . $this->getAdvancedVarianceLineItemObj()->reportTemplateAccountGroup->report_template_account_group_name)
                )
                ->action(
                    'View Advanced Variance',
                    $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->id . '?pureid=' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->property_id . '&uuid=' . $this->id
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
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'advanced_variance_line_item_id' => $this->getAdvancedVarianceLineItemObj()->id,
            'recipient_id_arr'               => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'detacher_user_id'               => $this->detacher_user_id,
            'attachment_name'                => $this->attachment_name,
        ];
    }
}
