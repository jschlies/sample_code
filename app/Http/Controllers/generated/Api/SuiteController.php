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

use App\Waypoint\Http\Requests\Generated\Api\CreateSuiteRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateSuiteRequest;
use App\Waypoint\Models\Suite;
use App\Waypoint\Repositories\SuiteRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class SuiteController
 */
final class SuiteController extends BaseApiController
{
    /** @var  SuiteRepository */
    private $SuiteRepositoryObj;

    public function __construct(SuiteRepository $SuiteRepositoryObj)
    {
        $this->SuiteRepositoryObj = $SuiteRepositoryObj;
        parent::__construct($SuiteRepositoryObj);
    }

    /**
     * Display a listing of the Suite.
     * GET|HEAD /suites
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->SuiteRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->SuiteRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $SuiteObjArr = $this->SuiteRepositoryObj->all();

        return $this->sendResponse($SuiteObjArr, 'Suite(s) retrieved successfully');
    }

    /**
     * Store a newly created Suite in storage.
     *
     * @param CreateSuiteRequest $SuiteRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateSuiteRequest $SuiteRequestObj)
    {
        $input = $SuiteRequestObj->all();

        $SuiteObj = $this->SuiteRepositoryObj->create($input);

        return $this->sendResponse($SuiteObj, 'Suite saved successfully');
    }

    /**
     * Display the specified Suite.
     * GET|HEAD /suites/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Suite $suite */
        $SuiteObj = $this->SuiteRepositoryObj->findWithoutFail($id);
        if (empty($SuiteObj))
        {
            return Response::json(ResponseUtil::makeError('Suite not found'), 404);
        }

        return $this->sendResponse($SuiteObj, 'Suite retrieved successfully');
    }

    /**
     * Update the specified Suite in storage.
     * PUT/PATCH /suites/{id}
     *
     * @param integer $id
     * @param UpdateSuiteRequest $SuiteRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateSuiteRequest $SuiteRequestObj)
    {
        $input = $SuiteRequestObj->all();
        /** @var Suite $SuiteObj */
        $SuiteObj = $this->SuiteRepositoryObj->findWithoutFail($id);
        if (empty($SuiteObj))
        {
            return Response::json(ResponseUtil::makeError('Suite not found'), 404);
        }
        $SuiteObj = $this->SuiteRepositoryObj->update($input, $id);

        return $this->sendResponse($SuiteObj, 'Suite updated successfully');
    }

    /**
     * Remove the specified Suite from storage.
     * DELETE /suites/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Suite $SuiteObj */
        $SuiteObj = $this->SuiteRepositoryObj->findWithoutFail($id);
        if (empty($SuiteObj))
        {
            return Response::json(ResponseUtil::makeError('Suite not found'), 404);
        }

        $this->SuiteRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Suite deleted successfully');
    }
}
