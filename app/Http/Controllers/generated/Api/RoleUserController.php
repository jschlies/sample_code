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

use App\Waypoint\Http\Requests\Generated\Api\CreateRoleUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateRoleUserRequest;
use App\Waypoint\Models\RoleUser;
use App\Waypoint\Repositories\RoleUserRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RoleUserController
 */
final class RoleUserController extends BaseApiController
{
    /** @var  RoleUserRepository */
    private $RoleUserRepositoryObj;

    public function __construct(RoleUserRepository $RoleUserRepositoryObj)
    {
        $this->RoleUserRepositoryObj = $RoleUserRepositoryObj;
        parent::__construct($RoleUserRepositoryObj);
    }

    /**
     * Display a listing of the RoleUser.
     * GET|HEAD /roleUsers
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->RoleUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RoleUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RoleUserObjArr = $this->RoleUserRepositoryObj->all();

        return $this->sendResponse($RoleUserObjArr, 'RoleUser(s) retrieved successfully');
    }

    /**
     * Store a newly created RoleUser in storage.
     *
     * @param CreateRoleUserRequest $RoleUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateRoleUserRequest $RoleUserRequestObj)
    {
        $input = $RoleUserRequestObj->all();

        $RoleUserObj = $this->RoleUserRepositoryObj->create($input);

        return $this->sendResponse($RoleUserObj, 'RoleUser saved successfully');
    }

    /**
     * Display the specified RoleUser.
     * GET|HEAD /roleUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var RoleUser $roleUser */
        $RoleUserObj = $this->RoleUserRepositoryObj->findWithoutFail($id);
        if (empty($RoleUserObj))
        {
            return Response::json(ResponseUtil::makeError('RoleUser not found'), 404);
        }

        return $this->sendResponse($RoleUserObj, 'RoleUser retrieved successfully');
    }

    /**
     * Update the specified RoleUser in storage.
     * PUT/PATCH /roleUsers/{id}
     *
     * @param integer $id
     * @param UpdateRoleUserRequest $RoleUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateRoleUserRequest $RoleUserRequestObj)
    {
        $input = $RoleUserRequestObj->all();
        /** @var RoleUser $RoleUserObj */
        $RoleUserObj = $this->RoleUserRepositoryObj->findWithoutFail($id);
        if (empty($RoleUserObj))
        {
            return Response::json(ResponseUtil::makeError('RoleUser not found'), 404);
        }
        $RoleUserObj = $this->RoleUserRepositoryObj->update($input, $id);

        return $this->sendResponse($RoleUserObj, 'RoleUser updated successfully');
    }

    /**
     * Remove the specified RoleUser from storage.
     * DELETE /roleUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var RoleUser $RoleUserObj */
        $RoleUserObj = $this->RoleUserRepositoryObj->findWithoutFail($id);
        if (empty($RoleUserObj))
        {
            return Response::json(ResponseUtil::makeError('RoleUser not found'), 404);
        }

        $this->RoleUserRepositoryObj->delete($id);

        return $this->sendResponse($id, 'RoleUser deleted successfully');
    }
}
