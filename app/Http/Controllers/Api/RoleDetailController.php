<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\RoleDetailRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class RoleDetailController
 */
class RoleDetailController extends BaseApiController
{
    /** @var  RoleDetailRepository */
    private $RoleDetailRepositoryObj;

    public function __construct(RoleDetailRepository $RoleDetailRepository)
    {
        $this->RoleDetailRepositoryObj = $RoleDetailRepository;
        parent::__construct($RoleDetailRepository);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index($client_id, Request $request)
    {
        $this->RoleDetailRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->RoleDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));

        $RoleObjArr = $this->RoleDetailRepositoryObj->all();
        return $this->sendResponse(collect_waypoint($RoleObjArr), 'System RoleDetail(s) retrieved successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function getAvailable($client_id)
    {
        $RoleObjArr = $this->RoleDetailRepositoryObj->all();
        return $this->sendResponse(collect_waypoint($RoleObjArr), 'System Role(s) Available');
    }
}
