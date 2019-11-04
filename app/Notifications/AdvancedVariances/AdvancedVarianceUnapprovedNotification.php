<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceUnapprovedNotification extends AdvancedVarianceNotificationBase
{
    public $unapprover_display_name;

    /**
     * @return void
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj, $recipient_user_id_arr = [], $unapprover_display_name = 'unknown')
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceObj($AdvancedVarianceObj);
        $this->unapprover_display_name = $unapprover_display_name;

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
            $subject  = $this->getAdvancedVarianceObj()->property->name . '\'s ' .
                        $this->getAdvancedVarianceObj()->as_of_month . '/' .
                        $this->getAdvancedVarianceObj()->as_of_year . ' Variance Report has been unapproved';
            $greeting = 'Advanced Variance has been APPROVED by ' . $this->unapprover_display_name;
            return (new MailMessage)
                ->subject($subject)
                ->greeting($greeting)
                ->action('View Advanced Variance',
                         $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                         $this->getAdvancedVarianceObj()->id . '?pureid=' .
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
            'advanced_variance_id'    => $this->getAdvancedVarianceObj()->id,
            'recipient_id_arr'        => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'unapprover_display_name' => $this->unapprover_display_name,
        ];
    }
}
