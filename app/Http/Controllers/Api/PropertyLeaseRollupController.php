<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyLeaseRollup;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyLeaseRollupRepository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyLeaseRollupController
 */
class PropertyLeaseRollupController extends BaseApiController
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /** @var  PropertyLeaseRollupRepository */
    private $PropertyLeaseRollupRepositoryObj;

    public function __construct(PropertyLeaseRollupRepository $PropertyLeaseRollupRepositoryObj)
    {
        $this->PropertyLeaseRollupRepositoryObj = $PropertyLeaseRollupRepositoryObj;
        parent::__construct($PropertyLeaseRollupRepositoryObj);
    }

    /**
     * @param Request $Request
     * @param $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request, $client_id)
    {
        Lease::set_model_as_of_date($Request, $client_id);

        $this->PropertyLeaseRollupRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertyLeaseRollupRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        $PropertyLeaseRollupObjArr =
            $this->PropertyLeaseRollupRepositoryObj
                ->with('leaseDetails.suiteDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.suiteDetails.leaseDetails.suiteDetails')
                ->with('leaseDetails.tenantDetails.tenantAttributeDetails')
                ->with('leaseDetails.tenantDetails.tenantIndustryDetail')
                ->with('suiteDetails.leaseDetails.property')
                ->with('suiteDetails.leaseDetails.suiteDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.leaseDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.suiteDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.tenantAttributeDetails')
                ->with('suiteDetails.leaseDetails.tenantDetails.tenantIndustryDetail')
                ->with('suiteDetails.tenantDetails.client.properties')
                ->with('suiteDetails.tenantDetails.leaseDetails.suiteDetails')
                ->with('suiteDetails.tenantDetails.suiteDetails')
                ->with('suiteDetails.tenantDetails.tenantAttributeDetails')
                ->with('suiteDetails.tenantDetails.tenantIndustryDetail')
                ->findWhereIn(
                    'id',
                    $this->getCurrentLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()
                );

        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyLeaseRollupObjArr,
            Lease::get_model_as_of_date(),
            null,
            null
        );

        $metadata                      = array_merge_recursive($metadata, $this->get_tenant_arr_metadata($PropertyLeaseRollupObjArr));
        $metadata['leases_date']       = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');
        $metadata['leases_as_of_date'] = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');
        return $this->sendResponse(
            $PropertyLeaseRollupObjArr->toArray(),
            'PropertyLeaseRollup(s) retrieved successfully',
            [],
            [],
            $metadata
        );
    }

    /**
     * @param Request $Request
     * @param $client_id
     * @param $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function indexForProperty(Request $Request, $client_id, $property_id)
    {
        Lease::set_model_as_of_date($Request, $client_id);

        /** @var PropertyLeaseRollup $PropertyLeaseRollupObj */
        $PropertyLeaseRollupObj =
            $this->PropertyLeaseRollupRepositoryObj
                ->with('suites.leases')
                ->with('suites')
                ->with('leases')
                ->find($property_id);
        if (empty($PropertyLeaseRollupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyLeaseRollup not found'), 404);
        }

        $metadata = $this->get_property_suites_metadata(
            $PropertyLeaseRollupObj,
            Lease::get_model_as_of_date(),
            null,
            null
        );

        $metadata['leases_date']       = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');
        $metadata['leases_as_of_date'] = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');
        return $this->sendResponse(
            $PropertyLeaseRollupObj->toArray(),
            'PropertyLeaseRollup retrieved successfully',
            [],
            [],
            $metadata
        );
    }

    /**
     * @param Request $Request
     * @param integer $client_id
     * @param integer $property_group_id
     * @param null|string $lease_as_of_date
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function indexForPropertyGroup(Request $Request, $client_id, $property_group_id, $lease_as_of_date = null)
    {
        Lease::set_model_as_of_date($Request, $client_id);

        /** @var PropertyGroupRepository $PropertyGroupRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);

        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj =
            $PropertyGroupRepositoryObj
                ->with('properties.suites.leases')
                ->with('properties.suites')
                ->with('properties.leases')
                ->findWithoutFail($property_group_id);

        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupFull not found'), 404);
        }

        $PropertyLeaseRollupObjArr =
            $this->PropertyLeaseRollupRepositoryObj
                ->findWhereIn(
                    'id',
                    $PropertyGroupObj
                        ->properties
                        ->pluck('id')
                        ->toArray()
                );

        $metadata                = $this->get_property_arr_suites_metadata(
            $PropertyLeaseRollupObjArr,
            Lease::get_model_as_of_date(),
            null,
            null
        );
        $metadata['leases_date'] = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');

        return $this->sendResponse(
            $PropertyLeaseRollupObjArr->toArray(),
            'PropertyLeaseRollup(s) retrieved successfully',
            [],
            [],
            $metadata
        );
    }
}
