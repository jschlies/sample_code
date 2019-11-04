<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Api\CreateCommentRequest;
use App\Waypoint\Http\Requests\Generated\Api\CreateOpportunityRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateOpportunityRequest;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\OpportunityRepository;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class OpportunityController
 */
class OpportunityController extends BaseApiController
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
     * Display the specified Opportunity.
     * GET|HEAD /Opportunitys/{id}
     *
     * @param integer $client_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $opportunity_id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj, 'Opportunity retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     */
    public function indexForClient($client_id, $opportunity_id_arr = null)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientObj = $ClientRepositoryObj->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        if ( ! $ClientObj->canUseOpportunities())
        {
            throw new GeneralException('Cannot use opportunities');
        }

        $OpportunityObjArr = $ClientObj->getOpportunities();
        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($opportunity_id_arr)
        {
            $OpportunityObjArr = $OpportunityObjArr->whereIn('id', explode(',', $opportunity_id_arr));
        }

        return $this->sendResponse($OpportunityObjArr, 'Opportunity(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     */
    public function indexForProperty($client_id, $property_id, $opportunity_id_arr = null)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientObj = $ClientRepositoryObj->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }

        if ( ! $ClientObj->canUseOpportunities())
        {
            throw new GeneralException('Cannot use opportunities');
        }

        if ( ! $PropertyObj = Property::find($property_id))
        {
            return Response::json(ResponseUtil::makeError('No such property'), 400);
        }

        $OpportunityObjArr = $PropertyObj->opportunities;
        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($opportunity_id_arr)
        {
            $OpportunityObjArr = $OpportunityObjArr->whereIn('id', explode(',', $opportunity_id_arr));
        }
        return $this->sendResponse($OpportunityObjArr, 'Opportunity(s) retrieved successfully');
    }

    /**
     * Store a newly created Opportunity in storage.
     *
     * @param CreateOpportunityRequest $OpportunityRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreateOpportunityRequest $OpportunityRequestObj)
    {
        if ( ! $this->getCurrentLoggedInUserObj()->client->canUseOpportunities())
        {
            throw new GeneralException('Cannot use opportunities');
        }
        $input          = $OpportunityRequestObj->all();
        $OpportunityObj = $this->OpportunityRepositoryObj->create($input);

        if ($this->getCurrentLoggedInUserObj()->client->canSendNotification())
        {
            $ExpectedReciepiantsUserObjArr   = $OpportunityObj->getExpectedRecipiants();
            $ExpectedReciepiantsUserObjArr[] = $this->getCurrentLoggedInUserObj();

            $this->post_job_to_queue(
                [
                    'client_id'        => $OpportunityObj->client_id,
                    'opportunity_id'   => $OpportunityObj->id,
                    'recipient_id_arr' => $ExpectedReciepiantsUserObjArr->pluck('id')->toArray(),
                ],
                App\Waypoint\Jobs\OpportunityOpenedNotificationJob::class,
                config('queue.queue_lanes.OpportunityOpenedNotification', false)
            );
        }

        return $this->sendResponse($OpportunityObj, 'Opportunity saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $opportunity_id
     * @param UpdateOpportunityRequest $OpportunityRequestObj
     * @return JsonResponse|null
     * @throws ValidatorException
     */
    public function update($client_id, $opportunity_id, UpdateOpportunityRequest $OpportunityRequestObj)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientRepositoryObj->find($client_id)->canUseOpportunities())
        {
            throw new GeneralException('Cannot use opportunities');
        }

        $input = $OpportunityRequestObj->all();
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->findWithoutFail($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }
        $prev_opportunity_status = $OpportunityObj->opportunity_status;
        $OpportunityObj          = $this->OpportunityRepositoryObj->update($input, $opportunity_id);

        if (isset($input['opportunity_status']) && ($input['opportunity_status'] == 'closed' || $input['opportunity_status'] == 'open'))
        {
            $OpportunityObj->dirtyDataAlternative = $input;

            /**
             * we are not currently sending these out
             */
            if ($prev_opportunity_status != $OpportunityObj->opportunity_status)
            {
                $ExpectedReciepiantsUserObjArr   = $OpportunityObj->getExpectedRecipiants();
                $ExpectedReciepiantsUserObjArr[] = $this->getCurrentLoggedInUserObj();

                if ($OpportunityObj->opportunity_status == Opportunity::OPPORTUNITY_STATUS_OPEN)
                {
                    $this->post_job_to_queue(
                        [
                            'opportunity_id'   => $OpportunityObj->id,
                            'recipient_id_arr' => $ExpectedReciepiantsUserObjArr->pluck('id')->toArray(),
                        ],
                        App\Waypoint\Jobs\OpportunityOpenedNotificationJob::class,
                        config('queue.queue_lanes.OpportunityOpenedNotification', false)
                    );
                }
                elseif ($OpportunityObj->opportunity_status == Opportunity::OPPORTUNITY_STATUS_CLOSED)
                {
                    $this->post_job_to_queue(
                        [
                            'opportunity_id'   => $OpportunityObj->id,
                            'recipient_id_arr' => $ExpectedReciepiantsUserObjArr->pluck('id')->toArray(),
                        ],
                        App\Waypoint\Jobs\OpportunityUpdatedNotificationJob::class,
                        config('queue.queue_lanes.OpportunityUpdatedNotification', false)
                    );
                }
                else
                {
                    throw new GeneralException('invalid opportunity_status');
                }
            }
        }
        return $this->sendResponse($OpportunityObj, 'Opportunity updated successfully');
    }

    /**
     * Remove the specified Opportunity from storage.
     * DELETE /opportunities/{id}
     *
     * @param integer $client_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     */
    public function destroy($client_id, $opportunity_id)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientRepositoryObj->find($client_id)->canUseOpportunities())
        {
            throw new GeneralException('Cannot use opportunities');
        }

        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->findWithoutFail($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }
        $OpportunityObj->delete();

        return $this->sendResponse($opportunity_id, 'Opportunity deleted successfully');
    }

    /**
     * Display the specified Opportunity.
     * GET|HEAD /Opportunitys/{id}
     *
     * @param integer $client_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAudits($client_id, $opportunity_id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj->getAuditArr(), 'Opportunity audits retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function showRelatedUserTypes($client_id)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientObj = $ClientRepositoryObj->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        return $this->sendResponse(
            $ClientObj->getRelatedUserTypes(Opportunity::class), 'Related User Type(s) retrieved successfully'
        );
    }

    /**
     * @param integer $client_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showOpportunityComments($client_id, $opportunity_id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($opportunity_id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj->getComments(), 'Comment(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $opportunity_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard route - fix me
     */
    public function showAttachments($client_id, $opportunity_id)
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
     * @param $opportunity_id
     * @param CreateOpportunityRequest $OpportunityRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function storeAttachments($client_id, $opportunity_id, CreateOpportunityRequest $OpportunityRequestObj)
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

            /** @var UploadedFile $FileObj */
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

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $opportinity_id
     * @param CreateCommentRequest $CommentCreateRequestObj
     * @return JsonResponse|null
     */
    public function storeOpportunityComments($client_id, $property_id, $opportinity_id, CreateCommentRequest $CommentCreateRequestObj)
    {
        $OpportunityRepositoryObj = App::make(OpportunityRepository::class);
        if ( ! $OpportunityObj = $OpportunityRepositoryObj->find($opportinity_id))
        {
            throw new ModelNotFoundException('No such Opportunity');
        }
        $input                     = $CommentCreateRequestObj->all();
        $input['commentable_id']   = $opportinity_id;
        $input['commentable_type'] = Opportunity::class;
        if ( ! isset($input['comment']) || empty($input['comment']) || ! $input['comment'])
        {
            throw new GeneralException('invalid comment');
        }

        try
        {
            $this->getCurrentLoggedInUserObj()->comment($OpportunityObj, $input['comment']);

            $OpportunityObj->add_reviewer($this->getCurrentLoggedInUserObj()->id);

            $this->post_job_to_queue(
                [
                    'opportunity_id'        => $OpportunityObj->id,
                    'recipient_id_arr'      => [],
                    'comment'               => $input['comment'],
                    'commenter_id'          => $this->getCurrentLoggedInUserObj()->id,
                    'mentioned_user_id_arr' => isset($input['mentions']) ? $input['mentions'] : [],
                ],
                App\Waypoint\Jobs\OpportunityCommentedNotificationJob::class,
                config('queue.queue_lanes.OpportunityCommentedNotification', false)
            );
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($OpportunityObj->comments, 'Comment saved successfully');
    }
}
