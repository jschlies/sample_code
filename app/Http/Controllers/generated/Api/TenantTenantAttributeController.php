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

use App\Waypoint\Http\Requests\Generated\Api\CreateTenantTenantAttributeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantTenantAttributeRequest;
use App\Waypoint\Models\TenantTenantAttribute;
use App\Waypoint\Repositories\TenantTenantAttributeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantTenantAttributeController
 */
final class TenantTenantAttributeController extends BaseApiController
{
    /** @var  TenantTenantAttributeRepository */
    private $TenantTenantAttributeRepositoryObj;

    public function __construct(TenantTenantAttributeRepository $TenantTenantAttributeRepositoryObj)
    {
        $this->TenantTenantAttributeRepositoryObj = $TenantTenantAttributeRepositoryObj;
        parent::__construct($TenantTenantAttributeRepositoryObj);
    }

    /**
     * Display a listing of the TenantTenantAttribute.
     * GET|HEAD /tenantTenantAttributes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->TenantTenantAttributeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantTenantAttributeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $TenantTenantAttributeObjArr = $this->TenantTenantAttributeRepositoryObj->all();

        return $this->sendResponse($TenantTenantAttributeObjArr, 'TenantTenantAttribute(s) retrieved successfully');
    }

    /**
     * Store a newly created TenantTenantAttribute in storage.
     *
     * @param CreateTenantTenantAttributeRequest $TenantTenantAttributeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateTenantTenantAttributeRequest $TenantTenantAttributeRequestObj)
    {
        $input = $TenantTenantAttributeRequestObj->all();

        $TenantTenantAttributeObj = $this->TenantTenantAttributeRepositoryObj->create($input);

        return $this->sendResponse($TenantTenantAttributeObj, 'TenantTenantAttribute saved successfully');
    }

    /**
     * Display the specified TenantTenantAttribute.
     * GET|HEAD /tenantTenantAttributes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var TenantTenantAttribute $tenantTenantAttribute */
        $TenantTenantAttributeObj = $this->TenantTenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantTenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantTenantAttribute not found'), 404);
        }

        return $this->sendResponse($TenantTenantAttributeObj, 'TenantTenantAttribute retrieved successfully');
    }

    /**
     * Update the specified TenantTenantAttribute in storage.
     * PUT/PATCH /tenantTenantAttributes/{id}
     *
     * @param integer $id
     * @param UpdateTenantTenantAttributeRequest $TenantTenantAttributeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateTenantTenantAttributeRequest $TenantTenantAttributeRequestObj)
    {
        $input = $TenantTenantAttributeRequestObj->all();
        /** @var TenantTenantAttribute $TenantTenantAttributeObj */
        $TenantTenantAttributeObj = $this->TenantTenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantTenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantTenantAttribute not found'), 404);
        }
        $TenantTenantAttributeObj = $this->TenantTenantAttributeRepositoryObj->update($input, $id);

        return $this->sendResponse($TenantTenantAttributeObj, 'TenantTenantAttribute updated successfully');
    }

    /**
     * Remove the specified TenantTenantAttribute from storage.
     * DELETE /tenantTenantAttributes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var TenantTenantAttribute $TenantTenantAttributeObj */
        $TenantTenantAttributeObj = $this->TenantTenantAttributeRepositoryObj->findWithoutFail($id);
        if (empty($TenantTenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantTenantAttribute not found'), 404);
        }

        $this->TenantTenantAttributeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'TenantTenantAttribute deleted successfully');
    }
}
