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

use App\Waypoint\Http\Requests\Generated\Api\CreateRoleRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateRoleRequest;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\RoleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RoleController
 */
final class RoleController extends BaseApiController
{
    /** @var  RoleRepository */
    private $RoleRepositoryObj;

    public function __construct(RoleRepository $RoleRepositoryObj)
    {
        $this->RoleRepositoryObj = $RoleRepositoryObj;
        parent::__construct($RoleRepositoryObj);
    }

    /**
     * Display a listing of the Role.
     * GET|HEAD /roles
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->RoleRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RoleRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RoleObjArr = $this->RoleRepositoryObj->all();

        return $this->sendResponse($RoleObjArr, 'Role(s) retrieved successfully');
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param CreateRoleRequest $RoleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateRoleRequest $RoleRequestObj)
    {
        $input = $RoleRequestObj->all();

        $RoleObj = $this->RoleRepositoryObj->create($input);

        return $this->sendResponse($RoleObj, 'Role saved successfully');
    }

    /**
     * Display the specified Role.
     * GET|HEAD /roles/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Role $role */
        $RoleObj = $this->RoleRepositoryObj->findWithoutFail($id);
        if (empty($RoleObj))
        {
            return Response::json(ResponseUtil::makeError('Role not found'), 404);
        }

        return $this->sendResponse($RoleObj, 'Role retrieved successfully');
    }

    /**
     * Update the specified Role in storage.
     * PUT/PATCH /roles/{id}
     *
     * @param integer $id
     * @param UpdateRoleRequest $RoleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateRoleRequest $RoleRequestObj)
    {
        $input = $RoleRequestObj->all();
        /** @var Role $RoleObj */
        $RoleObj = $this->RoleRepositoryObj->findWithoutFail($id);
        if (empty($RoleObj))
        {
            return Response::json(ResponseUtil::makeError('Role not found'), 404);
        }
        $RoleObj = $this->RoleRepositoryObj->update($input, $id);

        return $this->sendResponse($RoleObj, 'Role updated successfully');
    }

    /**
     * Remove the specified Role from storage.
     * DELETE /roles/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Role $RoleObj */
        $RoleObj = $this->RoleRepositoryObj->findWithoutFail($id);
        if (empty($RoleObj))
        {
            return Response::json(ResponseUtil::makeError('Role not found'), 404);
        }

        $this->RoleRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Role deleted successfully');
    }
}
