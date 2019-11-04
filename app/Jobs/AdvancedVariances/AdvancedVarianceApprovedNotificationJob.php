<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceSlimRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Notifications\AdvancedVarianceApprovedNotification;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceApprovedNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $advanced_variance_approved_notification_arr;

    /**
     * @param array $model_arr
     * @throws JobException
     */
    public function __construct($advanced_variance_approved_notification_arr)
    {
        $this->advanced_variance_approved_notification_arr = $advanced_variance_approved_notification_arr;
    }

    /**
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            /** @var AdvancedVarianceRepository $AdvancedVarianceSlimRepositoryObj */
            $AdvancedVarianceSlimRepositoryObj = App::make(AdvancedVarianceSlimRepository::class)->setSuppressEvents(true);
            if ( ! $AdvancedVarianceObj = $AdvancedVarianceSlimRepositoryObj->findWithoutFail(
                $this->advanced_variance_approved_notification_arr['advanced_variance_id']
            ))
            {
                throw new JobException('Failed to find advanced_variance -' . print_r($this->advanced_variance_approved_notification_arr, 1) . ' in ' . __CLASS__);
            }
            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($val)
                    {
                        return User::find($val);
                    },
                    $this->advanced_variance_approved_notification_arr['recipient_id_arr']
                )
            );
            Notification::send(
                $RecipientObjArr,
                new AdvancedVarianceApprovedNotification(
                    $AdvancedVarianceObj,
                    $RecipientObjArr->getArrayOfIDs(),
                    $this->advanced_variance_approved_notification_arr['approver_display_name']
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
