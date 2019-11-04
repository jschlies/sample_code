<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Notifications\AdvancedVarianceLineItemResolvedNotification;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceLineItemResolvedNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $advanced_variance_resolved_line_item_notification_arr;

    /**
     * @param array $model_arr
     * JobException
     */
    public function __construct($advanced_variance_line_item_resolved_notification_arr)
    {
        $this->advanced_variance_resolved_line_item_notification_arr = $advanced_variance_line_item_resolved_notification_arr;
    }

    /**
     * JobException
     */
    public function handle()
    {
        try
        {
            /** @var AdvancedVarianceLineItemRepository $AdvancedVarianceLineItemRepositoryObj */
            $AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class)->setSuppressEvents(true);
            if ( ! $AdvancedVarianceLineItemObj = $AdvancedVarianceLineItemRepositoryObj->findWithoutFail(
                $this->advanced_variance_resolved_line_item_notification_arr['advanced_variance_line_item_id']))
            {
                throw new JobException(
                    'Failed to find advanced_variance line item -' .
                    print_r($this->advanced_variance_resolved_line_item_notification_arr, 1) .
                    ' in ' . __CLASS__);
            }
            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($val)
                    {
                        return User::find($val);
                    },
                    $this->advanced_variance_resolved_line_item_notification_arr['recipient_id_arr']
                )
            );
            Notification::send(
                $RecipientObjArr,
                new AdvancedVarianceLineItemResolvedNotification(
                    $AdvancedVarianceLineItemObj,
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
