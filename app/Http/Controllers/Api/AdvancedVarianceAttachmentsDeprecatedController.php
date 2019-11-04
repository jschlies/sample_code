<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Class AdvancedVarianceDetailController
 * @codeCoverageIgnore
 */
class AdvancedVarianceAttachmentsDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceLineItemRepositoryObj;
    /** @var  AdvancedVarianceApprovalRepository */
    private $AdvancedVarianceApprovalRepositoryObj;
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceRepositoryObj         = $AdvancedVarianceRepositoryObj;
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);
        $this->AdvancedVarianceApprovalRepositoryObj = App::make(AdvancedVarianceApprovalRepository::class);
        $this->RelatedUserRepositoryObj              = App::make(RelatedUserRepository::class);
        parent::__construct($AdvancedVarianceRepositoryObj);
    }

    /**
     *
     * @param integer $client_id
     * @param integer $advanced_variance_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAdvancedVarianceAttachments($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj->getAttachments(), 'Attachment(s) retrieved successfully');
    }

    /**
     * Store a newly created Attachment in storage.
     *
     * @param \Illuminate\Http\Request $AttachmentRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function storeAdvancedVarianceAttachments($client_id, $property_id, $advanced_variance_id, Request $AttachmentRequestObj)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($advanced_variance_id);
        if ($AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance is locked');
        }

        $input = $AttachmentRequestObj->all();
        if (count($AttachmentRequestObj->allFiles()) !== 1)
        {
            throw new GeneralException('Please upload one attachment at a time');
        }

        try
        {
            $input['attachable_type'] = AdvancedVariance::class;

            /** @var SymfonyUploadedFile $FileObj */
            foreach ($AttachmentRequestObj->allFiles() as $FileObj)
            {
                $AdvancedVarianceObj->attach(
                    $FileObj->getRealPath(),
                    [
                        'disk'               => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                        'title'              => $FileObj->getFilename(),
                        'description'        => $FileObj->getFilename(),
                        'key'                => $FileObj->getClientOriginalName(),
                        'created_by_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ]
                );
            }

            $this->post_job_to_queue(
                [
                    'advanced_variance_id' => $AdvancedVarianceObj->id,
                    'as_of_month'           => $AdvancedVarianceObj->as_of_month,
                    'as_of_year'          => $AdvancedVarianceObj->as_of_year,
                    'recipient_id_arr'     => $AdvancedVarianceObj->getExpectedRecipiants()->pluck('id')->toArray(),
                ],
                App\Waypoint\Jobs\AdvancedVarianceAttachmentNotification::class,
                config('queue.queue_lanes.AdvancedVarianceAttachmentNotification', false)
            );
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($AdvancedVarianceObj->attachment($FileObj->getClientOriginalName()), 'Attachment saved successfully');
    }

    /**
     *
     * @param integer $client_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAdvancedVarianceLineItemAttachments($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id)
    {
        /** @var AdvancedVarianceLineItem $AdvancedLineItemVarianceObj */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($advanced_variance_line_item_id);
        if (empty($AdvancedVarianceLineItemObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceLineItem not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceLineItemObj->getAttachments(), 'Attachment(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @param integer $advanced_variance_line_item_id
     * @param Request $AttachmentRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function storeAdvancedVarianceLineItemAttachments($client_id, $property_id, $advanced_variance_id, $advanced_variance_line_item_id, Request $AttachmentRequestObj)
    {
        if (
            $this->getCurrentLoggedInUserObj()->hasRole(App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->getCurrentLoggedInUserObj()->is_hidden
        )
        {
            throw new GeneralException('WAYPOINT_ASSOCIATE_ROLE or hidden users not allowed');
        }

        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($advanced_variance_line_item_id);
        if ($AdvancedVarianceLineItemObj->advancedVariance->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
        {
            throw new GeneralException('AdvancedVariance is locked');
        }

        $input = $AttachmentRequestObj->all();
        if (count($AttachmentRequestObj->allFiles()) !== 1)
        {
            throw new GeneralException('Please upload one attachment at a time');
        }

        try
        {
            $input['attachable_type'] = AdvancedVarianceLineItem::class;

            /** @var SymfonyUploadedFile $FileObj */
            foreach ($AttachmentRequestObj->allFiles() as $FileObj)
            {
                $AdvancedVarianceLineItemObj->attach(
                    $FileObj->getRealPath(),
                    [
                        'disk'               => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                        'title'              => $FileObj->getFilename(),
                        'description'        => $FileObj->getFilename(),
                        'key'                => $FileObj->getClientOriginalName(),
                        'created_by_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ]
                );
            }

            $this->post_job_to_queue(
                [
                    'advanced_variance_id'           => $AdvancedVarianceLineItemObj->advancedVariance->id,
                    'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                    'as_of_month'                    => $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                    'as_of_year'                     => $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                    'recipient_id_arr'               => $AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                ],
                App\Waypoint\Jobs\AdvancedVarianceLineItemAttachmentNotificationJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemAttachmentNotification', false)
            );
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($AdvancedVarianceLineItemObj->attachment($FileObj->getClientOriginalName()), 'Attachment saved successfully');
    }
}
