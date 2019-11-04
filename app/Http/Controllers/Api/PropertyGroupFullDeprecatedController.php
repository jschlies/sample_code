<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\PropertyGroupFull;
use App\Waypoint\Repositories\PropertyGroupFullRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupFullPublicController
 * @codeCoverageIgnore
 */
class PropertyGroupFullDeprecatedController extends BaseApiController
{
    /** @var  PropertyGroupFullRepository */
    private $PropertyGroupFullRepositoryObj;

    public function __construct(PropertyGroupFullRepository $PropertyGroupFullRepositoryObj)
    {
        $this->PropertyGroupFullRepositoryObj = $PropertyGroupFullRepositoryObj;
        parent::__construct($PropertyGroupFullRepositoryObj);
    }

    /**
     * Display a listing of the PropertyGroupFull.
     * GET|HEAD /propertyGroupsFull
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyGroupFullRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupFullRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupFullObjArr = $this->PropertyGroupFullRepositoryObj->all();
        return $this->sendResponse($PropertyGroupFullObjArr, 'PropertyGroupFull(s) retrieved successfully', [], [], []);
    }

    /**
     * Display the specified PropertyGroupFull.
     * GET|HEAD /propertyGroupsFull/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function show($id)
    {
        /** @var PropertyGroupFull $PropertyGroupFullObj */
        $PropertyGroupFullObj = $this->PropertyGroupFullRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupFullObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupFull not found'), 404);
        }
        return $this->sendResponse($PropertyGroupFullObj, 'PropertyGroupFull retrieved successfully', [], [], []);
    }
}
