<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use Illuminate\Auth\Access\AuthorizationException;
use App\Waypoint\Repositories\HeartbeatDetailRepository;

/**
 * Class HeartbeatController
 * @codeCoverageIgnore
 */
class HeartbeatDetailDeprecatedController extends BaseApiController
{
    /** @var  UserRepository */
    private $HeartbeatDetailRepository;

    public function __construct(HeartbeatDetailRepository $HeartbeatDetailRepository)
    {
        $this->HeartbeatDetailRepository = $HeartbeatDetailRepository;
        parent::__construct($HeartbeatDetailRepository);
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

        $HeartbeatObjArr = $this->HeartbeatDetailRepository->findHeartbeats($this->CurrentLoggedInUserObj);

        /**
         * that's right!!! array, not Collection. In this one instance, the front-end team wants this
         * to be an array, not an object as (I think almost) all routes are currently
         */
        $return_me = [];

        /** @var Heartbeat $HeartbeatObj */
        foreach ($HeartbeatObjArr as $HeartbeatObj)
        {
            $return_me[] = $HeartbeatObj->toArray();
        }
        return $this->sendResponse($return_me, 'Heartbeat retrieved successfully');
    }
}
