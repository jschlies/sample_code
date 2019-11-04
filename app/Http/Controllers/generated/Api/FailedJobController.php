<?php

namespace App\Waypoint\Http\Controllers\Api\Generated;

use App\Waypoint\Exceptions\GeneralException;
use Exception;
use Illuminate\Http\JsonResponse;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Http\Requests\Generated\Api\CreateFailedJobRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateFailedJobRequest;
use App\Waypoint\Models\FailedJob;
use App\Waypoint\Repositories\FailedJobRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class FailedJobController
 */
final class FailedJobController extends BaseApiController
{
    /** @var  FailedJobRepository */
    private $FailedJobRepositoryObj;

    public function __construct(FailedJobRepository $FailedJobRepositoryObj)
    {
        $this->FailedJobRepositoryObj = $FailedJobRepositoryObj;
        parent::__construct($FailedJobRepositoryObj);
    }

    /**
     * Display a listing of the FailedJob.
     * GET|HEAD /failedJobs
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->FailedJobRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->FailedJobRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $FailedJobObjArr = $this->FailedJobRepositoryObj->all();

        return $this->sendResponse($FailedJobObjArr, 'FailedJob(s) retrieved successfully');
    }

    /**
     * Store a newly created FailedJob in storage.
     *
     * @param CreateFailedJobRequest $FailedJobRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateFailedJobRequest $FailedJobRequestObj)
    {
        $input = $FailedJobRequestObj->all();

        $FailedJobObj = $this->FailedJobRepositoryObj->create($input);

        return $this->sendResponse($FailedJobObj, 'FailedJob saved successfully');
    }

    /**
     * Display the specified FailedJob.
     * GET|HEAD /failedJobs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var FailedJob $failedJob */
        $FailedJobObj = $this->FailedJobRepositoryObj->findWithoutFail($id);
        if (empty($FailedJobObj))
        {
            return Response::json(ResponseUtil::makeError('FailedJob not found'), 404);
        }

        return $this->sendResponse($FailedJobObj, 'FailedJob retrieved successfully');
    }

    /**
     * Update the specified FailedJob in storage.
     * PUT/PATCH /failedJobs/{id}
     *
     * @param integer $id
     * @param UpdateFailedJobRequest $FailedJobRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateFailedJobRequest $FailedJobRequestObj)
    {
        $input = $FailedJobRequestObj->all();
        /** @var FailedJob $FailedJobObj */
        $FailedJobObj = $this->FailedJobRepositoryObj->findWithoutFail($id);
        if (empty($FailedJobObj))
        {
            return Response::json(ResponseUtil::makeError('FailedJob not found'), 404);
        }
        $FailedJobObj = $this->FailedJobRepositoryObj->update($input, $id);

        return $this->sendResponse($FailedJobObj, 'FailedJob updated successfully');
    }

    /**
     * Remove the specified FailedJob from storage.
     * DELETE /failedJobs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var FailedJob $FailedJobObj */
        $FailedJobObj = $this->FailedJobRepositoryObj->findWithoutFail($id);
        if (empty($FailedJobObj))
        {
            return Response::json(ResponseUtil::makeError('FailedJob not found'), 404);
        }

        $this->FailedJobRepositoryObj->delete($id);

        return $this->sendResponse($id, 'FailedJob deleted successfully');
    }
}
