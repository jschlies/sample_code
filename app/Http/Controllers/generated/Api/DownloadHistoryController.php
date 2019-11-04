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

use App\Waypoint\Http\Requests\Generated\Api\CreateDownloadHistoryRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateDownloadHistoryRequest;
use App\Waypoint\Models\DownloadHistory;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class DownloadHistoryController
 */
final class DownloadHistoryController extends BaseApiController
{
    /** @var  DownloadHistoryRepository */
    private $DownloadHistoryRepositoryObj;

    public function __construct(DownloadHistoryRepository $DownloadHistoryRepositoryObj)
    {
        $this->DownloadHistoryRepositoryObj = $DownloadHistoryRepositoryObj;
        parent::__construct($DownloadHistoryRepositoryObj);
    }

    /**
     * Display a listing of the DownloadHistory.
     * GET|HEAD /downloadHistories
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->DownloadHistoryRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->DownloadHistoryRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $DownloadHistoryObjArr = $this->DownloadHistoryRepositoryObj->all();

        return $this->sendResponse($DownloadHistoryObjArr, 'DownloadHistory(s) retrieved successfully');
    }

    /**
     * Store a newly created DownloadHistory in storage.
     *
     * @param CreateDownloadHistoryRequest $DownloadHistoryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateDownloadHistoryRequest $DownloadHistoryRequestObj)
    {
        $input = $DownloadHistoryRequestObj->all();

        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->create($input);

        return $this->sendResponse($DownloadHistoryObj, 'DownloadHistory saved successfully');
    }

    /**
     * Display the specified DownloadHistory.
     * GET|HEAD /downloadHistories/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var DownloadHistory $downloadHistory */
        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->findWithoutFail($id);
        if (empty($DownloadHistoryObj))
        {
            return Response::json(ResponseUtil::makeError('DownloadHistory not found'), 404);
        }

        return $this->sendResponse($DownloadHistoryObj, 'DownloadHistory retrieved successfully');
    }

    /**
     * Update the specified DownloadHistory in storage.
     * PUT/PATCH /downloadHistories/{id}
     *
     * @param integer $id
     * @param UpdateDownloadHistoryRequest $DownloadHistoryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateDownloadHistoryRequest $DownloadHistoryRequestObj)
    {
        $input = $DownloadHistoryRequestObj->all();
        /** @var DownloadHistory $DownloadHistoryObj */
        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->findWithoutFail($id);
        if (empty($DownloadHistoryObj))
        {
            return Response::json(ResponseUtil::makeError('DownloadHistory not found'), 404);
        }
        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->update($input, $id);

        return $this->sendResponse($DownloadHistoryObj, 'DownloadHistory updated successfully');
    }

    /**
     * Remove the specified DownloadHistory from storage.
     * DELETE /downloadHistories/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var DownloadHistory $DownloadHistoryObj */
        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->findWithoutFail($id);
        if (empty($DownloadHistoryObj))
        {
            return Response::json(ResponseUtil::makeError('DownloadHistory not found'), 404);
        }

        $this->DownloadHistoryRepositoryObj->delete($id);

        return $this->sendResponse($id, 'DownloadHistory deleted successfully');
    }
}
