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

use App\Waypoint\Http\Requests\Generated\Api\CreateRelatedUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateRelatedUserRequest;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Repositories\RelatedUserRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RelatedUserController
 */
final class RelatedUserController extends BaseApiController
{
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;

    public function __construct(RelatedUserRepository $RelatedUserRepositoryObj)
    {
        $this->RelatedUserRepositoryObj = $RelatedUserRepositoryObj;
        parent::__construct($RelatedUserRepositoryObj);
    }

    /**
     * Display a listing of the RelatedUser.
     * GET|HEAD /relatedUsers
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserObjArr = $this->RelatedUserRepositoryObj->all();

        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * Store a newly created RelatedUser in storage.
     *
     * @param CreateRelatedUserRequest $RelatedUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateRelatedUserRequest $RelatedUserRequestObj)
    {
        $input = $RelatedUserRequestObj->all();

        $RelatedUserObj = $this->RelatedUserRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserObj, 'RelatedUser saved successfully');
    }

    /**
     * Display the specified RelatedUser.
     * GET|HEAD /relatedUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var RelatedUser $relatedUser */
        $RelatedUserObj = $this->RelatedUserRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUser not found'), 404);
        }

        return $this->sendResponse($RelatedUserObj, 'RelatedUser retrieved successfully');
    }

    /**
     * Update the specified RelatedUser in storage.
     * PUT/PATCH /relatedUsers/{id}
     *
     * @param integer $id
     * @param UpdateRelatedUserRequest $RelatedUserRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateRelatedUserRequest $RelatedUserRequestObj)
    {
        $input = $RelatedUserRequestObj->all();
        /** @var RelatedUser $RelatedUserObj */
        $RelatedUserObj = $this->RelatedUserRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUser not found'), 404);
        }
        $RelatedUserObj = $this->RelatedUserRepositoryObj->update($input, $id);

        return $this->sendResponse($RelatedUserObj, 'RelatedUser updated successfully');
    }

    /**
     * Remove the specified RelatedUser from storage.
     * DELETE /relatedUsers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var RelatedUser $RelatedUserObj */
        $RelatedUserObj = $this->RelatedUserRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUser not found'), 404);
        }

        $this->RelatedUserRepositoryObj->delete($id);

        return $this->sendResponse($id, 'RelatedUser deleted successfully');
    }
}
