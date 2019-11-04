<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Models\Lease;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\SuiteDetailRepository;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\UpdateSuiteRequest;
use App\Waypoint\Models\Suite;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use App;
use Response;

/**
 * Class SuiteController
 */
class SuiteController extends BaseApiController
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /** @var  SuiteDetailRepository */
    private $SuiteDetailRepositoryObj;

    public function __construct(SuiteDetailRepository $SuiteDetailRepositoryObj)
    {
        $this->SuiteDetailRepositoryObj = $SuiteDetailRepositoryObj;
        parent::__construct($SuiteDetailRepositoryObj);
    }

    /**
     * Display the specified Suite.
     * GET|HEAD /suiteDetails/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $property_id, $suite_id)
    {
        /** @var Suite $suite */
        $SuiteDetailObj = $this->SuiteDetailRepositoryObj->findWithoutFail($suite_id);
        if (empty($SuiteDetailObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteDetail(s) not found'), 404);
        }

        return $this->sendResponse($SuiteDetailObj->toArray(), 'SuiteDetail(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param UpdateSuiteRequest $SuiteRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_suites_for_property($client_id, $property_id, UpdateSuiteRequest $SuiteRequestObj)
    {
        Lease::set_model_as_of_date($SuiteRequestObj, $client_id);

        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        $PropertyObj           = $PropertyRepositoryObj
            ->with('suiteDetails.leases')
            ->with('suites.leases')
            ->with('suites')
            ->with('leases')
            ->find($property_id);

        $metadata = $this->get_property_suites_metadata(
            $PropertyObj,
            Lease::get_model_as_of_date(),
            null,
            null
        );

        return $this->sendResponse($PropertyObj->suiteDetails->toArray(), 'Suite(s) retrieved successfully', [], [], $metadata);
    }

    /**
     * @param integer $client_id
     * @param $property_group_id
     * @param UpdateSuiteRequest $SuiteRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function get_suites_for_property_group($client_id, $property_group_id, UpdateSuiteRequest $SuiteRequestObj)
    {
        Lease::set_model_as_of_date($SuiteRequestObj, $client_id);

        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        $PropertyGroupObj           = $PropertyGroupRepositoryObj
            ->with('properties.suiteDetails')
            ->with('properties.suites.leases')
            ->with('properties.suites')
            ->with('properties.leases')
            ->find($property_group_id);

        /** @var Collection $SuiteDetailObjArr */
        $SuiteDetailObjArr = $PropertyGroupObj->properties->map(
            function ($PropertyObj)
            {
                return $PropertyObj->suiteDetails;

            }
        )->flatten();

        $metadata = $this->get_property_arr_suites_metadata(
            $PropertyGroupObj->properties,
            Lease::get_model_as_of_date(),
            null,
            null
        );

        return $this->sendResponse($SuiteDetailObjArr->toArray(), 'Lease(s) retrieved successfully', [], [], $metadata);
    }
}
