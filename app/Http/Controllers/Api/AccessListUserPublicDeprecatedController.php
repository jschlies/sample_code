<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListUserRequest;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\AccessListUserRepository;
use function explode;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccessListUserController
 * @codeCoverageIgnore
 */
class AccessListUserPublicDeprecatedController extends BaseApiController
{
    /** @var  AccessListUserRepository */
    private $AccessListUserRepositoryObj;

    public function __construct(AccessListUserRepository $AccessListUserRepositoryObj)
    {
        $this->AccessListUserRepositoryObj = $AccessListUserRepositoryObj;
        parent::__construct($AccessListUserRepositoryObj);
    }

    /**
     * Display a listing of the AccessListUser.
     * GET|HEAD /accessListProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id, $access_list_id)
    {
        $this->AccessListUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $AccessListUserObjArr = $this->AccessListUserRepositoryObj->findWhere(['access_list_id' => $access_list_id]);
        }
        else
        {
            $AccessListUserObjArr = $this->AccessListUserRepositoryObj->findWhere(['access_list_id' => $access_list_id])->filter(
                function (AccessListUser $AccessListUserObj)
                {
                    return ! $AccessListUserObj->user->is_hidden;
                }
            );
        }

        return $this->sendResponse($AccessListUserObjArr, 'AccessListUser(s) retrieved successfully');
    }

    /**
     * Store a newly created AccessListUser in storage.
     *
     * @param CreateAccessListUserRequest $AccessListUserRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store($client_id, $access_list_id, CreateAccessListUserRequest $AccessListUserRequestObj)
    {
        $input = $AccessListUserRequestObj->all();

        if ( ! is_array($access_list_id))
        {
            $input['access_list_id'] = explode(',', $access_list_id);
        }
        if ( ! is_array($input['user_id']))
        {
            $input['user_id'] = explode(',', $input['user_id']);
        }

        /**
         * allows multiple user_id's
         */
        $AccessListUserObjArr = new Collection();

        /**
         * because $input['access_list_id'] and $input['user_id']
         * are arrays
         */
        $inner_input = $input;
        foreach ($input['access_list_id'] as $access_list_id)
        {
            $inner_input['access_list_id'] = $access_list_id;
            foreach ($input['user_id'] as $user_id)
            {
                $inner_input['user_id'] = $user_id;
                if ($AccessListUserObj = $this->AccessListUserRepositoryObj->findWhere(
                    [
                        'access_list_id' => $inner_input['access_list_id'],
                        'user_id'        => $inner_input['user_id'],
                    ]
                )->first()
                )
                {
                    $AccessListUserObjArr[] = $AccessListUserObj;
                    continue;
                }

                $AccessListUserObjArr[] = $this->AccessListUserRepositoryObj->create($inner_input);
            }
        }

        if ($AccessListUserObjArr->count() == 1)
        {
            return $this->sendResponse($AccessListUserObjArr->first(), 'AccessListUser saved successfully');
        }
        return $this->sendResponse($AccessListUserObjArr, 'AccessListUser saved successfully');
    }

    /**
     * Display the specified AccessListUser.
     * GET|HEAD /accessListUsers/{id}
     *
     * @param integer $client_id
     * @param integer $access_list_id
     * @param integer $access_list_user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $access_list_id, $access_list_user_id)
    {
        /** @var AccessListUser $AccessListUser */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->findWithoutFail($access_list_user_id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }

        return $this->sendResponse($AccessListUserObj, 'AccessListUser retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_id
     * @param integer $access_list_user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $access_list_id, $access_list_user_id)
    {
        /** @var AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->findWithoutFail($access_list_user_id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }
        $AccessListUserObj->delete();

        return $this->sendResponse($access_list_user_id, 'AccessListUser deleted successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showAudits($client_id, $access_list_user_id)
    {
        /** @var AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->find($access_list_user_id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }

        return $this->sendResponse($AccessListUserObj->getAuditArr(), 'AccessListUser audits retrieved successfully');
    }
}
