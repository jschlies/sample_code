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

use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateRequest;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\ReportTemplateRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReportTemplateController
 */
final class ReportTemplateController extends BaseApiController
{
    /** @var  ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;

    public function __construct(ReportTemplateRepository $ReportTemplateRepositoryObj)
    {
        $this->ReportTemplateRepositoryObj = $ReportTemplateRepositoryObj;
        parent::__construct($ReportTemplateRepositoryObj);
    }

    /**
     * Display a listing of the ReportTemplate.
     * GET|HEAD /reportTemplates
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ReportTemplateRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ReportTemplateRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ReportTemplateObjArr = $this->ReportTemplateRepositoryObj->all();

        return $this->sendResponse($ReportTemplateObjArr, 'ReportTemplate(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplate in storage.
     *
     * @param CreateReportTemplateRequest $ReportTemplateRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateReportTemplateRequest $ReportTemplateRequestObj)
    {
        $input = $ReportTemplateRequestObj->all();

        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->create($input);

        return $this->sendResponse($ReportTemplateObj, 'ReportTemplate saved successfully');
    }

    /**
     * Display the specified ReportTemplate.
     * GET|HEAD /reportTemplates/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ReportTemplate $reportTemplate */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }

        return $this->sendResponse($ReportTemplateObj, 'ReportTemplate retrieved successfully');
    }

    /**
     * Update the specified ReportTemplate in storage.
     * PUT/PATCH /reportTemplates/{id}
     *
     * @param integer $id
     * @param UpdateReportTemplateRequest $ReportTemplateRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateReportTemplateRequest $ReportTemplateRequestObj)
    {
        $input = $ReportTemplateRequestObj->all();
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->update($input, $id);

        return $this->sendResponse($ReportTemplateObj, 'ReportTemplate updated successfully');
    }

    /**
     * Remove the specified ReportTemplate from storage.
     * DELETE /reportTemplates/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($id);
        if (empty($ReportTemplateObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }

        $this->ReportTemplateRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ReportTemplate deleted successfully');
    }
}
