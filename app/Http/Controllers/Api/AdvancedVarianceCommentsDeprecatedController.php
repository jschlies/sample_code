<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\AdvancedVarianceLineItemCommentEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Api\CreateCommentRequest;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\CommentRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App;
use function collect_waypoint;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * Class AdvancedVarianceCommentsDeprecatedController
 * @codeCoverageIgnore
 */
class AdvancedVarianceCommentsDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceLineItemRepositoryObj;
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;
    /** @var  CommentRepository */
    private $CommentRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceRepositoryObj         = $AdvancedVarianceRepositoryObj;
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);
        $this->RelatedUserRepositoryObj              = App::make(RelatedUserRepository::class);
        $this->CommentRepositoryObj                  = App::make(CommentRepository::class);
        parent::__construct($AdvancedVarianceRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return JsonResponse|null
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAdvancedVarianceLineItemComments($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemObj =
            $this->AdvancedVarianceLineItemRepositoryObj
                ->with('comments')
                ->find($advanced_variance_line_item_id);

        $CommentsObjArr = $AdvancedVarianceLineItemObj->getComments();

        return $this->sendResponse($CommentsObjArr, 'AdvancedVarianceLineItemComments(s) retrieved successfully');
    }

    /**
     * Store a newly created Comment in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @param CreateCommentRequest $CommentCreateRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function storeAdvancedVarianceLineItemComments(
        $client_id,
        $property_id,
        $advanced_variance_id,
        $advanced_variance_line_item_id,
        CreateCommentRequest $CommentCreateRequestObj
    ) {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj
            ->with('advancedVariance')
            ->find($advanced_variance_line_item_id);

        if ($AdvancedVarianceLineItemObj->advancedVariance->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance is locked');
        }

        $input                     = $CommentCreateRequestObj->all();
        $input['commentable_id']   = $advanced_variance_line_item_id;
        $input['commentable_type'] = AdvancedVarianceLineItem::class;
        if ( ! isset($input['comment']) || empty($input['comment']) || ! $input['comment'])
        {
            throw new GeneralException('invalid comment');
        }

        $input['commented_id'] = $this->getCurrentLoggedInUserObj()->id;
        try
        {
            /** @var User $CommentedUserObj */
            $CommentedUserObj = $this->CommentRepositoryObj->create($input);

            /** @var AdvancedVariance $AdvancedVarianceObj */
            $AdvancedVarianceObj = $AdvancedVarianceLineItemObj->advancedVariance;
            $AdvancedVarianceObj->add_reviewer($this->getCurrentLoggedInUserObj()->id);

            /**
             * $input['mentions'] is a list if user_id - mentioned; Should be passed from frontend but just in case
             */
            if ( ! isset($input['mentions']) || ! is_array($input['mentions']))
            {
                /**
                 * pluck mentioned users just in case
                 */
                $input['mentions'] = [];
                preg_match_all("/\[\~(\d*)\]/", $input['comment'], $gleaned_arr);
                foreach ($gleaned_arr[1] as $mentioned_user_id)
                {
                    $input['mentions'][] = $mentioned_user_id;
                }
            }

            $recipient_id_arr = array_merge(
                $AdvancedVarianceObj->getExpectedRecipiants()->pluck('id')->toArray(),
                $input['mentions']
            );

            $this->post_job_to_queue(
                [
                    'advanced_variance_id'           => $AdvancedVarianceObj->id,
                    'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                    'as_of_month'                    => $AdvancedVarianceObj->as_of_month,
                    'as_of_year'                     => $AdvancedVarianceObj->as_of_year,
                    'recipient_id_arr'               => $recipient_id_arr,
                    'commetor_display_name'          => $this->getCurrentLoggedInUserObj()->getDisplayName(),
                    'commetor_text'                  => $input['comment'],
                ],
                App\Waypoint\Jobs\AdvancedVarianceLineItemCommentNotificationJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemCommentNotification', false)
            );
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }

        /**
         * we presume the last comment is the one we just created
         */
        return $this->sendResponse(
            collect_waypoint(
                [
                    $CommentedUserObj->comments
                        ->sort(
                            function ($item)
                            {
                                return $item->created_at->toDateTimeString();
                            }
                        )->last(),
                ]
            ),
            'Comment saved successfully'
        );
    }

    /**
     * Remove the specified Comment from storage.
     * DELETE /opportunities/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @param integer $comment_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroyAdvancedVarianceLineItemComment($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id, $comment_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }
        $this->CommentRepositoryObj->delete($comment_id);
        return $this->sendResponse($comment_id, 'Comment deleted successfully');
    }
}
