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

use App\Waypoint\Http\Requests\Generated\Api\CreateSuiteLeaseRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateSuiteLeaseRequest;
use App\Waypoint\Models\SuiteLease;
use App\Waypoint\Repositories\SuiteLeaseRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class SuiteLeaseController
 */
final class SuiteLeaseController extends BaseApiController
{
    /** @var  SuiteLeaseRepository */
    private $SuiteLeaseRepositoryObj;

    public function __construct(SuiteLeaseRepository $SuiteLeaseRepositoryObj)
    {
        $this->SuiteLeaseRepositoryObj = $SuiteLeaseRepositoryObj;
        parent::__construct($SuiteLeaseRepositoryObj);
    }

    /**
     * Display a listing of the SuiteLease.
     * GET|HEAD /suiteLeases
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->SuiteLeaseRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->SuiteLeaseRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $SuiteLeaseObjArr = $this->SuiteLeaseRepositoryObj->all();

        return $this->sendResponse($SuiteLeaseObjArr, 'SuiteLease(s) retrieved successfully');
    }

    /**
     * Store a newly created SuiteLease in storage.
     *
     * @param CreateSuiteLeaseRequest $SuiteLeaseRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateSuiteLeaseRequest $SuiteLeaseRequestObj)
    {
        $input = $SuiteLeaseRequestObj->all();

        $SuiteLeaseObj = $this->SuiteLeaseRepositoryObj->create($input);

        return $this->sendResponse($SuiteLeaseObj, 'SuiteLease saved successfully');
    }

    /**
     * Display the specified SuiteLease.
     * GET|HEAD /suiteLeases/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var SuiteLease $suiteLease */
        $SuiteLeaseObj = $this->SuiteLeaseRepositoryObj->findWithoutFail($id);
        if (empty($SuiteLeaseObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteLease not found'), 404);
        }

        return $this->sendResponse($SuiteLeaseObj, 'SuiteLease retrieved successfully');
    }

    /**
     * Update the specified SuiteLease in storage.
     * PUT/PATCH /suiteLeases/{id}
     *
     * @param integer $id
     * @param UpdateSuiteLeaseRequest $SuiteLeaseRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateSuiteLeaseRequest $SuiteLeaseRequestObj)
    {
        $input = $SuiteLeaseRequestObj->all();
        /** @var SuiteLease $SuiteLeaseObj */
        $SuiteLeaseObj = $this->SuiteLeaseRepositoryObj->findWithoutFail($id);
        if (empty($SuiteLeaseObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteLease not found'), 404);
        }
        $SuiteLeaseObj = $this->SuiteLeaseRepositoryObj->update($input, $id);

        return $this->sendResponse($SuiteLeaseObj, 'SuiteLease updated successfully');
    }

    /**
     * Remove the specified SuiteLease from storage.
     * DELETE /suiteLeases/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var SuiteLease $SuiteLeaseObj */
        $SuiteLeaseObj = $this->SuiteLeaseRepositoryObj->findWithoutFail($id);
        if (empty($SuiteLeaseObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteLease not found'), 404);
        }

        $this->SuiteLeaseRepositoryObj->delete($id);

        return $this->sendResponse($id, 'SuiteLease deleted successfully');
    }
}
