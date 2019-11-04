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

use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyRequest;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\PropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyController
 */
final class PropertyController extends BaseApiController
{
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;

    public function __construct(PropertyRepository $PropertyRepositoryObj)
    {
        $this->PropertyRepositoryObj = $PropertyRepositoryObj;
        parent::__construct($PropertyRepositoryObj);
    }

    /**
     * Display a listing of the Property.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyObjArr = $this->PropertyRepositoryObj->all();

        return $this->sendResponse($PropertyObjArr, 'Property(s) retrieved successfully');
    }

    /**
     * Store a newly created Property in storage.
     *
     * @param CreatePropertyRequest $PropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePropertyRequest $PropertyRequestObj)
    {
        $input = $PropertyRequestObj->all();

        $PropertyObj = $this->PropertyRepositoryObj->create($input);

        return $this->sendResponse($PropertyObj, 'Property saved successfully');
    }

    /**
     * Display the specified Property.
     * GET|HEAD /properties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Property $property */
        $PropertyObj = $this->PropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyObj))
        {
            return Response::json(ResponseUtil::makeError('Property not found'), 404);
        }

        return $this->sendResponse($PropertyObj, 'Property retrieved successfully');
    }

    /**
     * Update the specified Property in storage.
     * PUT/PATCH /properties/{id}
     *
     * @param integer $id
     * @param UpdatePropertyRequest $PropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePropertyRequest $PropertyRequestObj)
    {
        $input = $PropertyRequestObj->all();
        /** @var Property $PropertyObj */
        $PropertyObj = $this->PropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyObj))
        {
            return Response::json(ResponseUtil::makeError('Property not found'), 404);
        }
        $PropertyObj = $this->PropertyRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyObj, 'Property updated successfully');
    }

    /**
     * Remove the specified Property from storage.
     * DELETE /properties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Property $PropertyObj */
        $PropertyObj = $this->PropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyObj))
        {
            return Response::json(ResponseUtil::makeError('Property not found'), 404);
        }

        $this->PropertyRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Property deleted successfully');
    }
}
