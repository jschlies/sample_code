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

use App\Waypoint\Http\Requests\Generated\Api\CreatePermissionRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePermissionRequest;
use App\Waypoint\Models\Permission;
use App\Waypoint\Repositories\PermissionRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PermissionController
 */
final class PermissionController extends BaseApiController
{
    /** @var  PermissionRepository */
    private $PermissionRepositoryObj;

    public function __construct(PermissionRepository $PermissionRepositoryObj)
    {
        $this->PermissionRepositoryObj = $PermissionRepositoryObj;
        parent::__construct($PermissionRepositoryObj);
    }

    /**
     * Display a listing of the Permission.
     * GET|HEAD /permissions
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PermissionRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PermissionRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PermissionObjArr = $this->PermissionRepositoryObj->all();

        return $this->sendResponse($PermissionObjArr, 'Permission(s) retrieved successfully');
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param CreatePermissionRequest $PermissionRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePermissionRequest $PermissionRequestObj)
    {
        $input = $PermissionRequestObj->all();

        $PermissionObj = $this->PermissionRepositoryObj->create($input);

        return $this->sendResponse($PermissionObj, 'Permission saved successfully');
    }

    /**
     * Display the specified Permission.
     * GET|HEAD /permissions/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Permission $permission */
        $PermissionObj = $this->PermissionRepositoryObj->findWithoutFail($id);
        if (empty($PermissionObj))
        {
            return Response::json(ResponseUtil::makeError('Permission not found'), 404);
        }

        return $this->sendResponse($PermissionObj, 'Permission retrieved successfully');
    }

    /**
     * Update the specified Permission in storage.
     * PUT/PATCH /permissions/{id}
     *
     * @param integer $id
     * @param UpdatePermissionRequest $PermissionRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePermissionRequest $PermissionRequestObj)
    {
        $input = $PermissionRequestObj->all();
        /** @var Permission $PermissionObj */
        $PermissionObj = $this->PermissionRepositoryObj->findWithoutFail($id);
        if (empty($PermissionObj))
        {
            return Response::json(ResponseUtil::makeError('Permission not found'), 404);
        }
        $PermissionObj = $this->PermissionRepositoryObj->update($input, $id);

        return $this->sendResponse($PermissionObj, 'Permission updated successfully');
    }

    /**
     * Remove the specified Permission from storage.
     * DELETE /permissions/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Permission $PermissionObj */
        $PermissionObj = $this->PermissionRepositoryObj->findWithoutFail($id);
        if (empty($PermissionObj))
        {
            return Response::json(ResponseUtil::makeError('Permission not found'), 404);
        }

        $this->PermissionRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Permission deleted successfully');
    }
}
