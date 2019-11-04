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

use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListPropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAccessListPropertyRequest;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Repositories\AccessListPropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccessListPropertyController
 */
final class AccessListPropertyController extends BaseApiController
{
    /** @var  AccessListPropertyRepository */
    private $AccessListPropertyRepositoryObj;

    public function __construct(AccessListPropertyRepository $AccessListPropertyRepositoryObj)
    {
        $this->AccessListPropertyRepositoryObj = $AccessListPropertyRepositoryObj;
        parent::__construct($AccessListPropertyRepositoryObj);
    }

    /**
     * Display a listing of the AccessListProperty.
     * GET|HEAD /accessListProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AccessListPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AccessListPropertyObjArr = $this->AccessListPropertyRepositoryObj->all();

        return $this->sendResponse($AccessListPropertyObjArr, 'AccessListProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created AccessListProperty in storage.
     *
     * @param CreateAccessListPropertyRequest $AccessListPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAccessListPropertyRequest $AccessListPropertyRequestObj)
    {
        $input = $AccessListPropertyRequestObj->all();

        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->create($input);

        return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty saved successfully');
    }

    /**
     * Display the specified AccessListProperty.
     * GET|HEAD /accessListProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AccessListProperty $accessListProperty */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWithoutFail($id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }

        return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty retrieved successfully');
    }

    /**
     * Update the specified AccessListProperty in storage.
     * PUT/PATCH /accessListProperties/{id}
     *
     * @param integer $id
     * @param UpdateAccessListPropertyRequest $AccessListPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAccessListPropertyRequest $AccessListPropertyRequestObj)
    {
        $input = $AccessListPropertyRequestObj->all();
        /** @var AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWithoutFail($id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->update($input, $id);

        return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty updated successfully');
    }

    /**
     * Remove the specified AccessListProperty from storage.
     * DELETE /accessListProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWithoutFail($id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }

        $this->AccessListPropertyRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AccessListProperty deleted successfully');
    }
}
