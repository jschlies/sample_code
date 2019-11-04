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

use App\Waypoint\Http\Requests\Generated\Api\CreateTenantAttributeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantAttributeRequest;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Repositories\TenantAttributeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantAttributeController
 */
final class TenantAttributeController extends BaseApiController
{
    /** @var  TenantAttributeRepository */
    private $TenantAttributeRepositoryObj;

    public function __construct(TenantAttributeRepository $TenantAttributeRepositoryObj)
    {
        $this->TenantAttributeRepositoryObj = $TenantAttributeRepositoryObj;
        parent::__construct($TenantAttributeRepositoryObj);
    }

    /**
     * Display a listing of the TenantAttribute.
     * GET|HEAD /tenantAttributes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->TenantAttributeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantAttributeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $TenantAttributeObjArr = $this->TenantAttributeRepositoryObj->all();

        return $this->sendResponse($TenantAttributeObjArr, 'TenantAttribute(s) retrieved successfully');
    }

    /**
     * Store a newly created TenantAttribute in storage.
     *
     * @param CreateTenantAttributeRequest $TenantAttributeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateTenantAttributeRequest $TenantAttributeRequestObj)
    {
        $input = $TenantAttributeRequestObj->all();

        $TenantAttributeObj = $this->TenantAttributeRepositoryObj->create($input);

        return $this->sendResponse($TenantAttributeObj, 'TenantAttribute saved successfully');
    }

    /**
     * Display the specified TenantAttribute.
     * GET|HEAD /tenantAttributes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var TenantAttribute $tenantAttribute */
        $TenantAttributeObj = $this->TenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantAttribute not found'), 404);
        }

        return $this->sendResponse($TenantAttributeObj, 'TenantAttribute retrieved successfully');
    }

    /**
     * Update the specified TenantAttribute in storage.
     * PUT/PATCH /tenantAttributes/{id}
     *
     * @param integer $id
     * @param UpdateTenantAttributeRequest $TenantAttributeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateTenantAttributeRequest $TenantAttributeRequestObj)
    {
        $input = $TenantAttributeRequestObj->all();
        /** @var TenantAttribute $TenantAttributeObj */
        $TenantAttributeObj = $this->TenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantAttribute not found'), 404);
        }
        $TenantAttributeObj = $this->TenantAttributeRepositoryObj->update($input, $id);

        return $this->sendResponse($TenantAttributeObj, 'TenantAttribute updated successfully');
    }

    /**
     * Remove the specified TenantAttribute from storage.
     * DELETE /tenantAttributes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var TenantAttribute $TenantAttributeObj */
        $TenantAttributeObj = $this->TenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantAttribute not found'), 404);
        }

        $this->TenantAttributeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'TenantAttribute deleted successfully');
    }
}
