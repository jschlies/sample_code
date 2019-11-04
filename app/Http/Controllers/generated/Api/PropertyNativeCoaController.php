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

use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyNativeCoaRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyNativeCoaRequest;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Repositories\PropertyNativeCoaRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyNativeCoaController
 */
final class PropertyNativeCoaController extends BaseApiController
{
    /** @var  PropertyNativeCoaRepository */
    private $PropertyNativeCoaRepositoryObj;

    public function __construct(PropertyNativeCoaRepository $PropertyNativeCoaRepositoryObj)
    {
        $this->PropertyNativeCoaRepositoryObj = $PropertyNativeCoaRepositoryObj;
        parent::__construct($PropertyNativeCoaRepositoryObj);
    }

    /**
     * Display a listing of the PropertyNativeCoa.
     * GET|HEAD /propertyNativeCoas
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyNativeCoaRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyNativeCoaRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyNativeCoaObjArr = $this->PropertyNativeCoaRepositoryObj->all();

        return $this->sendResponse($PropertyNativeCoaObjArr, 'PropertyNativeCoa(s) retrieved successfully');
    }

    /**
     * Store a newly created PropertyNativeCoa in storage.
     *
     * @param CreatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj)
    {
        $input = $PropertyNativeCoaRequestObj->all();

        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->create($input);

        return $this->sendResponse($PropertyNativeCoaObj, 'PropertyNativeCoa saved successfully');
    }

    /**
     * Display the specified PropertyNativeCoa.
     * GET|HEAD /propertyNativeCoas/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var PropertyNativeCoa $propertyNativeCoa */
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($PropertyNativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyNativeCoa not found'), 404);
        }

        return $this->sendResponse($PropertyNativeCoaObj, 'PropertyNativeCoa retrieved successfully');
    }

    /**
     * Update the specified PropertyNativeCoa in storage.
     * PUT/PATCH /propertyNativeCoas/{id}
     *
     * @param integer $id
     * @param UpdatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj)
    {
        $input = $PropertyNativeCoaRequestObj->all();
        /** @var PropertyNativeCoa $PropertyNativeCoaObj */
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($PropertyNativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyNativeCoa not found'), 404);
        }
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyNativeCoaObj, 'PropertyNativeCoa updated successfully');
    }

    /**
     * Remove the specified PropertyNativeCoa from storage.
     * DELETE /propertyNativeCoas/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var PropertyNativeCoa $PropertyNativeCoaObj */
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($PropertyNativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyNativeCoa not found'), 404);
        }

        $this->PropertyNativeCoaRepositoryObj->delete($id);

        return $this->sendResponse($id, 'PropertyNativeCoa deleted successfully');
    }
}
