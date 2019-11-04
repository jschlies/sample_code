<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceLineItemCommentNotification extends AdvancedVarianceNotificationBase
{
    /** @var  string */
    public $commetor_display_name;
    /** @var  string */
    private $comment_text;

    /**
     * @return void
     */
    public function __construct(
        AdvancedVarianceLineItem $AdvancedVarianceLineItemObj,
        $recipient_user_id_arr = [],
        $commetor_display_name = null,
        $comment_text = null
    ) {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceLineItemObj($AdvancedVarianceLineItemObj);
        $this->commetor_display_name = $commetor_display_name;
        $this->comment_text          = $comment_text;

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
                                        $this->get_period_text_for_advanced_variance_line_item() .
                                        ' Variance Report has a new comment';
            $MailMessageObj           = new MailMessage;
            $MailMessageObj->viewData = ['advanced_variance' => true];
            $action_button_url        = $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->id . '?pureid=' .
                                        $this->getAdvancedVarianceLineItemObj()->advancedVariance->property_id . '&uuid=' . $this->id;

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($this->commetor_display_name . ' commented on ' . $this->get_account_name_from_advanced_variance_line_item())
                ->line('<blockquote>' . $this->replaceUserIdsWithNames($this->comment_text) . '</blockquote>')
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
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'advanced_variance_line_item_id' => $this->getAdvancedVarianceLineItemObj()->id,
            'recipient_id_arr'               => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'commetor_display_name'          => $this->commetor_display_name,
            'commetor_text'                  => $this->comment_text,
        ];
    }
}
