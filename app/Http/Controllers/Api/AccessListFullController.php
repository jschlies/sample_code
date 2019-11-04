<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessListFull;
use App\Waypoint\Repositories\AccessListFullRepository;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class AccessListFullController
 * @package App\Waypoint\Http\Controllers\ApiRequest
 */
class AccessListFullController extends BaseApiController
{
    /** @var  AccessListFullRepository */
    private $AccessListFullRepositoryObj;

    public function __construct(AccessListFullRepository $AccessListFullRepository)
    {
        $this->AccessListFullRepositoryObj = $AccessListFullRepository;
        parent::__construct($AccessListFullRepository);
    }

    /**
     * @param $client_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request, $client_id)
    {
        $this->AccessListFullRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->AccessListFullRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $AccessListFullObjArr = $this->AccessListFullRepositoryObj
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($AccessListFullObjArr, 'AccessListsFull(s) retrieved successfully');
    }

    /**
     * @param $client_id
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function show($client_id, $id)
    {
        /** @var AccessListFull $accessList */
        $AccessListFullObj = $this->AccessListFullRepositoryObj->findWithoutFail($id);
        if (empty($AccessListFullObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListFull not found'), 404);
        }

        return $this->sendResponse($AccessListFullObj, 'AccessListFull retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function getAccessListFullForClient($client_id)
    {
        $AccessListFullObjArr = $this->AccessListFullRepositoryObj->findWhere([['client_id', '=', $client_id]]);
        return $this->sendResponse($AccessListFullObjArr, 'AccessListsFull(s) retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getAccessListFullForUser($client_id, $user_id)
    {
        $AccessListFullObjArr = $this->AccessListFullRepositoryObj->findWhere([['user_id', '=', $user_id]]);
        return $this->sendResponse($AccessListFullObjArr, 'AccessListsFull(s) retrieved successfully');
    }
}
