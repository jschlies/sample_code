<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceLockedNotification extends AdvancedVarianceNotificationBase
{
    /**
     * @return void
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj, $recipient_user_id_arr = [])
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
     * @param $NotifiableUserObj
     * @return MailMessage
     * @throws GeneralException
     * @throws JobException
     */
    public function toMail($NotifiableUserObj)
    {
        try
        {
            $subject                  = $this->getAdvancedVarianceObj()->property->name . '\'s ' . $this->get_period_text() . ' Variance Report was locked';
            $MailMessageObj           = new MailMessage;
            $MailMessageObj->viewData = ['advanced_variance' => true];
            $action_button_url        = $this->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                        $this->getAdvancedVarianceObj()->id . '?pureid=' .
                                        $this->getAdvancedVarianceObj()->property_id . '&uuid=' . $this->id;

            /**
             * this is a temp fix. We have lots of race conditions in our system. In this case, while the Notification job has been sitting on queue, the
             * AdvancedVariance in question has been unlocked - See HER-4003
             * @todo fix me
             */
            $locked_string = 'Report is locked';
            if($this->getAdvancedVarianceObj()->lockerUser)
            {
                $locked_string = $this->getAdvancedVarianceObj()->lockerUser->getDisplayName() . ' locked the report';
            }

            return ($MailMessageObj)
                ->subject($subject)
                ->greeting($locked_string)
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
            'advanced_variance_id' => $this->getAdvancedVarianceObj()->id,
            'recipient_id_arr'     => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'model_name'           => AdvancedVarianceLockedNotification::class,
        ];
    }
}
