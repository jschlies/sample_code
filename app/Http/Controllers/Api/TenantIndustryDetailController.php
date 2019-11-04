<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateTenantIndustryRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantIndustryRequest;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Models\TenantIndustryDetail;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\TenantRepository;
use App\Waypoint\Repositories\TenantIndustryDetailRepository;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class TenantController``
 */
final class TenantIndustryDetailController extends BaseApiController
{
    /** @var  TenantRepository */
    private $TenantIndustryDetailRepositoryObj;

    public function __construct(TenantIndustryDetailRepository $TenantIndustryDetailRepositoryObj)
    {
        $this->TenantIndustryDetailRepositoryObj = $TenantIndustryDetailRepositoryObj;
        $this->ClientRepositoryObj               = App::make(ClientRepository::class);
        parent::__construct($TenantIndustryDetailRepositoryObj);
    }

    /**
     * Display a listing of the Tenant.
     * GET|HEAD /tenants
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index($client_id, Request $RequestObj)
    {
        $this->TenantIndustryDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantIndustryDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var PropertyGroup $PropertyGroupObj */
        $ClientObj = $this->ClientRepositoryObj->find($client_id);

        return $this->sendResponse($ClientObj->tenantIndustryDetails, 'TenantIndustryDetail(s) retrieved successfully');
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
    public function show($client_id, $tenant_industry_id)
    {
        /** @var TenantIndustryDetail $tenantTenantAttribute */
        $TenantIndustryDetailObj = $this->TenantIndustryDetailRepositoryObj->findWithoutFail($tenant_industry_id);
        if (empty($TenantIndustryDetailObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }

        return $this->sendResponse($TenantIndustryDetailObj, 'TenantIndustryDetail retrieved successfully');
    }

    /**
     * Store a newly created TenantIndustry in storage.
     *
     * @param CreateTenantIndustryRequest $TenantIndustryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store($client_id, CreateTenantIndustryRequest $TenantIndustryRequestObj)
    {
        $input = $TenantIndustryRequestObj->all();

        $TenantIndustryObj = $this->TenantIndustryDetailRepositoryObj->create($input);

        return $this->sendResponse($TenantIndustryObj, 'TenantIndustry saved successfully');
    }

    /**
     * Update the specified TenantIndustry in storage.
     * PUT/PATCH /tenantIndustries/{id}
     *
     * @param integer $id
     * @param UpdateTenantIndustryRequest $TenantIndustryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($client_id, $tenant_attribute_id, UpdateTenantIndustryRequest $TenantIndustryRequestObj)
    {
        $input = $TenantIndustryRequestObj->all();
        /** @var TenantIndustry $TenantIndustryObj */
        $TenantIndustryObj = $this->TenantIndustryDetailRepositoryObj->findWithoutFail($tenant_attribute_id);
        if (empty($TenantIndustryObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }
        $TenantIndustryObj = $this->TenantIndustryDetailRepositoryObj->update($input, $tenant_attribute_id);

        return $this->sendResponse($TenantIndustryObj, 'TenantIndustry updated successfully');
    }

    /**
     * Remove the specified TenantIndustryDetail from storage.
     * DELETE /tenantIndustryDetail/{id}
     *
     * @param integer $tenant_industry_detail_id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($client_id, $tenant_industry_id)
    {
        /** @var TenantIndustryDetail $TenantTenantAttributeObj */
        $TenantIndustryDetailObj = $this->TenantIndustryDetailRepositoryObj->findWithoutFail($tenant_industry_id);
        if (empty($TenantIndustryDetailObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }
        if ($TenantIndustryDetailObj->tenants->count())
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry is tied to a tenant'), 404);
        }

        $this->TenantIndustryDetailRepositoryObj->delete($tenant_industry_id);

        return $this->sendResponse($tenant_industry_id, 'TenantIndustry deleted successfully');
    }
}
