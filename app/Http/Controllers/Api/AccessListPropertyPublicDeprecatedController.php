<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListPropertyRequest;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Repositories\AccessListPropertyRepository;
use App\Waypoint\Repositories\AccessListRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccessListPropertyController
 * @codeCoverageIgnore
 */
class AccessListPropertyPublicDeprecatedController extends BaseApiController
{
    /** @var  AccessListPropertyRepository */
    private $AccessListPropertyRepositoryObj;

    public function __construct(AccessListPropertyRepository $AccessListPropertyRepositoryObj)
    {
        $this->AccessListPropertyRepositoryObj = $AccessListPropertyRepositoryObj;
        parent::__construct($AccessListPropertyRepositoryObj);
    }

    /**
     * Display a listing of the AccessListProperty.
     * GET|HEAD /accessListProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id, $access_list_id)
    {
        $this->AccessListPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        /** @var AccessListRepository $AccessListRepository */
        $AccessListRepository = $this->AccessListPropertyRepositoryObj->makeRepository(AccessListRepository::class);
        $AccessListObj        = $AccessListRepository->find($access_list_id);

        return $this->sendResponse($AccessListObj->accessListProperties, 'AccessListProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created AccessListProperty in storage.
     *
     * @param CreateAccessListPropertyRequest $AccessListPropertyRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store(CreateAccessListPropertyRequest $AccessListPropertyRequestObj)
    {
        $input = $AccessListPropertyRequestObj->all();
        if (isset($input['access_list_id']) && isset($input['property_id']))
        {
            if ($AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWhere(
                [
                    'access_list_id' => $input['access_list_id'],
                    'property_id'    => $input['property_id'],
                ]
            )->first()
            )
            {
                return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty already exists');
            }
        }
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->create($input);

        return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty saved successfully');
    }

    /**
     * Display the specified AccessListProperty.
     * GET|HEAD /accessListProperties/{id}
     *
     * @param integer $client_id
     * @param integer $access_list_id
     * @param integer $access_list_property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $access_list_id, $access_list_property_id)
    {
        /** @var AccessListProperty $accessListProperty */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWithoutFail($access_list_property_id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }

        return $this->sendResponse($AccessListPropertyObj, 'AccessListProperty retrieved successfully');
    }

    /**
     * Remove the specified AccessListProperty from storage.
     * DELETE /accessListProperties/{id}
     *
     * @param integer $client_id
     * @param integer $access_list_id
     * @param integer $access_ist_property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $access_list_id, $access_ist_property_id)
    {
        /** @var AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->findWithoutFail($access_ist_property_id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }
        $AccessListPropertyObj->delete();

        return $this->sendResponse($access_ist_property_id, 'AccessListProperty deleted successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAudits($client_id, $access_list_property_id)
    {
        /** @var AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->AccessListPropertyRepositoryObj->find($access_list_property_id);
        if (empty($AccessListPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListProperty not found'), 404);
        }

        return $this->sendResponse($AccessListPropertyObj->getAuditArr(), 'AccessListProperty audits retrieved successfully');
    }
}
