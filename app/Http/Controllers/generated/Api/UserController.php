<?php

namespace App\Waypoint\Http\Controllers\Api\Generated;

use App\Waypoint\Exceptions\GeneralException;
use Exception;
use Illuminate\Http\JsonResponse;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Http\Requests\Generated\Api\CreateUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateUserRequest;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class UserController
 */
final class UserController extends BaseApiController
{
    /** @var  UserRepository */
    private $UserRepositoryObj;

    public function __construct(UserRepository $UserRepositoryObj)
    {
        $this->UserRepositoryObj = $UserRepositoryObj;
        parent::__construct($UserRepositoryObj);
    }

    /**
     * Display a listing of the User.
     * GET|HEAD /users
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->UserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->UserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $UserObjArr = $this->UserRepositoryObj->all();

        return $this->sendResponse($UserObjArr, 'User(s) retrieved successfully');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateUserRequest $UserRequestObj)
    {
        $input = $UserRequestObj->all();

        $UserObj = $this->UserRepositoryObj->create($input);

        return $this->sendResponse($UserObj, 'User saved successfully');
    }

    /**
     * Display the specified User.
     * GET|HEAD /users/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var User $user */
        $UserObj = $this->UserRepositoryObj->findWithoutFail($id);
        if (empty($UserObj))
        {
            return Response::json(ResponseUtil::makeError('User not found'), 404);
        }

        return $this->sendResponse($UserObj, 'User retrieved successfully');
    }

    /**
     * Update the specified User in storage.
     * PUT/PATCH /users/{id}
     *
     * @param integer $id
     * @param UpdateUserRequest $UserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateUserRequest $UserRequestObj)
    {
        $input = $UserRequestObj->all();
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj->findWithoutFail($id);
        if (empty($UserObj))
        {
            return Response::json(ResponseUtil::makeError('User not found'), 404);
        }
        $UserObj = $this->UserRepositoryObj->update($input, $id);

        return $this->sendResponse($UserObj, 'User updated successfully');
    }

    /**
     * Remove the specified User from storage.
     * DELETE /users/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj->findWithoutFail($id);
        if (empty($UserObj))
        {
            return Response::json(ResponseUtil::makeError('User not found'), 404);
        }

        $this->UserRepositoryObj->delete($id);

        return $this->sendResponse($id, 'User deleted successfully');
    }
}
