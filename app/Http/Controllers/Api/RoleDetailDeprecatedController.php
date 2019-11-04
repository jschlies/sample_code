<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\RoleDetail;
use App\Waypoint\Repositories\RoleDetailRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class RoleDetailDeprecatedController
 * @codeCoverageIgnore
 */
class RoleDetailDeprecatedController extends BaseApiController
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
    public function index(Request $request)
    {
        $this->RoleDetailRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->RoleDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));

        $RoleObjArr = $this->RoleDetailRepositoryObj->all();
        return $this->sendResponse($RoleObjArr, 'RoleDetail(s) retrieved successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function getAvailable()
    {
        return $this->sendResponse(RoleDetail::$available_role_values, 'Role(s) Available');
    }
}
