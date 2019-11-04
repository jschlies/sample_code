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

use App\Waypoint\Http\Requests\Generated\Api\CreateLeaseScheduleRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateLeaseScheduleRequest;
use App\Waypoint\Models\LeaseSchedule;
use App\Waypoint\Repositories\LeaseScheduleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class LeaseScheduleController
 */
final class LeaseScheduleController extends BaseApiController
{
    /** @var  LeaseScheduleRepository */
    private $LeaseScheduleRepositoryObj;

    public function __construct(LeaseScheduleRepository $LeaseScheduleRepositoryObj)
    {
        $this->LeaseScheduleRepositoryObj = $LeaseScheduleRepositoryObj;
        parent::__construct($LeaseScheduleRepositoryObj);
    }

    /**
     * Display a listing of the LeaseSchedule.
     * GET|HEAD /leaseSchedules
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->LeaseScheduleRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->LeaseScheduleRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $LeaseScheduleObjArr = $this->LeaseScheduleRepositoryObj->all();

        return $this->sendResponse($LeaseScheduleObjArr, 'LeaseSchedule(s) retrieved successfully');
    }

    /**
     * Store a newly created LeaseSchedule in storage.
     *
     * @param CreateLeaseScheduleRequest $LeaseScheduleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateLeaseScheduleRequest $LeaseScheduleRequestObj)
    {
        $input = $LeaseScheduleRequestObj->all();

        $LeaseScheduleObj = $this->LeaseScheduleRepositoryObj->create($input);

        return $this->sendResponse($LeaseScheduleObj, 'LeaseSchedule saved successfully');
    }

    /**
     * Display the specified LeaseSchedule.
     * GET|HEAD /leaseSchedules/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var LeaseSchedule $leaseSchedule */
        $LeaseScheduleObj = $this->LeaseScheduleRepositoryObj->findWithoutFail($id);
        if (empty($LeaseScheduleObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseSchedule not found'), 404);
        }

        return $this->sendResponse($LeaseScheduleObj, 'LeaseSchedule retrieved successfully');
    }

    /**
     * Update the specified LeaseSchedule in storage.
     * PUT/PATCH /leaseSchedules/{id}
     *
     * @param integer $id
     * @param UpdateLeaseScheduleRequest $LeaseScheduleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateLeaseScheduleRequest $LeaseScheduleRequestObj)
    {
        $input = $LeaseScheduleRequestObj->all();
        /** @var LeaseSchedule $LeaseScheduleObj */
        $LeaseScheduleObj = $this->LeaseScheduleRepositoryObj->findWithoutFail($id);
        if (empty($LeaseScheduleObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseSchedule not found'), 404);
        }
        $LeaseScheduleObj = $this->LeaseScheduleRepositoryObj->update($input, $id);

        return $this->sendResponse($LeaseScheduleObj, 'LeaseSchedule updated successfully');
    }

    /**
     * Remove the specified LeaseSchedule from storage.
     * DELETE /leaseSchedules/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var LeaseSchedule $LeaseScheduleObj */
        $LeaseScheduleObj = $this->LeaseScheduleRepositoryObj->findWithoutFail($id);
        if (empty($LeaseScheduleObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseSchedule not found'), 404);
        }

        $this->LeaseScheduleRepositoryObj->delete($id);

        return $this->sendResponse($id, 'LeaseSchedule deleted successfully');
    }
}
