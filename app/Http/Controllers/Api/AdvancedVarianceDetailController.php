<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\EventBase;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceRequest;
use App\Waypoint\Http\Requests\Generated\Api\CreateRelatedUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceLineItemRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceRequest;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVarianceDetail;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Repositories\AdvancedVarianceDetailRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemDetailRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\AdvancedVarianceSlimRepository;
use App\Waypoint\Repositories\AdvancedVarianceSummaryRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemWorkflowRepository;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\ResponseUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use Response;

/**
 * Class AdvancedVarianceDetailController
 */
class AdvancedVarianceDetailController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceSummaryRepository */
    private $AdvancedVarianceSummaryRepositoryObj;
    /** @var  AdvancedVarianceDetailRepository */
    private $AdvancedVarianceDetailRepositoryObj;
    /** @var  AdvancedVarianceLineItemRepository */
    private $AdvancedVarianceLineItemRepositoryObj;
    /** @var  AdvancedVarianceLineItemDetailRepository */
    private $AdvancedVarianceLineItemDetailRepositoryObj;
    /** @var  AdvancedVarianceApprovalRepository */
    private $AdvancedVarianceApprovalRepositoryObj;
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceApprovalRepositoryObj         = App::make(AdvancedVarianceApprovalRepository::class);
        $this->AdvancedVarianceDetailRepositoryObj           = App::make(AdvancedVarianceDetailRepository::class);
        $this->AdvancedVarianceLineItemRepositoryObj         = App::make(AdvancedVarianceLineItemRepository::class);
        $this->AdvancedVarianceLineItemDetailRepositoryObj   = App::make(AdvancedVarianceLineItemDetailRepository::class);
        $this->AdvancedVarianceRepositoryObj                 = $AdvancedVarianceRepositoryObj;
        $this->AdvancedVarianceSummaryRepositoryObj          = App::make(AdvancedVarianceSummaryRepository::class);
        $this->AdvancedVarianceLineItemWorkflowRepositoryObj = App::make(AdvancedVarianceLineItemWorkflowRepository::class);
        $this->RelatedUserRepositoryObj                      = App::make(RelatedUserRepository::class);
        parent::__construct($AdvancedVarianceRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVariance.
     *
     * @param Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceSummaryRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceSummaryRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceSummaryObjArr = $this->AdvancedVarianceSummaryRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceSummaryObjArr, 'AdvancedVariance(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function store($client_id, $property_id, CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        $input                = $AdvancedVarianceRequestObj->all();
        $input['property_id'] = $property_id;

        $AdvancedVarianceSummaryObj = $this->AdvancedVarianceSummaryRepositoryObj->create($input);
        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $AdvancedVarianceSummaryObj->id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        return $this->sendResponse($AdvancedVarianceSummaryObj, 'AdvancedVariance saved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $advancedVariance */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance retrieved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showDetail($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVarianceDetail $advancedVariance */
        $AdvancedVarianceDetailObj = $this->AdvancedVarianceDetailRepositoryObj->find($advanced_variance_id);
        if (empty($AdvancedVarianceDetailObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 400);
        }

        return $this->sendResponse($AdvancedVarianceDetailObj, 'AdvancedVariance retrieved successfully');
    }

    /**
     * Remove the specified AdvancedVariance from storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }
        $AdvancedVarianceObj->delete();

        return $this->sendResponse($advanced_variance_id, 'AdvancedVariance deleted successfully');
    }

    /**
     * @param CreateRelatedUserRequest $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function storeReviewer(CreateRelatedUserRequest $RequestObj, $client_id, $property_id, $advanced_variance_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        if ( ! $AdvancedVarianceObj = App::make(AdvancedVariance::class)->find($advanced_variance_id))
        {
            throw new ModelNotFoundException('No such AdvancedVariance');
        }
        $input = $RequestObj->all();
        /** var User $UserObj */
        if ( ! $UserObj = App::make(UserRepository::class)->find($input['user_id']))
        {
            throw new ModelNotFoundException('No such AdvancedVariance');
        }
        if ( ! $UserObj->canAccessProperty($property_id))
        {
            throw new ModelNotFoundException('Invalid user');
        }
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        $AdvancedVarianceObj->add_reviewer($input['user_id']);

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance Reviewer saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $related_user_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroyReviewer($client_id, $property_id, $advanced_variance_id, $related_user_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVariance $AdvancedVarianceObj */
        if ( ! $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }

        if ($AdvancedVarianceObj->getReviewers()->count() <= 1)
        {
            throw new GeneralException('AdvancedVariance must have at least one reviewer');
        }
        $this->RelatedUserRepositoryObj->delete($related_user_id);
        return $this->sendResponse($related_user_id, 'AdvancedVarianceReviewer deleted successfully');
    }

    /**
     * Store a newly created AdvancedVarianceApproval in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     */
    public function storeApproval($client_id, $property_id, $advanced_variance_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $AdvancedVarianceSlimObj =
            App::make(AdvancedVarianceSlimRepository::class)
               ->with('property.client')
               ->with('property.propertyGroups')
               ->with('lockerUser')
               ->find($advanced_variance_id);

        if ($AdvancedVarianceSlimObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        $AdvancedVarianceSlimObj->add_reviewer($this->getCurrentLoggedInUserObj()->id);
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->create(
            [
                "advanced_variance_id" => $advanced_variance_id,
                "approving_user_id"    => $this->getCurrentLoggedInUserObj()->id,
                "approval_date"        => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $this->post_job_to_queue(
            [
                'client_id'             => $AdvancedVarianceSlimObj->property->client_id,
                'property_id'           => $AdvancedVarianceSlimObj->property_id,
                'advanced_variance_id'  => $AdvancedVarianceSlimObj->id,
                'as_of_month'           => $AdvancedVarianceSlimObj->as_of_month,
                'as_of_year'            => $AdvancedVarianceSlimObj->as_of_year,
                'recipient_id_arr'      => $AdvancedVarianceSlimObj->getExpectedRecipiants()->pluck('id')->toArray(),
                'approver_display_name' => $AdvancedVarianceSlimObj->approver_display_name,
            ],
            App\Waypoint\Jobs\AdvancedVarianceApprovedNotificationJob::class,
            config('queue.queue_lanes.AdvancedVarianceApprovedNotification', false)
        );

        $purge_list_arr = EventBase::build_advanced_varance_purge_list($AdvancedVarianceSlimObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceSlimObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceSlimObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceSlimObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceSlimObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceSlimObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceSlimObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceSlimObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceSlimObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceApprovalObj, 'AdvancedVarianceApproval saved successfully');
    }

    /**
     * Remove the specified AdvancedVarianceApproval from storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_approval_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function destroyApproval($client_id, $property_id, $advanced_variance_id, $advanced_variance_approval_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id);

        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }

        $this->AdvancedVarianceApprovalRepositoryObj->deleteWhere(['id' => $advanced_variance_approval_id]);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        return $this->sendResponse($advanced_variance_approval_id, 'AdvancedVarianceApproval deleted successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function indexFlagged($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $advancedVariance */
        if ( ! $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );
        return $this->sendResponse($AdvancedVarianceObj->getFlagged(), 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function indexFlaggedManually($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $advancedVariance */
        if ( ! $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj->getFlaggedManually(), 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function indexFlaggedByPolicy($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $advancedVariance */
        if ( ! $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj->getFlaggedByPolicy(), 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     */
    public function updateFlagAdvancedVarianceLineItems($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $AdvancedVarianceObj =
            App::make(AdvancedVarianceRepository::class)
               ->with('property.client')
               ->with('property.propertyGroups')
               ->with('lockerUser')
               ->find($advanced_variance_id);

        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }

        $input = [
            'flagger_user_id'       => $this->getCurrentLoggedInUserObj()->id,
            'flagged_manually'      => true,
            'flagged_manually_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $AdvancedVarianceObj->add_reviewer($this->getCurrentLoggedInUserObj()->id);

        $AdvancedVarianceLineItemWorkflowObj = $this->AdvancedVarianceLineItemWorkflowRepositoryObj->update($input, $advanced_variance_line_item_id);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $this->post_job_to_queue(
            [
                'advanced_variance_id'           => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->id,
                'advanced_variance_line_item_id' => $AdvancedVarianceLineItemWorkflowObj->id,
                'as_of_month'                    => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_month,
                'as_of_year'                     => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_year,
                'recipient_id_arr'               => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemFlaggedNotificationJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemFlaggedNotification', false)
        );

        $purge_list_arr = EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceLineItemWorkflowObj->toArray(), 'AdvancedVarianceLineItemWorkflow updated flagged');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function updateUnflagAdvancedVarianceLineItems($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $AdvancedVarianceObj =
            App::make(AdvancedVarianceRepository::class)
               ->find($advanced_variance_id);

        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        $input = [
            'flagger_user_id'       => null,
            'flagged_manually'      => false,
            'flagged_manually_date' => null,
        ];

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $AdvancedVarianceLineItemWorkflowObj = $this->AdvancedVarianceLineItemWorkflowRepositoryObj->update($input, $advanced_variance_line_item_id);

        /**
         * No Notification per Peter. In fact no 'Un' anything Notification, unflag, unapprove ......
         */

        return $this->sendResponse($AdvancedVarianceLineItemWorkflowObj->toArray(), 'AdvancedVarianceLineItemWorkflow updated unflagged');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param UpdateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     */
    public function updateLock($client_id, $property_id, $advanced_variance_id, UpdateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $input = [
            'locker_user_id' => $this->getCurrentLoggedInUserObj()->id,
            'locked_date'    => Carbon::now()->format('Y-m-d H:i:s'),

        ];

        $input['advanced_variance_status'] = AdvancedVariance::ACTIVE_STATUS_LOCKED;
        $AdvancedVarianceObj               = $this->AdvancedVarianceRepositoryObj->update($input, $advanced_variance_id);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
                'as_of_month'          => $AdvancedVarianceObj->as_of_month,
                'as_of_year'           => $AdvancedVarianceObj->as_of_year,
                'recipient_id_arr'     => $AdvancedVarianceObj->getExpectedRecipiants()->pluck('id')->toArray(),
            ],
            App\Waypoint\Jobs\AdvancedVarianceLockedNotificationJob::class,
            config('queue.queue_lanes.AdvancedVarianceLockedNotification', false)
        );

        $purge_list_arr = EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj->property->client),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->property->client->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj->property->client),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->property->client->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance locked');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws ValidatorException
     */
    public function updateUnlock($client_id, $property_id, $advanced_variance_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        $input = [
            'locker_user_id'           => null,
            'locked_date'              => null,
            'advanced_variance_status' => AdvancedVariance::ACTIVE_STATUS_UNLOCKED,
        ];

        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->update($input, $advanced_variance_id);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        /**
         * No Notification per Peter. In fact no 'Un' anything Notification, unflag, unapprove ......
         */

        $purge_list_arr = App\Waypoint\Events\EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => App\Waypoint\Events\EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => App\Waypoint\Events\EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance updated flagged');
    }

    /**
     * Display the specified AdvancedVariance.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function indexAdvancedVariancesPerProperty(UpdateAdvancedVarianceRequest $AdvancedVarianceRequestObj, $client_id, $property_id)
    {
        $input = $AdvancedVarianceRequestObj->all();
        /** @var Property $PropertyObj */
        $PropertyObj = App::make(PropertyRepository::class)->find($property_id);
        if (
            isset($input['as_of_year']) &&
            isset($input['as_of_month'])
        )
        {
            $key                       = 'advancedVarianceSummaries_property_' . $property_id . '_' . $input['as_of_year'] . '_' . $input['as_of_month'];
            $advancedVarianceSummaries = $PropertyObj->getPreCalcValue($key);
            if ($advancedVarianceSummaries === null)
            {
                $advancedVarianceSummaries = $PropertyObj->advancedVarianceSummaries
                    ->where('as_of_year', '=', $input['as_of_year'])
                    ->where('as_of_month', '=', $input['as_of_month'])->toArray();

                $PropertyObj->updatePreCalcValue(
                    $key,
                    $advancedVarianceSummaries
                );
            }
        }
        else
        {
            $key                       = 'advancedVarianceSummaries_property_' . $property_id;
            $advancedVarianceSummaries = $PropertyObj->getPreCalcValue($key);
            if ($advancedVarianceSummaries === null)
            {
                $advancedVarianceSummaries = $PropertyObj->advancedVarianceSummaries->toArray();

                $PropertyObj->updatePreCalcValue(
                    $key,
                    $advancedVarianceSummaries
                );
            }
        }
        return $this->sendResponse($advancedVarianceSummaries, 'AdvancedVarianceSummary(s) retrieved successfully');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function updateResolveAdvancedVarianceLineItems($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemWorkflowObj = App::make(AdvancedVarianceLineItemWorkflowRepository::class)
                                                  ->with('advancedVariance')
                                                  ->find($advanced_variance_line_item_id);

        if ($AdvancedVarianceLineItemWorkflowObj->advancedVariance->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        if ( ! $AdvancedVarianceLineItemWorkflowObj->explanation)
        {
            throw new GeneralException('explanation is blank');
        }
        $input = [
            'resolver_user_id' => $this->getCurrentLoggedInUserObj()->id,
            'resolved_date'    => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $AdvancedVarianceLineItemWorkflowObj = $this->AdvancedVarianceLineItemWorkflowRepositoryObj->update($input, $advanced_variance_line_item_id);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $this->post_job_to_queue(
            [
                'advanced_variance_id'           => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->id,
                'advanced_variance_line_item_id' => $AdvancedVarianceLineItemWorkflowObj->id,
                'as_of_month'                    => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_month,
                'as_of_year'                     => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_year,
                'recipient_id_arr'               => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemResolvedNotificationJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemResolvedNotification', false)
        );
        $AdvancedVarianceObj = $AdvancedVarianceLineItemWorkflowObj->advancedVariance;

        $purge_list_arr = EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],
                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceLineItemWorkflowObj->toArray(), 'AdvancedVarianceLineItemWorkflow resolved');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function updateUnresolveAdvancedVarianceLineItems($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id);

        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance  is locked');
        }
        $input = [
            'resolver_user_id' => null,
            'resolved_date'    => null,
        ];

        $AdvancedVarianceLineItemWorkflowObj = $this->AdvancedVarianceLineItemWorkflowRepositoryObj->update($input, $advanced_variance_line_item_id);

        /**
         * No Notification per Peter. In fact no 'Un' anything Notification, unflag, unapprove ......
         */

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        $purge_list_arr = EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );

        return $this->sendResponse($AdvancedVarianceLineItemWorkflowObj->toArray(), 'AdvancedVarianceLineItemWorkflow updated unresolved');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @param UpdateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function updateExplanationAdvancedVarianceLineItems(
        $client_id,
        $property_id,
        $advanced_variance_id,
        $advanced_variance_line_item_id,
        UpdateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj
    ) {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = App::make(AdvancedVarianceRepository::class)
                                  ->find($advanced_variance_id);

        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance is locked');
        }

        $input = $AdvancedVarianceLineItemRequestObj->all();

        $AdvancedVarianceLineItemWorkflowObj = $this->AdvancedVarianceLineItemWorkflowRepositoryObj->update($input, $advanced_variance_line_item_id);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $advanced_variance_id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        /**
         * No Notification per Peter. In fact no 'Un' anything Notification, unflag, unapprove ......
         */
        if($AdvancedVarianceLineItemWorkflowObj->explanation)
        {
            $this->post_job_to_queue(
                [
                'advanced_variance_id'           => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->id,
                'advanced_variance_line_item_id' => $AdvancedVarianceLineItemWorkflowObj->id,
                'as_of_month'                    => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_month,
                'as_of_year'                     => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->as_of_year,
                'recipient_id_arr'               => $AdvancedVarianceLineItemWorkflowObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                    'explainer_display_name'         => $this->CurrentLoggedInUserObj->getDisplayName(),
                ],
                App\Waypoint\Jobs\AdvancedVarianceLineItemExplanationNotificationJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemExplanationNotification', false)
            );
        }

        $purge_list_arr = App\Waypoint\Events\EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj);
        event(
            new PreCalcPropertiesEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $AdvancedVarianceObj->property->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($AdvancedVarianceObj),
                    'event_trigger_object_class_id'    => $AdvancedVarianceObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    => EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj),
                    'launch_job_property_id_arr'       => $purge_list_arr['properties'],
                    'launch_job_property_group_id_arr' => $purge_list_arr['property_groups'],

                ]
            )
        );
        return $this->sendResponse($AdvancedVarianceLineItemWorkflowObj->toArray(), 'AdvancedVarianceLineItemWorkflow updated explanation');
    }

    /**
     * @param integer $client_id
     * @param integer $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showComments($client_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj =
            $this->AdvancedVarianceRepositoryObj
                ->with('comments')
                ->find($advanced_variance_id);

        $CommentsObjArr = $AdvancedVarianceObj->getComments();

        return $this->sendResponse($CommentsObjArr->toArray(), 'Comment(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function advancedVariancesPerClientTriggerJobs($client_id)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new ModelNotFoundException('No such property');
        }

        /** @var Property $PropertyObj */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            /** @var AdvancedVariance $AdvancedVarianceObj */
            foreach ($PropertyObj->advancedVariances as $AdvancedVarianceObj)
            {
                $this->post_job_to_queue(
                    [
                        'advanced_variance_id' => $AdvancedVarianceObj->id,
                        'as_of_month'          => $AdvancedVarianceObj->as_of_month,
                        'as_of_year'           => $AdvancedVarianceObj->as_of_year,
                    ],
                    App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                    config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
                );
            }
        }
        return $this->sendResponse([], 'AdvancedVariance Job(s) triggered');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function advancedVariancesPerPropertyTriggerJobs($client_id, $property_id)
    {
        /** @var Property $PropertyObj */
        if ( ! $PropertyObj = App::make(PropertyRepository::class)->find($property_id))
        {
            throw new ModelNotFoundException('No such property');
        }

        foreach ($PropertyObj->advancedVarianceSummaries as $AdvancedVarianceObj)
        {
            $this->post_job_to_queue(
                [
                    'advanced_variance_id' => $AdvancedVarianceObj->id,
                    'as_of_month'          => $AdvancedVarianceObj->as_of_month,
                    'as_of_year'           => $AdvancedVarianceObj->as_of_year,
                ],
                App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
            );
        }
        return $this->sendResponse([], 'AdvancedVariance Job(s) triggered');
    }

    /**
     * Display a listing of the AdvancedVarianceLineItem.
     * GET|HEAD /advancedVarianceLineItems
     *
     * @param \Illuminate\Http\Request $AdvancedVarianceLineItemRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function advanced_variance_line_items_by_property_id_native_account_id($client_id, $property_id_arr, $native_account_id_arr, Request $AdvancedVarianceLineItemRequestObj)
    {
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new RequestCriteria($AdvancedVarianceLineItemRequestObj));
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($AdvancedVarianceLineItemRequestObj));

        $input = $AdvancedVarianceLineItemRequestObj->all();
        if ( ! isset($input['startDate']))
        {
            $input['startDate'] = ApiController::DEFAULT_START_DATE;
        }
        $input['startDate'] = explode('-', $input['startDate']);
        if (count($input['startDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }
        if ( ! isset($input['endDate']))
        {
            $input['endDate'] = ApiController::DEFAULT_END_DATE;
        }
        $input['endDate'] = explode('-', $input['endDate']);
        if (count($input['endDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }

        $StartDateObj = Carbon::create($input['startDate'][0], $input['startDate'][2], $input['startDate'][1]);
        $EndDateObj   = Carbon::create($input['endDate'][0], $input['endDate'][2], $input['endDate'][1]);

        $property_id_arr       = explode(',', $property_id_arr);
        $native_account_id_arr = explode(',', $native_account_id_arr);

        $AdvancedVarianceLineItemObjArr =
            $this->AdvancedVarianceLineItemDetailRepositoryObj
                ->with('advancedVariance')
                ->with('nativeAccount')
                ->with('flaggerUser')
                ->findWhereIn('native_account_id', $native_account_id_arr)
                ->filter(
                    function ($AdvancedVarianceLineItemObj) use ($property_id_arr, $StartDateObj, $EndDateObj)
                    {
                        $AdvancedVarianceDateObj = Carbon::create(
                            $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                            $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                            31
                        );
                        return
                            in_array($AdvancedVarianceLineItemObj->advancedVariance->property_id, $property_id_arr) &&
                            $AdvancedVarianceDateObj->greaterThanOrEqualTo($StartDateObj) &&
                            $AdvancedVarianceDateObj->lessThanOrEqualTo($EndDateObj);
                    }
                );

        return $this->sendResponse($AdvancedVarianceLineItemObjArr, 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Display a listing of the AdvancedVarianceLineItem.
     * GET|HEAD /advancedVarianceLineItems
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function advanced_variance_line_items_by_property_id_report_template_account_group_id(
        $client_id,
        $property_id_arr,
        $rtag_id_arr,
        Request $AdvancedVarianceLineItemRequestObj
    ) {
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new RequestCriteria($AdvancedVarianceLineItemRequestObj));
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($AdvancedVarianceLineItemRequestObj));

        $input = $AdvancedVarianceLineItemRequestObj->all();
        if ( ! isset($input['startDate']))
        {
            $input['startDate'] = '1900-01-01';
        }
        $input['startDate'] = explode('-', $input['startDate']);
        if (count($input['startDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }
        if ( ! isset($input['endDate']))
        {
            $input['endDate'] = '2099-12-31';
        }
        $input['endDate'] = explode('-', $input['endDate']);
        if (count($input['endDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }

        $StartDateObj = Carbon::create($input['startDate'][0], $input['startDate'][2], $input['startDate'][1]);
        $EndDateObj   = Carbon::create($input['endDate'][0], $input['endDate'][2], $input['endDate'][1]);

        $property_id_arr = explode(',', $property_id_arr);
        $rtag_id_arr     = explode(',', $rtag_id_arr);

        $AdvancedVarianceLineItemObjArr =
            $this->AdvancedVarianceLineItemDetailRepositoryObj
                ->with('advancedVariance')
                ->with('reportTemplateAccountGroup.nativeAccountType')
                ->with('flaggerUser')
                ->findWhereIn('report_template_account_group_id', $rtag_id_arr)
                ->filter(
                    function ($AdvancedVarianceLineItemObj) use ($property_id_arr, $StartDateObj, $EndDateObj)
                    {
                        $AdvancedVarianceDateObj =
                            Carbon::create(
                                $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                                $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                                31
                            );
                        return
                            in_array($AdvancedVarianceLineItemObj->advancedVariance->property_id, $property_id_arr) &&
                            $AdvancedVarianceDateObj->greaterThanOrEqualTo($StartDateObj) &&
                            $AdvancedVarianceDateObj->lessThanOrEqualTo($EndDateObj);
                    }
                );

        return $this->sendResponse($AdvancedVarianceLineItemObjArr, 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Display a listing of the AdvancedVarianceLineItem.
     * GET|HEAD /advancedVarianceLineItems
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function advanced_variance_line_items_by_property_id_calculated_field_id(
        $client_id,
        $property_id_arr,
        $calculated_field_id_arr,
        Request $AdvancedVarianceLineItemRequestObj
    ) {
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new RequestCriteria($AdvancedVarianceLineItemRequestObj));
        $this->AdvancedVarianceLineItemDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($AdvancedVarianceLineItemRequestObj));

        $input = $AdvancedVarianceLineItemRequestObj->all();
        if ( ! isset($input['startDate']))
        {
            $input['startDate'] = '1900-01-01';
        }
        $input['startDate'] = explode('-', $input['startDate']);
        if (count($input['startDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }
        if ( ! isset($input['endDate']))
        {
            $input['endDate'] = '2099-12-31';
        }
        $input['endDate'] = explode('-', $input['endDate']);
        if (count($input['endDate']) !== 3)
        {
            throw new GeneralException('Invalid date filter');
        }

        $StartDateObj = Carbon::create($input['startDate'][0], $input['startDate'][2], $input['startDate'][1]);
        $EndDateObj   = Carbon::create($input['endDate'][0], $input['endDate'][2], $input['endDate'][1]);

        $property_id_arr         = explode(',', $property_id_arr);
        $calculated_field_id_arr = explode(',', $calculated_field_id_arr);

        $AdvancedVarianceLineItemObjArr =
            $this->AdvancedVarianceLineItemDetailRepositoryObj
                ->with('advancedVariance')
                ->with('flaggerUser')
                ->findWhereIn('calculated_field_id', $calculated_field_id_arr)
                ->filter(
                    function ($AdvancedVarianceLineItemObj) use ($property_id_arr, $StartDateObj, $EndDateObj)
                    {
                        $AdvancedVarianceDateObj =
                            Carbon::create(
                                $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                                $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                                31
                            );
                        return
                            in_array($AdvancedVarianceLineItemObj->advancedVariance->property_id, $property_id_arr) &&
                            $AdvancedVarianceDateObj->greaterThanOrEqualTo($StartDateObj) &&
                            $AdvancedVarianceDateObj->lessThanOrEqualTo($EndDateObj);
                    }
                );

        return $this->sendResponse($AdvancedVarianceLineItemObjArr, 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $access_list_user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAudits($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj->getAuditArr(), 'AdvancedVariance audits retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @param $advanced_variances_line_item_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showAdvancedVarianceLineItemAudits($client_id, $property_id, $advanced_variance_id, $advanced_variances_line_item_id)
    {
        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($advanced_variances_line_item_id);
        if (empty($AdvancedVarianceLineItemObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceLineItem not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceLineItemObj->getAuditArr(), 'AdvancedVarianceLineItem audits retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @param $advanced_variances_approval_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showAdvancedVarianceApprovalAudits($client_id, $property_id, $advanced_variance_id, $advanced_variances_approval_id)
    {
        /** @var AdvancedVarianceApproval $AdvancedVarianceApprovalObj */
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->find($advanced_variances_approval_id);
        if (empty($AdvancedVarianceApprovalObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceApproval not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceApprovalObj->getAuditArr(), 'AdvancedVarianceApproval audits retrieved successfully');
    }
}
