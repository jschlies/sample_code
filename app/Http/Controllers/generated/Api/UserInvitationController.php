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

use App\Waypoint\Http\Requests\Generated\Api\CreateUserInvitationRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateUserInvitationRequest;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Repositories\UserInvitationRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class UserInvitationController
 */
final class UserInvitationController extends BaseApiController
{
    /** @var  UserInvitationRepository */
    private $UserInvitationRepositoryObj;

    public function __construct(UserInvitationRepository $UserInvitationRepositoryObj)
    {
        $this->UserInvitationRepositoryObj = $UserInvitationRepositoryObj;
        parent::__construct($UserInvitationRepositoryObj);
    }

    /**
     * Display a listing of the UserInvitation.
     * GET|HEAD /userInvitations
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->UserInvitationRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->UserInvitationRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $UserInvitationObjArr = $this->UserInvitationRepositoryObj->all();

        return $this->sendResponse($UserInvitationObjArr, 'UserInvitation(s) retrieved successfully');
    }

    /**
     * Store a newly created UserInvitation in storage.
     *
     * @param CreateUserInvitationRequest $UserInvitationRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateUserInvitationRequest $UserInvitationRequestObj)
    {
        $input = $UserInvitationRequestObj->all();

        $UserInvitationObj = $this->UserInvitationRepositoryObj->create($input);

        return $this->sendResponse($UserInvitationObj, 'UserInvitation saved successfully');
    }

    /**
     * Display the specified UserInvitation.
     * GET|HEAD /userInvitations/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var UserInvitation $userInvitation */
        $UserInvitationObj = $this->UserInvitationRepositoryObj->findWithoutFail($id);
        if (empty($UserInvitationObj))
        {
            return Response::json(ResponseUtil::makeError('UserInvitation not found'), 404);
        }

        return $this->sendResponse($UserInvitationObj, 'UserInvitation retrieved successfully');
    }

    /**
     * Update the specified UserInvitation in storage.
     * PUT/PATCH /userInvitations/{id}
     *
     * @param integer $id
     * @param UpdateUserInvitationRequest $UserInvitationRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateUserInvitationRequest $UserInvitationRequestObj)
    {
        $input = $UserInvitationRequestObj->all();
        /** @var UserInvitation $UserInvitationObj */
        $UserInvitationObj = $this->UserInvitationRepositoryObj->findWithoutFail($id);
        if (empty($UserInvitationObj))
        {
            return Response::json(ResponseUtil::makeError('UserInvitation not found'), 404);
        }
        $UserInvitationObj = $this->UserInvitationRepositoryObj->update($input, $id);

        return $this->sendResponse($UserInvitationObj, 'UserInvitation updated successfully');
    }

    /**
     * Remove the specified UserInvitation from storage.
     * DELETE /userInvitations/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var UserInvitation $UserInvitationObj */
        $UserInvitationObj = $this->UserInvitationRepositoryObj->findWithoutFail($id);
        if (empty($UserInvitationObj))
        {
            return Response::json(ResponseUtil::makeError('UserInvitation not found'), 404);
        }

        $this->UserInvitationRepositoryObj->delete($id);

        return $this->sendResponse($id, 'UserInvitation deleted successfully');
    }
}
