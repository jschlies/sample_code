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

use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAccessListUserRequest;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Repositories\AccessListUserRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccessListUserController
 */
final class AccessListUserController extends BaseApiController
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
     * GET|HEAD /accessListUsers
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AccessListUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AccessListUserObjArr = $this->AccessListUserRepositoryObj->all();

        return $this->sendResponse($AccessListUserObjArr, 'AccessListUser(s) retrieved successfully');
    }

    /**
     * Store a newly created AccessListUser in storage.
     *
     * @param CreateAccessListUserRequest $AccessListUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAccessListUserRequest $AccessListUserRequestObj)
    {
        $input = $AccessListUserRequestObj->all();

        $AccessListUserObj = $this->AccessListUserRepositoryObj->create($input);

        return $this->sendResponse($AccessListUserObj, 'AccessListUser saved successfully');
    }

    /**
     * Display the specified AccessListUser.
     * GET|HEAD /accessListUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AccessListUser $accessListUser */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->findWithoutFail($id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }

        return $this->sendResponse($AccessListUserObj, 'AccessListUser retrieved successfully');
    }

    /**
     * Update the specified AccessListUser in storage.
     * PUT/PATCH /accessListUsers/{id}
     *
     * @param integer $id
     * @param UpdateAccessListUserRequest $AccessListUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAccessListUserRequest $AccessListUserRequestObj)
    {
        $input = $AccessListUserRequestObj->all();
        /** @var AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->findWithoutFail($id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }
        $AccessListUserObj = $this->AccessListUserRepositoryObj->update($input, $id);

        return $this->sendResponse($AccessListUserObj, 'AccessListUser updated successfully');
    }

    /**
     * Remove the specified AccessListUser from storage.
     * DELETE /accessListUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->AccessListUserRepositoryObj->findWithoutFail($id);
        if (empty($AccessListUserObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListUser not found'), 404);
        }

        $this->AccessListUserRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AccessListUser deleted successfully');
    }
}
