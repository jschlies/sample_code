<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Repositories\UserInvitationRepository;
use function collect_waypoint;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Notifications\InvitationNotification;

/**
 * Class InvitationNotificationJob
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class InvitationNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $invitation_notification_arr;

    /**
     * Create a new job instance.
     *
     * @param $invitation_notification_arr
     */
    public function __construct($invitation_notification_arr)
    {
        $this->invitation_notification_arr = $invitation_notification_arr;
    }

    /**
     * @throws GeneralException
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            $UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);
            $UserInvitationObj           = $UserInvitationRepositoryObj->find(
                $this->invitation_notification_arr['user_invitation_id']
            );

            $RecipientObjArr = collect_waypoint([$UserInvitationObj->inviteeUser]);

            Notification::send(
                $RecipientObjArr,
                new InvitationNotification(
                    $UserInvitationObj,
                    $RecipientObjArr->getArrayOfIDs()
                )
            );
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
        catch (\Exception $e)
        {
            throw new JobException(__CLASS__, 404, $e);
        }
    }
}
