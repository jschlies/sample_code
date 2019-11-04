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

use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyGroupPropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyGroupPropertyRequest;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupPropertyController
 */
final class PropertyGroupPropertyController extends BaseApiController
{
    /** @var  PropertyGroupPropertyRepository */
    private $PropertyGroupPropertyRepositoryObj;

    public function __construct(PropertyGroupPropertyRepository $PropertyGroupPropertyRepositoryObj)
    {
        $this->PropertyGroupPropertyRepositoryObj = $PropertyGroupPropertyRepositoryObj;
        parent::__construct($PropertyGroupPropertyRepositoryObj);
    }

    /**
     * Display a listing of the PropertyGroupProperty.
     * GET|HEAD /propertyGroupProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyGroupPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupPropertyObjArr = $this->PropertyGroupPropertyRepositoryObj->all();

        return $this->sendResponse($PropertyGroupPropertyObjArr, 'PropertyGroupProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created PropertyGroupProperty in storage.
     *
     * @param CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj)
    {
        $input = $PropertyGroupPropertyRequestObj->all();

        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->create($input);

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty saved successfully');
    }

    /**
     * Display the specified PropertyGroupProperty.
     * GET|HEAD /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var PropertyGroupProperty $propertyGroupProperty */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty retrieved successfully');
    }

    /**
     * Update the specified PropertyGroupProperty in storage.
     * PUT/PATCH /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @param UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj)
    {
        $input = $PropertyGroupPropertyRequestObj->all();
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty updated successfully');
    }

    /**
     * Remove the specified PropertyGroupProperty from storage.
     * DELETE /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }

        $this->PropertyGroupPropertyRepositoryObj->delete($id);

        return $this->sendResponse($id, 'PropertyGroupProperty deleted successfully');
    }
}
