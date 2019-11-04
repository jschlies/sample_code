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

use App\Waypoint\Http\Requests\Generated\Api\CreateNotificationLogRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNotificationLogRequest;
use App\Waypoint\Models\NotificationLog;
use App\Waypoint\Repositories\NotificationLogRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NotificationLogController
 */
final class NotificationLogController extends BaseApiController
{
    /** @var  NotificationLogRepository */
    private $NotificationLogRepositoryObj;

    public function __construct(NotificationLogRepository $NotificationLogRepositoryObj)
    {
        $this->NotificationLogRepositoryObj = $NotificationLogRepositoryObj;
        parent::__construct($NotificationLogRepositoryObj);
    }

    /**
     * Display a listing of the NotificationLog.
     * GET|HEAD /notificationLogs
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NotificationLogRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NotificationLogRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NotificationLogObjArr = $this->NotificationLogRepositoryObj->all();

        return $this->sendResponse($NotificationLogObjArr, 'NotificationLog(s) retrieved successfully');
    }

    /**
     * Store a newly created NotificationLog in storage.
     *
     * @param CreateNotificationLogRequest $NotificationLogRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNotificationLogRequest $NotificationLogRequestObj)
    {
        $input = $NotificationLogRequestObj->all();

        $NotificationLogObj = $this->NotificationLogRepositoryObj->create($input);

        return $this->sendResponse($NotificationLogObj, 'NotificationLog saved successfully');
    }

    /**
     * Display the specified NotificationLog.
     * GET|HEAD /notificationLogs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NotificationLog $notificationLog */
        $NotificationLogObj = $this->NotificationLogRepositoryObj->findWithoutFail($id);
        if (empty($NotificationLogObj))
        {
            return Response::json(ResponseUtil::makeError('NotificationLog not found'), 404);
        }

        return $this->sendResponse($NotificationLogObj, 'NotificationLog retrieved successfully');
    }

    /**
     * Update the specified NotificationLog in storage.
     * PUT/PATCH /notificationLogs/{id}
     *
     * @param integer $id
     * @param UpdateNotificationLogRequest $NotificationLogRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateNotificationLogRequest $NotificationLogRequestObj)
    {
        $input = $NotificationLogRequestObj->all();
        /** @var NotificationLog $NotificationLogObj */
        $NotificationLogObj = $this->NotificationLogRepositoryObj->findWithoutFail($id);
        if (empty($NotificationLogObj))
        {
            return Response::json(ResponseUtil::makeError('NotificationLog not found'), 404);
        }
        $NotificationLogObj = $this->NotificationLogRepositoryObj->update($input, $id);

        return $this->sendResponse($NotificationLogObj, 'NotificationLog updated successfully');
    }

    /**
     * Remove the specified NotificationLog from storage.
     * DELETE /notificationLogs/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NotificationLog $NotificationLogObj */
        $NotificationLogObj = $this->NotificationLogRepositoryObj->findWithoutFail($id);
        if (empty($NotificationLogObj))
        {
            return Response::json(ResponseUtil::makeError('NotificationLog not found'), 404);
        }

        $this->NotificationLogRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NotificationLog deleted successfully');
    }
}
