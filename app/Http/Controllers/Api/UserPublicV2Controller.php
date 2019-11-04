<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use BadMethodCallException;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use Prettus\Validator\Exceptions\ValidatorException;

class UserPublicV2Controller extends BaseApiController
{
    use Notifiable;

    /**
     * @var boolean
     */
    protected $controller_allow_cacheing = false;

    /** @var  UserRepository */
    private $UserRepositoryObj;

    public function __construct(UserRepository $UserRepositoryObj)
    {
        $this->UserRepositoryObj     = $UserRepositoryObj;
        parent::__construct($UserRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws BadMethodCallException
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function showAccessibleGroups($client_id, $user_id)
    {
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj
            ->find($user_id);

        $key                            = 'accessiblePropertyGroups_user_' . $UserObj->id;
        $AccessiblePropertyGroupsObjArr = $UserObj->getPreCalcValue($key);
        if ($AccessiblePropertyGroupsObjArr === null)
        {
            $AccessiblePropertyGroupsObjArr = $UserObj->getAccessiblePropertyGroupObjArr()->toArray();
            $UserObj->updatePreCalcValue(
                $key,
                $AccessiblePropertyGroupsObjArr
            );
        }

        /**
         * @todo please doc why array and not simply return $AccessiblePropertyGroupObjArr
         */
        $return_me = [
            "accessiblePropertyGroups" => $AccessiblePropertyGroupsObjArr,
        ];

        return $this->sendResponse($return_me, 'User accessible property group(s) retrieved successfully');
    }
}
