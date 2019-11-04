<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyGroupPropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyGroupPropertyRequest;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupPropertyController
 */
class PropertyGroupPropertyController extends BaseApiController
{
    /** @var  PropertyGroupPropertyRepository */
    private $PropertyGroupPropertyRepositoryObj;

    public function __construct(PropertyGroupPropertyRepository $PropertyGroupPropertyRepositoryObj)
    {
        $this->PropertyGroupPropertyRepositoryObj = $PropertyGroupPropertyRepositoryObj;
        parent::__construct($PropertyGroupPropertyRepositoryObj);
    }

    /**
     * Display a listing of the PropertyGroupProperty.
     * GET|HEAD /propertyGroupProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyGroupPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupPropertyObjArr = $this->PropertyGroupPropertyRepositoryObj->all();

        return $this->sendResponse($PropertyGroupPropertyObjArr, 'PropertyGroupProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created PropertyGroupProperty in storage.
     *
     * @param CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj, $client_id)
    {
        $input = $PropertyGroupPropertyRequestObj->all();
        if (isset($input['property_group_id']) && isset($input['property_id']))
        {
            if ($PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWhere(
                [
                    'property_group_id' => $input['property_group_id'],
                    'property_id'       => $input['property_id'],
                ]
            )->first()
            )
            {
                return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty already exists');
            }
        }
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->create($input);

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty saved successfully');
    }

    /**
     * @param integer $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $id)
    {
        /** @var PropertyGroupProperty $propertyGroupProperty */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $id
     * @param UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update($client_id, $id, UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj)
    {
        $input = $PropertyGroupPropertyRequestObj->all();
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyGroupPropertyObj, 'PropertyGroupProperty updated successfully');
    }

    /**
     * @param integer $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $id)
    {
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->PropertyGroupPropertyRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }
        $PropertyGroupPropertyObj->delete();

        return $this->sendResponse($id, 'PropertyGroupProperty deleted successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroyByComponents(Request $request, $client_id)
    {
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $property_id                        = $request->input('property_id');
        $property_group_id                  = $request->input('property_group_id');
        $PropertyGroupPropertyRepositoryObj = App::make(PropertyGroupPropertyRepository::class);
        if ( ! $property_id || ! $property_group_id || ! is_numeric($property_id) || ! is_numeric($property_group_id))
        {
            return Response::json(ResponseUtil::makeError('Invalid Property ID or Property Group ID'), 400);
        }

        $PropertyGroupPropertyObj = $PropertyGroupPropertyRepositoryObj->findWhere(
            [
                'property_id'       => $property_id,
                'property_group_id' => $property_group_id,
            ]
        )->first();

        if ( ! $PropertyGroupPropertyObj)
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupProperty not found'), 404);
        }

        $PropertyGroupPropertyRepositoryObj->delete($PropertyGroupPropertyObj->id);
        return $this->sendResponse(
            [
                'property_id'                        => intval($property_id),
                'property_group_id'                  => intval($property_group_id),
                'deleted_property_group_property_id' => $PropertyGroupPropertyObj->id,
            ],
            'PropertyGroupProperty deleted successfully'
        );
    }
}
