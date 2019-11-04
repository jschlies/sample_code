<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Repositories\TenantRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Waypoint\Http\Requests\Generated\Api\CreateTenantAttributeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantAttributeRequest;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Repositories\TenantAttributeDetailRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantAttributeDetailController
 */
class TenantAttributeDetailController extends BaseApiController
{
    /** @var  TenantAttributeDetailRepository */
    private $TenantAttributeDetailRepositoryObj;
    /** @var  TenantAttributeDetailRepository */
    private $TenantRepositoryObj;

    public function __construct(TenantAttributeDetailRepository $TenantAttributeDetailRepositoryObj)
    {
        $this->TenantAttributeDetailRepositoryObj = $TenantAttributeDetailRepositoryObj;
        $this->TenantRepositoryObj                = App::make(TenantRepository::class);
        parent::__construct($TenantAttributeDetailRepositoryObj);
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
    public function index($client_id, Request $RequestObj)
    {
        $this->TenantAttributeDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantAttributeDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $TenantAttributeObjArr = $this->TenantAttributeDetailRepositoryObj->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($TenantAttributeObjArr, 'TenantAttribute(s) retrieved successfully');
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
    public function indexForTenant($client_id, $tenant_id, Request $RequestObj)
    {
        $this->TenantAttributeDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantAttributeDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        /** @var Tenant $TenantObj */
        $TenantObj = $this->TenantRepositoryObj->findWhere(['id' => $tenant_id])->first();

        return $this->sendResponse($TenantObj->tenantAttributeDetails, 'TenantAttribute(s) retrieved successfully');
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
    public function store($client_id, CreateTenantAttributeRequest $TenantAttributeRequestObj)
    {
        $input = $TenantAttributeRequestObj->all();

        $TenantAttributeObj = $this->TenantAttributeDetailRepositoryObj->create($input);

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
    public function show($client_id, $tenant_attribute_id)
    {
        /** @var TenantAttribute $tenantAttribute */
        $TenantAttributeObj = $this->TenantAttributeDetailRepositoryObj->findWithoutFail($tenant_attribute_id);
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
    public function update($client_id, $tenant_attribute_id, UpdateTenantAttributeRequest $TenantAttributeRequestObj)
    {
        $input = $TenantAttributeRequestObj->all();
        /** @var TenantAttribute $TenantAttributeObj */
        $TenantAttributeObj = $this->TenantAttributeDetailRepositoryObj->findWithoutFail($tenant_attribute_id);
        if (empty($TenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantAttribute not found'), 404);
        }
        $TenantAttributeObj = $this->TenantAttributeDetailRepositoryObj->update($input, $tenant_attribute_id);

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
    public function destroy($client_id, $tenant_attribute_id)
    {
        /** @var TenantAttribute $TenantAttributeObj */
        $TenantAttributeObj = $this->TenantAttributeDetailRepositoryObj->findWithoutFail($tenant_attribute_id);
        if (empty($TenantAttributeObj))
        {
            return Response::json(ResponseUtil::makeError('TenantAttribute not found'), 404);
        }

        $this->TenantAttributeDetailRepositoryObj->delete($tenant_attribute_id);

        return $this->sendResponse($tenant_attribute_id, 'TenantAttribute deleted successfully');
    }
}
