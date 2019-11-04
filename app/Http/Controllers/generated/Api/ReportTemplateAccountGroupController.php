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

use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateAccountGroupRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateAccountGroupRequest;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReportTemplateAccountGroupController
 */
final class ReportTemplateAccountGroupController extends BaseApiController
{
    /** @var  ReportTemplateAccountGroupRepository */
    private $ReportTemplateAccountGroupRepositoryObj;

    public function __construct(ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj)
    {
        $this->ReportTemplateAccountGroupRepositoryObj = $ReportTemplateAccountGroupRepositoryObj;
        parent::__construct($ReportTemplateAccountGroupRepositoryObj);
    }

    /**
     * Display a listing of the ReportTemplateAccountGroup.
     * GET|HEAD /reportTemplateAccountGroups
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ReportTemplateAccountGroupRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ReportTemplateAccountGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ReportTemplateAccountGroupObjArr = $this->ReportTemplateAccountGroupRepositoryObj->all();

        return $this->sendResponse($ReportTemplateAccountGroupObjArr, 'ReportTemplateAccountGroup(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplateAccountGroup in storage.
     *
     * @param CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj)
    {
        $input = $ReportTemplateAccountGroupRequestObj->all();

        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->create($input);

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup saved successfully');
    }

    /**
     * Display the specified ReportTemplateAccountGroup.
     * GET|HEAD /reportTemplateAccountGroups/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ReportTemplateAccountGroup $reportTemplateAccountGroup */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup retrieved successfully');
    }

    /**
     * Update the specified ReportTemplateAccountGroup in storage.
     * PUT/PATCH /reportTemplateAccountGroups/{id}
     *
     * @param integer $id
     * @param UpdateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj)
    {
        $input = $ReportTemplateAccountGroupRequestObj->all();
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->update($input, $id);

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup updated successfully');
    }

    /**
     * Remove the specified ReportTemplateAccountGroup from storage.
     * DELETE /reportTemplateAccountGroups/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }

        $this->ReportTemplateAccountGroupRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ReportTemplateAccountGroup deleted successfully');
    }
}
