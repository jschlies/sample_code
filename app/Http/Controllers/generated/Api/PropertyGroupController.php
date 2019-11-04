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

use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyGroupRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyGroupRequest;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\PropertyGroupRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupController
 */
final class PropertyGroupController extends BaseApiController
{
    /** @var  PropertyGroupRepository */
    private $PropertyGroupRepositoryObj;

    public function __construct(PropertyGroupRepository $PropertyGroupRepositoryObj)
    {
        $this->PropertyGroupRepositoryObj = $PropertyGroupRepositoryObj;
        parent::__construct($PropertyGroupRepositoryObj);
    }

    /**
     * Display a listing of the PropertyGroup.
     * GET|HEAD /propertyGroups
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyGroupRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupObjArr = $this->PropertyGroupRepositoryObj->all();

        return $this->sendResponse($PropertyGroupObjArr, 'PropertyGroup(s) retrieved successfully');
    }

    /**
     * Store a newly created PropertyGroup in storage.
     *
     * @param CreatePropertyGroupRequest $PropertyGroupRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePropertyGroupRequest $PropertyGroupRequestObj)
    {
        $input = $PropertyGroupRequestObj->all();

        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->create($input);

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup saved successfully');
    }

    /**
     * Display the specified PropertyGroup.
     * GET|HEAD /propertyGroups/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var PropertyGroup $propertyGroup */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup retrieved successfully');
    }

    /**
     * Update the specified PropertyGroup in storage.
     * PUT/PATCH /propertyGroups/{id}
     *
     * @param integer $id
     * @param UpdatePropertyGroupRequest $PropertyGroupRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePropertyGroupRequest $PropertyGroupRequestObj)
    {
        $input = $PropertyGroupRequestObj->all();
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup updated successfully');
    }

    /**
     * Remove the specified PropertyGroup from storage.
     * DELETE /propertyGroups/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }

        $this->PropertyGroupRepositoryObj->delete($id);

        return $this->sendResponse($id, 'PropertyGroup deleted successfully');
    }
}
