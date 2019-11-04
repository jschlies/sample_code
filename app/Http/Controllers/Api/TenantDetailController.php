<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantRequest;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\TenantDetail;
use App\Waypoint\Models\TenantDetailForPropertyGroups;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\TenantDetailRepository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class TenantDetailController
 */
final class TenantDetailController extends BaseApiController
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /** @var  TenantDetailRepository */
    private $TenantDetailRepositoryObj;
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;
    /** @var  PropertyGroupRepository */
    private $PropertyGroupRepositoryObj;
    /** @var  PropertyGroupRepository */
    private $ClientRepositoryObj;

    public function __construct(TenantDetailRepository $TenantDetailRepositoryObj)
    {
        $this->TenantDetailRepositoryObj  = $TenantDetailRepositoryObj;
        $this->PropertyRepositoryObj      = App::make(PropertyRepository::class);
        $this->PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        $this->ClientRepositoryObj        = App::make(ClientRepository::class);
        parent::__construct($TenantDetailRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param Request $RequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForProperty($client_id, $property_id, Request $RequestObj)
    {
        Lease::set_model_as_of_date($RequestObj, $client_id);

        $this->TenantDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /**
         * start here for complaints about slowness here
         *
         * keep this syncs with below
         */
        /** @var Property $PropertyObj */
        $PropertyObj = $this->PropertyRepositoryObj
            ->with('leaseDetails.suiteDetails.leaseDetails.suiteDetails')
            ->with('leaseDetails.tenantDetails.leaseDetails.suiteDetails')
            ->with('leaseDetails.tenantDetails.suiteDetails.leaseDetails.suiteDetails')
            ->with('leaseDetails.tenantDetails.tenantAttributes')
            ->with('leaseDetails.tenantDetails.tenantIndustryDetail')
            ->with('suiteDetails.leaseDetails.property')
            ->with('suiteDetails.leaseDetails.suiteDetails')
            ->with('suiteDetails.leaseDetails.tenantDetails.leaseDetails.suiteDetails')
            ->with('suiteDetails.leaseDetails.tenantDetails.suiteDetails')
            ->with('suiteDetails.leaseDetails.tenantDetails.tenantIndustryDetail')
            ->find($property_id);

        if (Lease::get_model_as_of_date())
        {
            TenantDetail::$property_id = $property_id;
            $TenantDetailObjArr        = $PropertyObj->getActiveUniqueLeaseDetailObjArr()->map(
                function (LeaseDetail $LeaseDetailObj)
                {
                    return $LeaseDetailObj->tenantDetails;
                }
            )->flatten();
        }

        $metadata = $this->get_tenant_arr_metadata(collect_waypoint([$PropertyObj]));

        return $this->sendResponse($TenantDetailObjArr, 'TenantDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param Request $RequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForPropertyGroup($client_id, $property_group_id, Request $RequestObj)
    {
        Lease::set_model_as_of_date($RequestObj, $client_id);

        $this->TenantDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj =
            $this->PropertyGroupRepositoryObj
                ->find(
                    ['id' => $property_group_id]
                )
                ->first();

        $property_group_properties_id_arr = $PropertyGroupObj->propertyGroupProperties->pluck('property_id')->toArray();

        /**
         * this route limits the leases that the TenantDetail to these properties. Not the properties that that
         * this tenant has active leases in which may be outside of $PropertyGroupObj.
         */
        //Tenant::$limit_leases_these_property_id_arr = $PropertyGroupObj->getAllPropertyIds();

        /**
         * start here for complaints about slowness here
         *
         * keep this syncs with above
         */
        $HydratedPropertyObjArr =
            $this->PropertyRepositoryObj
                ->with('leaseDetails.suiteDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.leaseDetails.property.suiteDetails.leaseDetails.property.suiteDetails.leaseDetails')
                ->with('leaseDetails.tenantDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.suiteDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.tenantAttributeDetails')
                ->with('leaseDetails.tenantDetails.tenantIndustryDetail')
                ->with('suiteDetails.leaseDetails.propertyDetail.leaseDetails')
                ->with('suiteDetails.leaseDetails.suiteDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.client.properties')
                ->with('suiteDetails.leaseDetails.tenantDetails.leaseDetails.property')
                ->with('suiteDetails.leaseDetails.tenantDetails.leaseDetails.suiteDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.suiteDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.tenantAttributeDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.tenantIndustryDetail')
                ->findWhereIn('id', $property_group_properties_id_arr
                );

        if (Lease::get_model_as_of_date())
        {
            TenantDetailForPropertyGroups::$properties_id_arr = $property_group_properties_id_arr;
            $TenantDetailObjArr                               = $HydratedPropertyObjArr
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj
                            ->getActiveUniqueLeaseDetailObjArr()
                            ->map(
                                function (LeaseDetail $LeaseDetailObj)
                                {
                                    return $LeaseDetailObj->tenantDetailsForPropertyGroups;
                                }
                            );
                    }
                )
                ->flatten()
                ->unique('id');
        }

        $metadata = $this->get_tenant_arr_metadata($HydratedPropertyObjArr);

        return $this->sendResponse($TenantDetailObjArr->toArray(), 'TenantDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param integer $tenant_detail_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $tenant_detail_id)
    {
        /** @var TenantDetail $TenantDetailObj */
        $TenantDetailObj = $this->TenantDetailRepositoryObj->findWithoutFail($tenant_detail_id);
        if (empty($TenantDetailObj))
        {
            return Response::json(ResponseUtil::makeError('TenantDetail not found'), 404);
        }

        return $this->sendResponse($TenantDetailObj, 'TenantDetail retrieved successfully');
    }

    /**
     * Update the specified Tenant in storage.
     * PUT/PATCH /tenants/{id}
     *
     * @param integer $id
     * @param UpdateTenantRequest $TenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($client_id, $tenant_id, UpdateTenantRequest $TenantRequestObj)
    {
        $input = $TenantRequestObj->all();

        /** @var TenantIndustry $TenantDetailObj */
        $TenantDetailObj = $this->TenantDetailRepositoryObj->findWithoutFail($tenant_id);
        if (empty($TenantDetailObj))
        {
            return Response::json(ResponseUtil::makeError('Tenant not found'), 404);
        }
        if (
            count($input) !== 1 ||
            ! array_key_exists('tenant_industry_id', $input)
        )
        {
            throw new GeneralException('You may only update tenant_industry_id');
        }
        $TenantDetailObj = $this->TenantDetailRepositoryObj->update($input, $tenant_id);

        return $this->sendResponse($TenantDetailObj, 'Tenant updated successfully');
    }
}
