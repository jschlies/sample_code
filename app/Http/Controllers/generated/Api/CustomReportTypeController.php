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

use App\Waypoint\Http\Requests\Generated\Api\CreateCustomReportTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCustomReportTypeRequest;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Repositories\CustomReportTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CustomReportTypeController
 */
final class CustomReportTypeController extends BaseApiController
{
    /** @var  CustomReportTypeRepository */
    private $CustomReportTypeRepositoryObj;

    public function __construct(CustomReportTypeRepository $CustomReportTypeRepositoryObj)
    {
        $this->CustomReportTypeRepositoryObj = $CustomReportTypeRepositoryObj;
        parent::__construct($CustomReportTypeRepositoryObj);
    }

    /**
     * Display a listing of the CustomReportType.
     * GET|HEAD /customReportTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CustomReportTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CustomReportTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CustomReportTypeObjArr = $this->CustomReportTypeRepositoryObj->all();

        return $this->sendResponse($CustomReportTypeObjArr, 'CustomReportType(s) retrieved successfully');
    }

    /**
     * Store a newly created CustomReportType in storage.
     *
     * @param CreateCustomReportTypeRequest $CustomReportTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCustomReportTypeRequest $CustomReportTypeRequestObj)
    {
        $input = $CustomReportTypeRequestObj->all();

        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->create($input);

        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType saved successfully');
    }

    /**
     * Display the specified CustomReportType.
     * GET|HEAD /customReportTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CustomReportType $customReportType */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }

        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType retrieved successfully');
    }

    /**
     * Update the specified CustomReportType in storage.
     * PUT/PATCH /customReportTypes/{id}
     *
     * @param integer $id
     * @param UpdateCustomReportTypeRequest $CustomReportTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCustomReportTypeRequest $CustomReportTypeRequestObj)
    {
        $input = $CustomReportTypeRequestObj->all();
        /** @var CustomReportType $CustomReportTypeObj */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType updated successfully');
    }

    /**
     * Remove the specified CustomReportType from storage.
     * DELETE /customReportTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CustomReportType $CustomReportTypeObj */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }

        $this->CustomReportTypeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CustomReportType deleted successfully');
    }
}
