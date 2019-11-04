<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Api\CreateCommentRequest;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\CommentRepository;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Notifications\Notifiable;
use Response;

/**
 * Class OpportunityCommentDeprecatedController
 * @codeCoverageIgnore
 */
class OpportunityCommentDeprecatedController extends BaseApiController
{
    use Notifiable;

    /** @var  OpportunityRepository */
    private $OpportunityRepositoryObj;
    /** @var  CommentRepository */
    private $CommentRepositoryObj;

    public function __construct(OpportunityRepository $OpportunityRepositoryObj)
    {
        $this->OpportunityRepositoryObj = $OpportunityRepositoryObj;
        $this->CommentRepositoryObj     = App::make(CommentRepository::class);
        parent::__construct($this->OpportunityRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showOpportunityComments($client_id, $property_id, $opportunity_id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);
        if ( ! $OpportunityObj)
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj->getComments(), 'Comment(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $opportunity_id
     * @param CreateCommentRequest $CommentCreateRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non-standard route - fix me
     */
    public function storeOpportunityComments($client_id, $property_id, $opportunity_id, CreateCommentRequest $CommentCreateRequestObj)
    {
        $input                   = $CommentCreateRequestObj->all();
        $input['client_id']      = $client_id;
        $input['commentable_id'] = $opportunity_id;
        if ( ! isset($input['commentable_id']) || ! $input['commentable_id'])
        {
            throw new GeneralException('invalid commentable_id');
        }
        $input['commentable_type'] = Opportunity::class;
        if ( ! isset($input['comment']) || empty($input['comment']) || ! $input['comment'])
        {
            throw new GeneralException('invalid comment');
        }
        $input['commented_id'] = $this->getCurrentLoggedInUserObj()->id;

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
        try
        {
            /** @var User $CommentedUserObj */
            $CommentedUserObj = $this->CommentRepositoryObj->create($input);

            $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);

            $this->post_job_to_queue(
                [
                    'client_id'             => $OpportunityObj->client_id,
                    'opportunity_id'        => $OpportunityObj->id,
                    'recipient_id_arr'      => [
                        $this->getCurrentLoggedInUserObj()->id,
                        $OpportunityObj->assignedToUser->id,
                        $OpportunityObj->createdByUser->id,
                    ],
                    'comment'               => $input['comment'],
                    'commenter_id'          => $this->getCurrentLoggedInUserObj()->id,
                    'mentioned_user_id_arr' => $input['mentions'],
                ],
                App\Waypoint\Jobs\OpportunityCommentedNotificationJob::class,
                config('queue.queue_lanes.OpportunityCommentedNotification', false)
            );
        }
        catch (Exception $e)
        {
            throw new GeneralException('invalid parameters');
        }

        /**
         * we presume the last comment is the one we just created
         */
        return $this->sendResponse(
            collect_waypoint(
                [$CommentedUserObj->comments
                     ->sort(
                         function ($item)
                         {
                             return $item->created_at->toDateTimeString();
                         }
                         )->last()
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
     * @param integer $opportunity_id
     * @param integer $comment_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroyOpportunityComment($client_id, $property_id, $opportunity_id, $comment_id)
    {
        $this->CommentRepositoryObj->delete($comment_id);

        return $this->sendResponse($comment_id, 'Comment deleted successfully');
    }
}
