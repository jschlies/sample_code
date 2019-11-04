<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\Ledger\NativeCoaLedgerRepository;

class AdvancedVarianceLineItemExplanationNotification extends AdvancedVarianceNotificationBase
{
    /** @var  string */
    public $explainer_display_name;

    /**
     * @return void
     */
    public function __construct(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $recipient_user_id_arr = [], $explainer_display_name = 'unknown')
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceLineItemObj($AdvancedVarianceLineItemObj);
        $this->explainer_display_name = $explainer_display_name;

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
            $subject                  = $this->getAdvancedVarianceLineItemObj()->advancedVariance->property->name . "'s " .
                                        $this->get_period_text() . ' Variance Report has a new explanation';
            $MailMessageObj           = new MailMessage();
            $MailMessageObj->viewData = ['advanced_variance' => true];
            $action_button_url        = $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->id . '?pureid=' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->property_id . '&uuid=' . $this->id;

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($this->explainer_display_name . ' entered an explanation for ' . $this->get_account_name_from_advanced_variance_line_item())
                ->action('View Report', $action_button_url);
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
     * @return string
     */
    protected function get_period_text()
    {
        return $this->getAdvancedVarianceLineItemObj()->advancedVariance->period_type == AdvancedVariance::PERIOD_TYPE_MONTHLY
            ?
            $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month . '/' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year
            :
            'Q' . NativeCoaLedgerRepository::MONTHS_QUARTERS_LOOKUP[$this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month] . ' ' .
            $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year;
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
            'explainer_display_name'         => $this->explainer_display_name,
        ];
    }
}
