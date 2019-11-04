<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Repositories\TenantTenantAttributeDetailRepository;
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
use App\Waypoint\Models\TenantTenantAttribute;
use App\Waypoint\Repositories\TenantTenantAttributeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantTenantAttributeDetailController
 */
final class TenantTenantAttributeDetailController extends BaseApiController
{
    /** @var  TenantTenantAttributeRepository */
    private $TenantTenantAttributeDetailRepositoryObj;

    public function __construct(TenantTenantAttributeDetailRepository $TenantTenantAttributeDetailRepositoryObj)
    {
        $this->TenantTenantAttributeDetailRepositoryObj = $TenantTenantAttributeDetailRepositoryObj;
        parent::__construct($TenantTenantAttributeDetailRepositoryObj);
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
    public function index($tenant_id, Request $RequestObj)
    {
        $this->TenantTenantAttributeDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantTenantAttributeDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        /** @var Tenant $TenantObj */
        $TenantObj = $this->TenantTenantAttributeDetailRepositoryObj->find($tenant_id);

        return $this->sendResponse($TenantObj->tenantAttributeDetails(), 'TenantAttributeDetail(s) retrieved successfully');
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

        $TenantTenantAttributeObj = $this->TenantTenantAttributeDetailRepositoryObj->create($input);

        return $this->sendResponse($TenantTenantAttributeObj, 'TenantTenantAttribute saved successfully');
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
    public function destroy($client_id, $tenant_id, $tenant_tenant_attribute_id)
    {
        /** @var TenantTenantAttribute $TenantTenantAttributeObj */
        $TenantTenantAttributeObj = $this->TenantTenantAttributeDetailRepositoryObj->findWithoutFail($tenant_tenant_attribute_id);
        if (empty($TenantTenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantTenantAttribute not found'), 404);
        }

        $this->TenantTenantAttributeDetailRepositoryObj->delete($tenant_tenant_attribute_id);

        return $this->sendResponse($tenant_tenant_attribute_id, 'TenantTenantAttribute deleted successfully');
    }
}
