<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceSlim;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceApprovedNotification extends AdvancedVarianceNotificationBase
{
    /** @var  string */
    public $approver_display_name;

    protected $period;

    /**
     * AdvancedVarianceApprovedNotification constructor.
     * @param AdvancedVarianceSlim $AdvancedVarianceObj
     * @param array $recipient_user_id_arr
     * @param string $approver_display_name
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj, $recipient_user_id_arr = [], $approver_display_name = 'unknown')
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceObj($AdvancedVarianceObj);
        $this->approver_display_name = $approver_display_name;

        /**
         * who should get copies
         */
        $this->setRecipientUserObjArr(
            waypoint_merge_collections(
                $this->getAdvancedVarianceObj()->getExpectedRecipiants(),
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
            $subject                  = $this->getAdvancedVarianceObj()->property->name . "'s " . $this->get_period_text() . ' Variance Report was approved';
            $MailMessageObj           = new MailMessage;
            $MailMessageObj->viewData = ['advanced_variance' => true];

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($this->approver_display_name . ' approved the report')
                ->action(
                    'View Report',
                    $this->getBaseNotificationUrl() . '#/property/variance/reports/' . $this->getAdvancedVarianceObj()->id . '?pureid=' .
                    $this->getAdvancedVarianceObj()->property_id . '&uuid=' . $this->id
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
            'advanced_variance_id'  => $this->getAdvancedVarianceObj()->id,
            'recipient_id_arr'      => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'approver_display_name' => $this->approver_display_name,
        ];
    }
}
