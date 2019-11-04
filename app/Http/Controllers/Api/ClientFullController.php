<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ClientFull;
use App\Waypoint\Repositories\ClientFullRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Request;
use Response;

/**
 * Class ClientFullController
 */
class ClientFullController extends BaseApiController
{
    /** @var  ClientFullRepository */
    private $ClientFullRepositoryObj;

    public function __construct(ClientFullRepository $ClientFullRepository)
    {
        $this->ClientFullRepositoryObj = $ClientFullRepository;
        parent::__construct($this->ClientFullRepositoryObj);
    }

    /**
     * Display a listing of the ClientFull.
     * GET|HEAD /clientFulls
     *
     * @param \Illuminate\Http\Request $Request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request)
    {
        $this->ClientFullRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->ClientFullRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $ClientFullObjArr = $this->ClientFullRepositoryObj->all();

        return $this->sendResponse($ClientFullObjArr, 'ClientFull(s) retrieved successfully');
    }

    /**
     * Display the specified ClientFull.
     * GET|HEAD /clientFulls/{id}
     *
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function show($client_id)
    {
        /** @var ClientFull $ClientFullObj */
        $ClientFullObj = $this->ClientFullRepositoryObj->find($client_id);
        if (empty($ClientFullObj))
        {
            return Response::json(ResponseUtil::makeError('ClientFull not found'), 404);
        }

        return $this->sendResponse($ClientFullObj, 'ClientFull retrieved successfully');
    }
}
