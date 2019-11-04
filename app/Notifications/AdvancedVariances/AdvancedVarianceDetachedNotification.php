<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;

class AdvancedVarianceDetachedNotification extends AdvancedVarianceNotificationBase
{
    use Queueable;

    private $detacher_user_id;
    private $DetacherUserObj;
    private $attachment_name;

    /**
     * @return void
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj, $attachment_name, $detacher_user_id, array $recipient_user_id_arr = [])
    {
        /**
         * @todo move all this to AdvancedVarianceNotificationBase when we do NotificationEnvelopes
         */

        $this->setAdvancedVarianceObj($AdvancedVarianceObj);
        $this->detacher_user_id = $detacher_user_id;
        $this->attachment_name  = $attachment_name;
        $this->DetacherUserObj  = User::find($detacher_user_id);

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
            $isCreator       = $this->DetacherUserObj->id == $NotifiableUserObj->id;
            $DetacherUserObj = User::find($this->detacher_user_id);

            return (new MailMessage)
                ->subject(
                    $DetacherUserObj->firstname . ' ' . $DetacherUserObj->lastname . ' removed an attachment from ' . $this->getAdvancedVarianceObj()->property->name . '\'s ' . $this->getAdvancedVarianceObj()->as_of_month . '/' .
                    $this->getAdvancedVarianceObj()->as_of_year . ' Variance Report'
                )
                ->greeting('ATTACHMENT has been deleted from Advanced Variance')
                ->line('Hi ' . $NotifiableUserObj->firstname . ', ')
                ->line(
                    ($isCreator
                        ? 'You have'
                        : $this->DetacherUserObj->firstname . ' ' .
                          $this->DetacherUserObj->lastname . ' has') . ' removed an attachment from ' .
                    $this->getAdvancedVarianceObj()->property->name . '\'s ' . $this->getAdvancedVarianceObj()->as_of_month . '/' .
                    $this->getAdvancedVarianceObj()->as_of_year . ' Advanced Variance Report'
                )
                ->action(
                    'View Advanced Variance',
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
            'advanced_variance_id' => $this->getAdvancedVarianceObj()->id,
            'recipient_id_arr'     => $this->getRecipientUserObjArr()->getArrayOfIDs(),
            'detacher_user_id'     => $this->detacher_user_id,
            'attachment_name'      => $this->attachment_name,
        ];
    }
}
