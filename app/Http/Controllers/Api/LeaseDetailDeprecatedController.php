<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\UpdateLeaseRequest;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\LeaseDetailRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Illuminate\Http\JsonResponse;
use Response;

/**
 * Class LeaseController
 * @codeCoverageIgnore
 */
class LeaseDetailDeprecatedController extends BaseApiController
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /** @var  LeaseDetailRepository */
    private $LeaseDetailRepositoryObj;
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;
    /** @var  PropertyGroupRepository */
    private $PropertyGroupRepositoryObj;

    public function __construct(LeaseDetailRepository $LeaseDetailRepositoryObj)
    {
        $this->LeaseDetailRepositoryObj   = $LeaseDetailRepositoryObj;
        $this->PropertyRepositoryObj      = App::make(PropertyRepository::class);
        $this->PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        parent::__construct($LeaseDetailRepositoryObj);
        Lease::$use_as_of_date = false;
    }

    /**
     * Display the specified Lease.
     * GET|HEAD /leases/{id}
     *
     * @param integer $lease_id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $property_id, $lease_id)
    {
        /** @var Lease $lease */
        $LeaseObj = $this->LeaseDetailRepositoryObj->findWithoutFail($lease_id);
        if (empty($LeaseObj))
        {
            return Response::json(ResponseUtil::makeError('Lease not found'), 404);
        }

        return $this->sendResponse($LeaseObj, 'Lease retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_leases_for_property($client_id, $property_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyObj = $this->PropertyRepositoryObj
            ->with('leaseDetails.suiteDetails')
            ->with('leaseDetails.tenantDetails.tenantIndustryDetail')
            ->find($property_id);

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_suites_metadata(
            $PropertyObj,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        $LeaseDetailsObjArr =
            $PropertyObj->leaseDetails
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return LeaseDetail::check_model_date_range($LeaseDetailObj);
                    }
                );

        return $this->sendResponse($LeaseDetailsObjArr->toArray(), 'leaseDetails(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_active_leases_for_property($client_id, $property_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyObj = $this->PropertyRepositoryObj
            ->with('leaseDetails')
            ->find($property_id);

        $LeaseDetailsObjArr =
            $PropertyObj->leaseDetails
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return LeaseDetail::check_model_date_range($LeaseDetailObj);
                    }
                );

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_suites_metadata(
            $PropertyObj,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        return $this->sendResponse($LeaseDetailsObjArr->toArray(), 'LeaseDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param $property_group_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_leases_for_property_group($client_id, $property_group_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyGroupObj = $this->PropertyGroupRepositoryObj
            ->with('properties.leaseDetails')
            ->find($property_group_id);

        $LeaseDetailObjArr =
            $PropertyGroupObj
                ->properties
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->leaseDetails;
                    }
                )
                ->flatten()
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return LeaseDetail::check_model_date_range($LeaseDetailObj);
                    }
                );

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupObj->properties,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        $metadata = array_merge_recursive(
            $metadata,
            $this->get_tenant_arr_metadata($PropertyGroupObj->properties)
        );

        return $this->sendResponse($LeaseDetailObjArr->toArray(), 'LeaseDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_active_leases_for_property_group($client_id, $property_group_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyGroupObj = $this->PropertyGroupRepositoryObj
            ->with('properties.leases')
            ->find($property_group_id);

        $LeaseDetailObjArr =
            $PropertyGroupObj->properties
                ->map(
                    function ($PropertyObj)
                    {
                        return $PropertyObj->getActiveLeaseDetailObjArr();
                    }
                )
                ->flatten()
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return LeaseDetail::check_model_date_range($LeaseDetailObj);
                    }
                );

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupObj->properties,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        return $this->sendResponse($LeaseDetailObjArr->toArray(), 'LeaseDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_leases_for_property_suiteless($client_id, $property_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyObj       = $this->PropertyRepositoryObj
            ->with('leaseDetails')
            ->with('suites')
            ->find($property_id);
        $LeaseDetailObjArr =
            $PropertyObj->leases
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return $LeaseDetailObj->suiteDetails && $LeaseDetailObj->suiteDetails->count() == 0;
                    }
                )
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return LeaseDetail::check_model_date_range($LeaseDetailObj);
                    }
                );

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_suites_metadata(
            $PropertyObj,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        return $this->sendResponse($LeaseDetailObjArr->toArray(), 'LeaseDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param $property_group_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_leases_for_property_group_suiteless($client_id, $property_group_id, UpdateLeaseRequest $LeaseRequestObj)
    {
        Lease::set_model_from_date($LeaseRequestObj);
        Lease::set_model_to_date($LeaseRequestObj);

        $PropertyGroupObj = $this->PropertyGroupRepositoryObj
            ->with('properties.leases.suites')
            ->find($property_group_id);

        $LeaseDetailObjArr = $PropertyGroupObj->properties->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->leaseDetails
                    ->filter(
                        function (LeaseDetail $LeaseDetailObj)
                        {
                            return $LeaseDetailObj->suites->count() == 0;
                        }
                    )
                    ->filter(
                        function (LeaseDetail $LeaseDetailObj)
                        {
                            return LeaseDetail::check_model_date_range($LeaseDetailObj);
                        }
                    );
            }
        )->flatten();

        /**
         * from_date and to_date are added here
         */
        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupObj->properties,
            null,
            Lease::get_model_from_date(),
            Lease::get_model_to_date()
        );

        return $this->sendResponse($LeaseDetailObjArr->toArray(), 'LeaseDetail(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param $property_group_id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function refresh_leases_for_property($client_id, $property_id)
    {
        $LeaseDetailObjArr = $this->LeaseDetailRepositoryObj->upload_leases_for_property($property_id);
        return $this->sendResponse($LeaseDetailObjArr, 'LeaseDetail(s) updated successfully');
    }
}
