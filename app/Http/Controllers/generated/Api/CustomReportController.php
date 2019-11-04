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

use App\Waypoint\Http\Requests\Generated\Api\CreateCustomReportRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCustomReportRequest;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Repositories\CustomReportRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CustomReportController
 */
final class CustomReportController extends BaseApiController
{
    /** @var  CustomReportRepository */
    private $CustomReportRepositoryObj;

    public function __construct(CustomReportRepository $CustomReportRepositoryObj)
    {
        $this->CustomReportRepositoryObj = $CustomReportRepositoryObj;
        parent::__construct($CustomReportRepositoryObj);
    }

    /**
     * Display a listing of the CustomReport.
     * GET|HEAD /customReports
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CustomReportRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CustomReportRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CustomReportObjArr = $this->CustomReportRepositoryObj->all();

        return $this->sendResponse($CustomReportObjArr, 'CustomReport(s) retrieved successfully');
    }

    /**
     * Store a newly created CustomReport in storage.
     *
     * @param CreateCustomReportRequest $CustomReportRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCustomReportRequest $CustomReportRequestObj)
    {
        $input = $CustomReportRequestObj->all();

        $CustomReportObj = $this->CustomReportRepositoryObj->create($input);

        return $this->sendResponse($CustomReportObj, 'CustomReport saved successfully');
    }

    /**
     * Display the specified CustomReport.
     * GET|HEAD /customReports/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CustomReport $customReport */
        $CustomReportObj = $this->CustomReportRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReport not found'), 404);
        }

        return $this->sendResponse($CustomReportObj, 'CustomReport retrieved successfully');
    }

    /**
     * Update the specified CustomReport in storage.
     * PUT/PATCH /customReports/{id}
     *
     * @param integer $id
     * @param UpdateCustomReportRequest $CustomReportRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCustomReportRequest $CustomReportRequestObj)
    {
        $input = $CustomReportRequestObj->all();
        /** @var CustomReport $CustomReportObj */
        $CustomReportObj = $this->CustomReportRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReport not found'), 404);
        }
        $CustomReportObj = $this->CustomReportRepositoryObj->update($input, $id);

        return $this->sendResponse($CustomReportObj, 'CustomReport updated successfully');
    }

    /**
     * Remove the specified CustomReport from storage.
     * DELETE /customReports/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CustomReport $CustomReportObj */
        $CustomReportObj = $this->CustomReportRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReport not found'), 404);
        }

        $this->CustomReportRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CustomReport deleted successfully');
    }
}
