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

use App\Waypoint\Http\Requests\Generated\Api\CreateApiLogRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateApiLogRequest;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Repositories\ApiLogRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ApiLogController
 */
final class ApiLogController extends BaseApiController
{
    /** @var  ApiLogRepository */
    private $ApiLogRepositoryObj;

    public function __construct(ApiLogRepository $ApiLogRepositoryObj)
    {
        $this->ApiLogRepositoryObj = $ApiLogRepositoryObj;
        parent::__construct($ApiLogRepositoryObj);
    }

    /**
     * Display a listing of the ApiLog.
     * GET|HEAD /apiLogs
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ApiLogRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ApiLogRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ApiLogObjArr = $this->ApiLogRepositoryObj->all();

        return $this->sendResponse($ApiLogObjArr, 'ApiLog(s) retrieved successfully');
    }

    /**
     * Store a newly created ApiLog in storage.
     *
     * @param CreateApiLogRequest $ApiLogRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateApiLogRequest $ApiLogRequestObj)
    {
        $input = $ApiLogRequestObj->all();

        $ApiLogObj = $this->ApiLogRepositoryObj->create($input);

        return $this->sendResponse($ApiLogObj, 'ApiLog saved successfully');
    }

    /**
     * Display the specified ApiLog.
     * GET|HEAD /apiLogs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ApiLog $apiLog */
        $ApiLogObj = $this->ApiLogRepositoryObj->findWithoutFail($id);
        if (empty($ApiLogObj))
        {
            return Response::json(ResponseUtil::makeError('ApiLog not found'), 404);
        }

        return $this->sendResponse($ApiLogObj, 'ApiLog retrieved successfully');
    }

    /**
     * Update the specified ApiLog in storage.
     * PUT/PATCH /apiLogs/{id}
     *
     * @param integer $id
     * @param UpdateApiLogRequest $ApiLogRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateApiLogRequest $ApiLogRequestObj)
    {
        $input = $ApiLogRequestObj->all();
        /** @var ApiLog $ApiLogObj */
        $ApiLogObj = $this->ApiLogRepositoryObj->findWithoutFail($id);
        if (empty($ApiLogObj))
        {
            return Response::json(ResponseUtil::makeError('ApiLog not found'), 404);
        }
        $ApiLogObj = $this->ApiLogRepositoryObj->update($input, $id);

        return $this->sendResponse($ApiLogObj, 'ApiLog updated successfully');
    }

    /**
     * Remove the specified ApiLog from storage.
     * DELETE /apiLogs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ApiLog $ApiLogObj */
        $ApiLogObj = $this->ApiLogRepositoryObj->findWithoutFail($id);
        if (empty($ApiLogObj))
        {
            return Response::json(ResponseUtil::makeError('ApiLog not found'), 404);
        }

        $this->ApiLogRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ApiLog deleted successfully');
    }
}
