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

use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAccessListRequest;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Repositories\AccessListRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccessListController
 */
final class AccessListController extends BaseApiController
{
    /** @var  AccessListRepository */
    private $AccessListRepositoryObj;

    public function __construct(AccessListRepository $AccessListRepositoryObj)
    {
        $this->AccessListRepositoryObj = $AccessListRepositoryObj;
        parent::__construct($AccessListRepositoryObj);
    }

    /**
     * Display a listing of the AccessList.
     * GET|HEAD /accessLists
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AccessListRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AccessListObjArr = $this->AccessListRepositoryObj->all();

        return $this->sendResponse($AccessListObjArr, 'AccessList(s) retrieved successfully');
    }

    /**
     * Store a newly created AccessList in storage.
     *
     * @param CreateAccessListRequest $AccessListRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAccessListRequest $AccessListRequestObj)
    {
        $input = $AccessListRequestObj->all();

        $AccessListObj = $this->AccessListRepositoryObj->create($input);

        return $this->sendResponse($AccessListObj, 'AccessList saved successfully');
    }

    /**
     * Display the specified AccessList.
     * GET|HEAD /accessLists/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AccessList $accessList */
        $AccessListObj = $this->AccessListRepositoryObj->findWithoutFail($id);
        if (empty($AccessListObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }

        return $this->sendResponse($AccessListObj, 'AccessList retrieved successfully');
    }

    /**
     * Update the specified AccessList in storage.
     * PUT/PATCH /accessLists/{id}
     *
     * @param integer $id
     * @param UpdateAccessListRequest $AccessListRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAccessListRequest $AccessListRequestObj)
    {
        $input = $AccessListRequestObj->all();
        /** @var AccessList $AccessListObj */
        $AccessListObj = $this->AccessListRepositoryObj->findWithoutFail($id);
        if (empty($AccessListObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }
        $AccessListObj = $this->AccessListRepositoryObj->update($input, $id);

        return $this->sendResponse($AccessListObj, 'AccessList updated successfully');
    }

    /**
     * Remove the specified AccessList from storage.
     * DELETE /accessLists/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AccessList $AccessListObj */
        $AccessListObj = $this->AccessListRepositoryObj->findWithoutFail($id);
        if (empty($AccessListObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }

        $this->AccessListRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AccessList deleted successfully');
    }
}
