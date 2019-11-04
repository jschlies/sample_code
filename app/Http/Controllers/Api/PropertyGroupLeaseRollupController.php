<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupLeaseRollup;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupLeaseRollupRepository;
use App\Waypoint\Repositories\PropertyLeaseRollupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupLeaseRollupController
 */
class PropertyGroupLeaseRollupController extends BaseApiController
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /** @var  PropertyGroupLeaseRollupRepository */
    private $PropertyGroupLeaseRollupRepositoryObj;
    /** @var  PropertyLeaseRollupRepository */
    private $PropertyLeaseRollupRepositoryObj;
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;

    public function __construct(PropertyGroupLeaseRollupRepository $PropertyGroupLeaseRollupRepositoryObj)
    {
        $this->PropertyGroupLeaseRollupRepositoryObj = $PropertyGroupLeaseRollupRepositoryObj;
        $this->PropertyLeaseRollupRepositoryObj      = App::make(PropertyLeaseRollupRepository::class);
        $this->PropertyRepositoryObj                 = App::make(PropertyRepository::class);
        parent::__construct($PropertyGroupLeaseRollupRepositoryObj);
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

        $this->PropertyGroupLeaseRollupRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertyGroupLeaseRollupRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        $ClientRepositoryObj = App::make(ClientRepository::class);
        /** @var Client $ClientObj */
        $ClientObj = $ClientRepositoryObj->find($client_id);
        /** @var PropertyGroupLeaseRollup $PropertyGroupLeaseRollupObjArr */
        $PropertyGroupLeaseRollupObjArr =
            collect_waypoint(
                $this->PropertyGroupLeaseRollupRepositoryObj
                    ->with('properties.leases')
                    ->findWhereIn('id', $ClientObj->getPropertyGroupIdArr())
            );

        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupLeaseRollupObjArr->filter(
                function (PropertyGroup $PropertyGroupObj)
                {
                    return $PropertyGroupObj->properties;
                }
            )->flatten(),
            Lease::get_model_as_of_date(),
            null,
            null
        );

        $metadata['leases_date'] = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');
        return $this->sendResponse(
            $PropertyGroupLeaseRollupObjArr,
            'PropertyGroupLeaseRollup(s) retrieved successfully',
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
    public function indexForPropertyGroup(Request $Request, $client_id, $property_group_id)
    {
        Lease::set_model_as_of_date($Request, $client_id);

        /** @var PropertyGroupLeaseRollup $PropertyGroupLeaseRollupObj */
        $PropertyGroupLeaseRollupObj =
            $this->PropertyGroupLeaseRollupRepositoryObj
                ->find($property_group_id);

        if (empty($PropertyGroupLeaseRollupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupLeaseRollup not found'), 404);
        }

        $PropertyLeaseRollupObjArr =
            collect_waypoint(
                $this->PropertyLeaseRollupRepositoryObj
                    ->with('leaseDetails.suiteDetails.tenantDetails.tenantAttributeDetails')
                    ->with('leaseDetails.suiteDetails.tenantDetails.tenantIndustryDetail')
                    ->with('leaseDetails.tenantDetails.client.properties')
                    ->with('leaseDetails.tenantDetails.leaseDetails.suiteDetails')
                    ->with('leaseDetails.tenantDetails.tenantAttributeDetails')
                    ->with('leaseDetails.tenantDetails.tenantIndustryDetail')
                    ->with('suiteDetails.leaseDetails.suiteDetails')
                    ->with('suiteDetails.leaseDetails.tenantDetails.tenantAttributeDetails')
                    ->with('suiteDetails.leaseDetails.tenantDetails.tenantIndustryDetail')
                    ->with('suiteDetails.tenantDetails.client.properties')
                    ->with('suiteDetails.tenantDetails.leaseDetails.suiteDetails')
                    ->with('suiteDetails.tenantDetails.suiteDetails.tenantDetails.tenantAttributeDetails')
                    ->with('suiteDetails.tenantDetails.suiteDetails.tenantDetails.tenantIndustryDetail')
                    ->with('suiteDetails.tenantDetails.tenantAttributeDetails')
                    ->with('suiteDetails.tenantDetails.tenantIndustryDetail')
                    ->findWhereIn('id', $PropertyGroupLeaseRollupObj->propertyGroupProperties->pluck('property_id')->toArray())
            );

        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupLeaseRollupObj->properties,
            Lease::get_model_as_of_date(),
            null,
            null
        );

        $metadata['lease_as_of_date'] = Lease::get_model_as_of_date()->format('Y-m-d H:i:s');

        return $this->sendResponse(
            $PropertyLeaseRollupObjArr->toArray(),
            'PropertyGroupLeaseRollup retrieved successfully',
            [],
            [],
            $metadata
        );
    }
}
