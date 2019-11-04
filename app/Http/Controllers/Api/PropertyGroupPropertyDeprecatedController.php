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
 * @codeCoverageIgnore
 */
class PropertyGroupPropertyDeprecatedController extends BaseApiController
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
     * @param \App\Waypoint\Http\Requests\Generated\Api\CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store(CreatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj)
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
     * Display the specified PropertyGroupProperty.
     * GET|HEAD /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($id)
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
     * Update the specified PropertyGroupProperty in storage.
     * PUT/PATCH /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @param \App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function update($id, UpdatePropertyGroupPropertyRequest $PropertyGroupPropertyRequestObj)
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
     * Remove the specified PropertyGroupProperty from storage.
     * DELETE /propertyGroupProperties/{id}
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($id)
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
     * Remove the PropertyGroupProperty between this Property and that Group
     * DELETE /propertyGroupProperties/
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroyByComponents(Request $request)
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
