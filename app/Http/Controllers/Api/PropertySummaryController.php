<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\PropertySummary;
use App\Waypoint\Repositories\PropertySummaryRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class PropertySummaryController
 */
class PropertySummaryController extends BaseApiController
{
    /** @var  PropertySummaryRepository */
    private $PropertySummaryRepositoryObj;

    public function __construct(PropertySummaryRepository $PropertySummaryRepository)
    {
        $this->PropertySummaryRepositoryObj = $PropertySummaryRepository;
        parent::__construct($PropertySummaryRepository);
    }

    /**
     * Display a listing of the Property.
     * GET|HEAD /propertiesSummary
     *
     * @param Request $Request
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request, $client_id)
    {
        $this->PropertySummaryRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertySummaryRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $PropertyObjArr = $this->PropertySummaryRepositoryObj->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($PropertyObjArr, 'PropertySummary(s) retrieved successfully');
    }

    /**
     * Display a listing of the Property.
     * GET|HEAD /propertiesSummary
     *
     * @param Request $Request
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $Request, $client_id)
    {
        $this->PropertySummaryRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertySummaryRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        $PropertyObjArr = $this->PropertySummaryRepositoryObj->findWhere(
            [['client_id', '=', $client_id]]
        );

        return $this->sendResponse($PropertyObjArr, 'PropertySummary(s) retrieved successfully');
    }

    /**
     * @param integer $property_id
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id)
    {
        /** @var PropertySummary $property */
        $propertySummary = $this->PropertySummaryRepositoryObj->find($property_id);

        return $this->sendResponse($propertySummary, 'PropertySummary retrieved successfully');
    }
}
