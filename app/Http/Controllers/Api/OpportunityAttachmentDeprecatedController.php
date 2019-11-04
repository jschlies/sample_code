<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateOpportunityRequest;
use App\Waypoint\Jobs\OpportunityAttachedNotificationJob;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notifiable;
use Response;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Class OpportunityAttachmentDeprecatedController
 * @codeCoverageIgnore
 */
class OpportunityAttachmentDeprecatedController extends BaseApiController
{
    use Notifiable;

    /** @var  OpportunityRepository */
    private $OpportunityRepositoryObj;

    public function __construct(OpportunityRepository $OpportunityRepositoryObj)
    {
        $this->OpportunityRepositoryObj = $OpportunityRepositoryObj;
        parent::__construct($this->OpportunityRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard route - fix me
     */
    public function showOpportunityAttachments($client_id, $property_id, $opportunity_id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj->getAttachments(), 'Attachment(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $opportunity_id
     * @param CreateOpportunityRequest $OpportunityRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     *
     * @todo non standard route - fix me
     */
    public function storeOpportunityAttachments($client_id, $property_id, $opportunity_id, CreateOpportunityRequest $OpportunityRequestObj)
    {
        /** @var Opportunity $OpportunityObj */
        if ( ! $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id))
        {
            throw new ModelNotFoundException('No such Opportunity');
        }

        $input = $OpportunityRequestObj->all();
        if (count($OpportunityRequestObj->allFiles()) !== 1)
        {
            throw new GeneralException('Please upload one attachment at a time');
        }

        try
        {
            $input['attachable_type'] = Opportunity::class;

            /** @var SymfonyUploadedFile $FileObj */
            foreach ($OpportunityRequestObj->allFiles() as $FileObj)
            {
                $OpportunityObj->attach(
                    $FileObj->getRealPath(),
                    [
                        'disk'               => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                        'title'              => $FileObj->getFilename(),
                        'description'        => $FileObj->getFilename(),
                        'key'                => $FileObj->getClientOriginalName(),
                        'created_by_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ]
                );

                $this->post_job_to_queue(
                    [
                        'client_id'        => $OpportunityObj->client_id,
                        'opportunity_id'   => $OpportunityObj->id,
                        'recipient_id_arr' => [
                            $this->getCurrentLoggedInUserObj()->id,
                            $OpportunityObj->assignedToUser->id,
                            $OpportunityObj->createdByUser->id,
                        ],
                        'attachment_name'  => $FileObj->getFilename(),
                        'attacher_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ],
                    OpportunityAttachedNotificationJob::class,
                    config('queue.queue_lanes.OpportunityAttachedNotification', false)
                );
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($OpportunityObj->attachment($FileObj->getClientOriginalName()), 'Attachment saved successfully');
    }

}
