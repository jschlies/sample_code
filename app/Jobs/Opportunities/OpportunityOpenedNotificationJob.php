<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Notifications\OpportunityOpenedNotification;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class OpportunityOpenedNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $opportunity_opened_notification_arr;

    /**
     * Create a new job instance.
     *
     * OpportunityOpenedNotificationJob constructor.
     * @param array $model_arr
     * JobException
     */
    public function __construct($opportunity_opened_notification_arr)
    {
        $this->opportunity_opened_notification_arr = $opportunity_opened_notification_arr;
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
            if ( ! $OpportunityObj = $OpportunityRepositoryObj->findWithoutFail($this->opportunity_opened_notification_arr['opportunity_id']))
            {
                throw new JobException('Failed to find opportunity -' . print_r($this->opportunity_opened_notification_arr, 1) . ' in ' . __CLASS__);
            }
            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($val)
                    {
                        return App\Waypoint\Models\User::find($val);
                    },
                    $this->opportunity_opened_notification_arr['recipient_id_arr']
                )
            );
            Notification::send($RecipientObjArr, new OpportunityOpenedNotification($OpportunityObj, $RecipientObjArr->getArrayOfIDs()));
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
