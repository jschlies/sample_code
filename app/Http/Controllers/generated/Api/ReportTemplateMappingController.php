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

use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateMappingRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateMappingRequest;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Repositories\ReportTemplateMappingRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReportTemplateMappingController
 */
final class ReportTemplateMappingController extends BaseApiController
{
    /** @var  ReportTemplateMappingRepository */
    private $ReportTemplateMappingRepositoryObj;

    public function __construct(ReportTemplateMappingRepository $ReportTemplateMappingRepositoryObj)
    {
        $this->ReportTemplateMappingRepositoryObj = $ReportTemplateMappingRepositoryObj;
        parent::__construct($ReportTemplateMappingRepositoryObj);
    }

    /**
     * Display a listing of the ReportTemplateMapping.
     * GET|HEAD /reportTemplateMappings
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ReportTemplateMappingRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ReportTemplateMappingRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ReportTemplateMappingObjArr = $this->ReportTemplateMappingRepositoryObj->all();

        return $this->sendResponse($ReportTemplateMappingObjArr, 'ReportTemplateMapping(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplateMapping in storage.
     *
     * @param CreateReportTemplateMappingRequest $ReportTemplateMappingRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateReportTemplateMappingRequest $ReportTemplateMappingRequestObj)
    {
        $input = $ReportTemplateMappingRequestObj->all();

        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->create($input);

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping saved successfully');
    }

    /**
     * Display the specified ReportTemplateMapping.
     * GET|HEAD /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ReportTemplateMapping $reportTemplateMapping */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping retrieved successfully');
    }

    /**
     * Update the specified ReportTemplateMapping in storage.
     * PUT/PATCH /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @param UpdateReportTemplateMappingRequest $ReportTemplateMappingRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateReportTemplateMappingRequest $ReportTemplateMappingRequestObj)
    {
        $input = $ReportTemplateMappingRequestObj->all();
        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->update($input, $id);

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping updated successfully');
    }

    /**
     * Remove the specified ReportTemplateMapping from storage.
     * DELETE /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }

        $this->ReportTemplateMappingRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ReportTemplateMapping deleted successfully');
    }
}
