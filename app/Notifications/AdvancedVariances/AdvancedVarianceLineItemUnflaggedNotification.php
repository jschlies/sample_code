<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceLineItemUnflaggedNotification extends AdvancedVarianceNotificationBase
{
    /** @var  string */
    private $unflagger_display_name;

    /**
     * @return void
     */
    public function __construct(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $recipient_user_id_arr = [], $unflagger_display_name = 'unknown')
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceLineItemObj($AdvancedVarianceLineItemObj);
        $this->unflagger_display_name = $unflagger_display_name;

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
            $subject  = 'Account was unmarked on ' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->property->name . '\'s ' .
                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month . '/' .
                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year . ' Variance Report';
            $greeting = $this->unflagger_display_name . ' has unmarked ' .
                        ($this->getAdvancedVarianceLineItemObj()->nativeAccount
                            ?
                            ' for account code ' . $this->getAdvancedVarianceLineItemObj()->nativeAccount->native_account_code
                            :
                            ' for report template account group ' . $this->getAdvancedVarianceLineItemObj()->reportTemplateAccountGroup->report_template_account_group_name);
            return (new MailMessage)
                ->subject($subject)
                ->greeting($greeting)
                ->action(
                    'View Advanced Variance',
                    $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->id . '?pureid=' .
                    $this->getAdvancedVarianceLineItemObj()->advancedVariance->property_id . '&uuid=' . $this->id)
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
            'unflagger_display_name'         => $this->unflagger_display_name,
        ];
    }
}