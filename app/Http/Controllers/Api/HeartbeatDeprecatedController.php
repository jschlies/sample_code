<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use Illuminate\Auth\Access\AuthorizationException;
use App\Waypoint\Repositories\HeartbeatRepository;

/**
 * Class HeartbeatController
 * @codeCoverageIgnore
 */
class HeartbeatDeprecatedController extends BaseApiController
{
    /** @var  UserRepository */
    private $HeartbeatRepositoryObj;

    public function __construct(HeartbeatRepository $HeartbeatRepositoryObj)
    {
        $this->HeartbeatRepositoryObj = $HeartbeatRepositoryObj;
        parent::__construct($HeartbeatRepositoryObj);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null
     * @throws AuthorizationException
     * @throws GeneralException
     * @throws \BadMethodCallException
     *
     * @todo non-standard route - fix me
     */
    public function index()
    {
        if ( ! $this->CurrentLoggedInUserObj)
        {
            throw new AuthorizationException();
        }

        $HeartbeatObjArr = $this->HeartbeatRepositoryObj->findHeartbeats($this->CurrentLoggedInUserObj);

        /**
         * that's right!!! array, not Collection. In this one instance, the front-end team wants this
         * to be an array, not an object as (I think almost) all routes are currently
         */
        $HeartbeatObjIndexedArr = [];

        /** @var Heartbeat $HeartbeatObj */
        foreach ($HeartbeatObjArr as $HeartbeatObj)
        {
            $HeartbeatObjIndexedArr[] = $HeartbeatObj->toArray();
        }
        return $this->sendResponse($HeartbeatObjIndexedArr, 'Heartbeat(s) retrieved successfully');
    }
}
