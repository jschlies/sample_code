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

use App\Waypoint\Http\Requests\Generated\Api\CreatePermissionRoleRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePermissionRoleRequest;
use App\Waypoint\Models\PermissionRole;
use App\Waypoint\Repositories\PermissionRoleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PermissionRoleController
 */
final class PermissionRoleController extends BaseApiController
{
    /** @var  PermissionRoleRepository */
    private $PermissionRoleRepositoryObj;

    public function __construct(PermissionRoleRepository $PermissionRoleRepositoryObj)
    {
        $this->PermissionRoleRepositoryObj = $PermissionRoleRepositoryObj;
        parent::__construct($PermissionRoleRepositoryObj);
    }

    /**
     * Display a listing of the PermissionRole.
     * GET|HEAD /permissionRoles
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PermissionRoleRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PermissionRoleRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PermissionRoleObjArr = $this->PermissionRoleRepositoryObj->all();

        return $this->sendResponse($PermissionRoleObjArr, 'PermissionRole(s) retrieved successfully');
    }

    /**
     * Store a newly created PermissionRole in storage.
     *
     * @param CreatePermissionRoleRequest $PermissionRoleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePermissionRoleRequest $PermissionRoleRequestObj)
    {
        $input = $PermissionRoleRequestObj->all();

        $PermissionRoleObj = $this->PermissionRoleRepositoryObj->create($input);

        return $this->sendResponse($PermissionRoleObj, 'PermissionRole saved successfully');
    }

    /**
     * Display the specified PermissionRole.
     * GET|HEAD /permissionRoles/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var PermissionRole $permissionRole */
        $PermissionRoleObj = $this->PermissionRoleRepositoryObj->findWithoutFail($id);
        if (empty($PermissionRoleObj))
        {
            return Response::json(ResponseUtil::makeError('PermissionRole not found'), 404);
        }

        return $this->sendResponse($PermissionRoleObj, 'PermissionRole retrieved successfully');
    }

    /**
     * Update the specified PermissionRole in storage.
     * PUT/PATCH /permissionRoles/{id}
     *
     * @param integer $id
     * @param UpdatePermissionRoleRequest $PermissionRoleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePermissionRoleRequest $PermissionRoleRequestObj)
    {
        $input = $PermissionRoleRequestObj->all();
        /** @var PermissionRole $PermissionRoleObj */
        $PermissionRoleObj = $this->PermissionRoleRepositoryObj->findWithoutFail($id);
        if (empty($PermissionRoleObj))
        {
            return Response::json(ResponseUtil::makeError('PermissionRole not found'), 404);
        }
        $PermissionRoleObj = $this->PermissionRoleRepositoryObj->update($input, $id);

        return $this->sendResponse($PermissionRoleObj, 'PermissionRole updated successfully');
    }

    /**
     * Remove the specified PermissionRole from storage.
     * DELETE /permissionRoles/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var PermissionRole $PermissionRoleObj */
        $PermissionRoleObj = $this->PermissionRoleRepositoryObj->findWithoutFail($id);
        if (empty($PermissionRoleObj))
        {
            return Response::json(ResponseUtil::makeError('PermissionRole not found'), 404);
        }

        $this->PermissionRoleRepositoryObj->delete($id);

        return $this->sendResponse($id, 'PermissionRole deleted successfully');
    }
}
