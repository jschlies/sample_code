<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Notifications\OpportunityCommentedNotification;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityCommentedNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $opportunity_commented_notification_arr;

    /**
     * Create a new job instance.
     *
     * OpportunityCommentedNotificationJob constructor.
     * @param $opportunity_commented_notification_arr
     */
    public function __construct($opportunity_commented_notification_arr)
    {
        $this->opportunity_commented_notification_arr = $opportunity_commented_notification_arr;
    }

    /**
     * @throws GeneralException
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            /** @var OpportunityRepository $OpportunityRepositoryObj */
            $OpportunityRepositoryObj = App::make(OpportunityRepository::class)->setSuppressEvents(true);

            if ( ! $OpportunityObj = $OpportunityRepositoryObj->findWithoutFail($this->opportunity_commented_notification_arr['opportunity_id']))
            {
                throw new JobException('Failed to find opportunity -' . print_r($this->opportunity_commented_notification_arr, 1) . ' in ' . __CLASS__);
            }

            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($recipient_id)
                    {
                        return App\Waypoint\Models\User::find($recipient_id);
                    },
                    array_unique(array_merge($this->opportunity_commented_notification_arr['recipient_id_arr'],$this->opportunity_commented_notification_arr['mentioned_user_id_arr']))
                )
            );

            Notification::send(
                $RecipientObjArr,
                new OpportunityCommentedNotification(
                    $OpportunityObj,
                    $this->opportunity_commented_notification_arr['comment'],
                    $this->opportunity_commented_notification_arr['commenter_id'],
                    $RecipientObjArr->getArrayOfIDs(),
                    $this->opportunity_commented_notification_arr['mentioned_user_id_arr']
                )
            );
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
}
