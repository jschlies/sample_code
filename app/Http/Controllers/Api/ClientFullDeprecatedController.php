<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Http\Request;
use App\Waypoint\Models\ClientFull;
use App\Waypoint\Repositories\ClientFullRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ClientFullController
 * @codeCoverageIgnore
 */
class ClientFullDeprecatedController extends BaseApiController
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
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function show($id)
    {
        /** @var ClientFull $ClientFullObj */
        $ClientFullObj = $this->ClientFullRepositoryObj->find($id);
        if (empty($ClientFullObj))
        {
            return Response::json(ResponseUtil::makeError('ClientFull not found'), 404);
        }

        return $this->sendResponse($ClientFullObj->toArray(), 'ClientFull retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function indexForClient($client_id)
    {
        $ClientFullObjArr = $this->ClientFullRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        if ( ! $ClientFullObjArr->count())
        {
            return Response::json(ResponseUtil::makeError('Client(s) not found'), 404);
        }

        return $this->sendResponse($ClientFullObjArr, 'ClientFull(s) retrieved successfully');
    }
}
