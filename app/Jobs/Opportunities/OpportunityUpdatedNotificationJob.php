<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Notifications\OpportunityUpdatedNotification;
use App\Waypoint\Notifications\Notification;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityUpdatedNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $opportunity_updated_notification_arr;

    /**
     * Create a new job instance.
     *
     * OpportunityUpdatedNotificationJob constructor.
     * @param array $model_arr
     * JobException
     */
    public function __construct($opportunity_updated_notification_arr)
    {
        $this->opportunity_updated_notification_arr = $opportunity_updated_notification_arr;
    }

    /**
     * JobException
     */
    public function handle()
    {
        try
        {
            /** @var OpportunityRepository $OpportunityRepositoryObj */
            $OpportunityRepositoryObj = App::make(OpportunityRepository::class)->setSuppressEvents(true);
            if ( ! $OpportunityObj = $OpportunityRepositoryObj->findWithoutFail($this->opportunity_updated_notification_arr['opportunity_id']))
            {
                throw new JobException('Failed to find opportunity -' . print_r($this->opportunity_updated_notification_arr, 1) . ' in ' . __CLASS__);
            }

            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($recipient_id)
                    {
                        return App\Waypoint\Models\User::find($recipient_id);
                    },
                    $this->opportunity_updated_notification_arr['recipient_id_arr']
                )
            );

            Notification::send(
                $RecipientObjArr,
                new OpportunityUpdatedNotification(
                    $OpportunityObj,
                    $RecipientObjArr->getArrayOfIDs()
                )
            );
        }
        catch (App\Waypoint\Exceptions\GeneralException $e)
        {
            throw  $e;
        }
        catch (\Exception $e)
        {
            throw new JobException('Failed to find opportunity -' . print_r($this->opportunity_updated_notification_arr, 1) . ' in ' . __CLASS__, 404, $e);
        }
    }
}
