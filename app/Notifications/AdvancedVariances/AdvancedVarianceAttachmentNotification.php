<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceAttachmentNotification extends AdvancedVarianceNotificationBase
{
    /**
     * @return void
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj, $recipient_user_id_arr = null)
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceObj($AdvancedVarianceObj);

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
            return (new MailMessage)
                ->subject(
                    'Advanced Variance has a new ATTACHMENT for property ' . $this->getAdvancedVarianceObj()->property->name
                )
                ->greeting('Advanced Variance has a new ATTACHMENT')
                ->line('Hi ' . $NotifiableUserObj->firstname . ', ')
                ->line(
                    'An advanced variance has a new ATTACHMENT for property ' . $this->getAdvancedVarianceObj()->property->name .
                    ' for ' . ($this->getAdvancedVarianceObj()->period_type == AdvancedVariance::PERIOD_TYPE_QUARTERLY ? 'quarter beginning ' : 'month ') .
                    $this->getAdvancedVarianceObj()->as_of_month . '/' . $this->getAdvancedVarianceObj()->as_of_year
                )
                ->action(
                    'View Advanced Variance',
                    $this->getBaseNotificationUrl() .
                    '#/property/variance/reports/' . $this->getAdvancedVarianceObj()->id . '?pureid=' . $this->getAdvancedVarianceObj()->property_id . '&uuid=' . $this->id
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
            'advanced_variance_id' => $this->getAdvancedVarianceObj()->id,
            'recipient_id_arr'     => $this->getRecipientUserObjArr()->getArrayOfIDs(),
        ];
    }
}
