<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Api\CreatePropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyRequest;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\PropertyRepository;
use BadMethodCallException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class PropertyPublicController
 */
class PropertyPublicController extends BaseApiController
{
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;

    public function __construct(PropertyRepository $PropertyRepositoryObj)
    {
        $this->PropertyRepositoryObj = $PropertyRepositoryObj;
        parent::__construct($PropertyRepositoryObj);
    }

    /**
     * Display a listing of the Property.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->PropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyObjArr = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        if ( ! $PropertyObjArr->count())
        {
            return Response::json(ResponseUtil::makeError('Property(s) not found for client'), 400);
        }

        return $this->sendResponse($PropertyObjArr, 'Property(s) retrieved successfully', [], [], []);
    }

    /**
     * Store a newly created Opportunity in storage.
     *
     * @param CreatePropertyRequest $PropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws BadMethodCallException
     * @throws ValidatorException
     */
    public function store(CreatePropertyRequest $PropertyRequestObj)
    {
        $input       = $PropertyRequestObj->all();
        $PropertyObj = $this->PropertyRepositoryObj->create($input);

        return $this->sendResponse($PropertyObj->toArray(), 'Property saved successfully');
    }

    /**
     * Display the specified Property.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($id)
    {
        /** @var Property $property */
        $PropertyObj = $this->PropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyObj))
        {
            return Response::json(ResponseUtil::makeError('Property not found'), 404);
        }
        return $this->sendResponse($PropertyObj, 'Property retrieved successfully', [], [], []);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param UpdatePropertyRequest $PropertyRequestObj
     * @return JsonResponse|null
     * @throws ValidatorException
     */
    public function update($client_id, $property_id, UpdatePropertyRequest $PropertyRequestObj)
    {
        $input = $PropertyRequestObj->all();
        $PropertyObj = $this->PropertyRepositoryObj->update($input, $property_id);
        return $this->sendResponse($PropertyObj, 'Property updated successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws \Exception
     */
    public function destroy($client_id, $property_id)
    {
        /** @var Property $PropertyObj */
        $PropertyObj = $this->PropertyRepositoryObj->findWithoutFail($property_id);
        $PropertyObj->delete();

        return $this->sendResponse($property_id, 'Property deleted successfully');
    }
}
