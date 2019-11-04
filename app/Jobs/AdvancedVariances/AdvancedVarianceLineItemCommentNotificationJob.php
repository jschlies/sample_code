<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Notifications\AdvancedVarianceLineItemCommentNotification;
use App\Waypoint\Notifications\Notification;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceLineItemCommentNotificationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var  [] */
    private $advanced_variance_line_item_comment_notification_arr;

    /**
     * @param array $model_arr
     * JobException
     */
    public function __construct($advanced_variance_line_item_comment_notification_arr)
    {
        $this->advanced_variance_line_item_comment_notification_arr = $advanced_variance_line_item_comment_notification_arr;
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
                $this->advanced_variance_line_item_comment_notification_arr['advanced_variance_line_item_id']
            ))
            {
                throw new JobException('Failed to find advanced_variance -' . print_r($this->advanced_variance_line_item_comment_notification_arr, 1) . ' in ' . __CLASS__);
            }
            $RecipientObjArr = collect_waypoint(
                array_map(
                    function ($val)
                    {
                        return User::find($val);
                    },
                    $this->advanced_variance_line_item_comment_notification_arr['recipient_id_arr']
                )
            );
            Notification::send(
                $RecipientObjArr,
                new AdvancedVarianceLineItemCommentNotification(
                    $AdvancedVarianceLineItemObj,
                    $RecipientObjArr->getArrayOfIDs(),
                    $this->advanced_variance_line_item_comment_notification_arr['commetor_display_name'],
                    $this->advanced_variance_line_item_comment_notification_arr['commetor_text']
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
