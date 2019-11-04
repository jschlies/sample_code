<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Repositories\AccessListSlimRepository;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

class AccessListSlimController extends BaseApiController
{
    /** @var  AccessListSlimRepository */
    private $AccessListSlimRepositoryObj;

    /**
     * AccessListSlimController constructor.
     * @param AccessListSlimRepository $AccessListSlimRepository
     */
    public function __construct(AccessListSlimRepository $AccessListSlimRepository)
    {
        $this->AccessListSlimRepositoryObj = $AccessListSlimRepository;
        $this->UserRepositoryObj           = App::make(UserRepository::class);
        parent::__construct($AccessListSlimRepository);
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getAccessListSlimForClient(Request $request, $client_id)
    {
        $this->AccessListSlimRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->AccessListSlimRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $AccessListSlimObjArr =
            $this->AccessListSlimRepositoryObj
                ->findWhere(
                    [
                        'client_id' => $client_id,
                    ]
                );
        return $this->sendResponse($AccessListSlimObjArr->toArray(), 'AccessListSlim(s) retrieved successfully');
    }
}
