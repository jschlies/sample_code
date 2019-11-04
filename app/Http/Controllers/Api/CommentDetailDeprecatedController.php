<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Api\CreateCommentRequest;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Comment;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Suite;
use App\Waypoint\Repositories\CommentDetailRepository;
use App\Waypoint\Repositories\CommentRepository;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class CommentDetailDeprecatedController
 * @codeCoverageIgnore
 */
class CommentDetailDeprecatedController extends BaseApiController
{
    /** @var  CommentDetailRepository */
    private $CommentDetailRepositoryObj;

    /**
     * CommentController constructor.
     * @param CommentRepository $CommentDetailRepositoryObj
     */
    public function __construct(CommentDetailRepository $CommentDetailRepositoryObj)
    {
        $this->CommentDetailRepositoryObj = $CommentDetailRepositoryObj;
        parent::__construct($CommentDetailRepositoryObj);
    }

    /**
     * @param integer $comment_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show($client_id, $comment_id)
    {
        /** @var Comment $CommentObj */
        $CommentObj = $this->CommentDetailRepositoryObj->findWithoutFail($comment_id);
        if (empty($CommentObj))
        {
            return Response::json(ResponseUtil::makeError('Comment not found'), 404);
        }
        return $this->sendResponse($CommentObj, 'Comment retrieved successfully');
    }

    /**
     * @param Request $request
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * $CommentedUserObj - the user making the comment
     * commented_id - the id of $CommentedUserObj
     * commented_type - the type of the thing doing the commeting - should always be "App\\Waypoint\\Models\\User"
     * commentable_type - the kink of thing we are commenting on, ie "App\\Waypoint\\Models\\Lease" or "App\\Waypoint\\Models\\Suite"
     * commentable_id - id of the thing (sease/suite) that the commetn was made on
     * $CommentableObj - the thing (sease/suite) that the commetn was made on
     */
    public function index(Request $request, $client_id, $commentable_type, $commentable_id)
    {
        $this->CommentDetailRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->CommentDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));

        $fq_commentable_type = 'App\Waypoint\Models\\' . $commentable_type;

        /** @var Suite|Lease| $CommentableObj */
        if ( ! $CommentableObj = $fq_commentable_type::find($commentable_id))
        {
            return Response::json(ResponseUtil::makeError($commentable_type . ' not found'), 404);
        }
        $CommentDetailArr = new Collection();
        foreach ($CommentableObj->comments->pluck('id')->toArray() as $id)
        {
            $CommentDetailArr[] = $this->CommentDetailRepositoryObj->find($id);
        }
        return $this->sendResponse($CommentDetailArr, 'CommentDetail(s) retrieved successfully');
    }

    /**
     * Store a newly created AssetType in storage.
     *
     * @param Request $AssetTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(CreateCommentRequest $CreateCommentRequestObj, $client_id, $commentable_type, $commentable_id)
    {
        $input                     = $CreateCommentRequestObj->all();
        $input['commentable_type'] = $commentable_type;
        $input['commentable_id']   = $commentable_id;
        if ( ! isset($input['commented_id']) || ! $input['commented_id'])
        {
            $input['commented_id'] = $this->getCurrentLoggedInUserObj()->id;
        }
        $CommentDetailObj = $this->CommentDetailRepositoryObj->create($input);

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

        /**
         * special code for various models - notifications and such
         */
        if ($input['commentable_type'] == Model::getShortModelNameFromModelName(Opportunity::class))
        {
            /** @var Opportunity $OpportunityObj */
            $OpportunityObj = Opportunity::find($commentable_id);
            $ExpectedReciepiantsUserObjArr = $OpportunityObj->getExpectedRecipiants();
            $ExpectedReciepiantsUserObjArr[] = $this->getCurrentLoggedInUserObj();
            $this->post_job_to_queue(
                [
                    'opportunity_id'        => $commentable_id,
                    'recipient_id_arr'               => $ExpectedReciepiantsUserObjArr->pluck('id')->toArray(),
                    'comment'               => $input['comment'],
                    'commenter_id'          => $this->getCurrentLoggedInUserObj()->id,
                    'mentioned_user_id_arr' => $input['mentions'],
                ],
                \App\Waypoint\Jobs\OpportunityCommentedNotificationJob::class,
                config('queue.queue_lanes.OpportunityCommentedNotification', false)
            );
        }
        elseif ($input['commentable_type'] == Model::getShortModelNameFromModelName(AdvancedVarianceLineItem::class))
        {
            /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
            $AdvancedVarianceLineItemObj = AdvancedVarianceLineItem::find($commentable_id);
            /** @var AdvancedVariance $AdvancedVarianceObj */
            $AdvancedVarianceObj = $AdvancedVarianceLineItemObj->advancedVariance;
            $ExpectedReciepiantsUserObjArr = $AdvancedVarianceObj->getExpectedRecipiants();
            $ExpectedReciepiantsUserObjArr[] = $this->getCurrentLoggedInUserObj();
            $this->post_job_to_queue(
                [
                    'advanced_variance_line_item_id' => $commentable_id,
                    'recipient_id_arr'               => $ExpectedReciepiantsUserObjArr->pluck('id')->toArray(),
                    'comment'                        => $input['comment'],
                    'commetor_display_name'          => $this->getCurrentLoggedInUserObj()->getDisplayName(),
                    'mentioned_user_id_arr'          => $input['mentions'],
                ],
                \App\Waypoint\Jobs\AdvancedVarianceLineItemCommentNotificationJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemCommentNotification', false)
            );
        }

        return $this->sendResponse($CommentDetailObj, 'Comment saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $comment_id)
    {
        /** @var Comment $CommentObj */
        $CommentObj = $this->CommentDetailRepositoryObj->findWithoutFail($comment_id);
        if (empty($CommentObj))
        {
            return Response::json(ResponseUtil::makeError('Comment not found'), 404);
        }
        $this->CommentDetailRepositoryObj->delete($CommentObj->id);

        return $this->sendResponse($CommentObj->id, 'Comment deleted successfully');
    }
}