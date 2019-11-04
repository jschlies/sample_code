<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AccessListDetailRepository;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class AccessListDetailV2Controller extends BaseApiController
{
    /** @var  AccessListDetailRepository */
    private $AccessListDetailRepositoryObj;
    /** @var  UserRepository */
    private $UserRepositoryObj;

    /**
     * AccessListDetailController constructor.
     * @param AccessListDetailRepository $AccessListDetailRepository
     */
    public function __construct(AccessListDetailRepository $AccessListDetailRepository)
    {
        $this->AccessListDetailRepositoryObj = $AccessListDetailRepository;
        $this->UserRepositoryObj             = App::make(UserRepository::class);
        parent::__construct($AccessListDetailRepository);
    }

    /**
     * @param integer $client_id
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getAccessiblePropertiesForUser($client_id, $user_id)
    {
        /** @var User $UserObj */
        $UserObj =
            $this->UserRepositoryObj
                ->find($user_id);
        $key                               = 'AccessiblePropertyObjFormattedArr_user_' . $UserObj->id;
        $AccessiblePropertyObjArr = $UserObj->getPreCalcValue($key);
        if ($AccessiblePropertyObjArr === null)
        {
            $AccessiblePropertyObjArr = $UserObj->getAccessiblePropertyObjArr()->toArray();

            $UserObj->updatePreCalcValue(
                $key,
                $AccessiblePropertyObjArr
            );
        }

        return $this->sendResponse($AccessiblePropertyObjArr, 'AccessibleProperty(s) retrieved successfully');
    }
}
