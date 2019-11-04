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

use App\Waypoint\Http\Requests\Generated\Api\CreateRelatedUserTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateRelatedUserTypeRequest;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RelatedUserTypeController
 */
final class RelatedUserTypeController extends BaseApiController
{
    /** @var  RelatedUserTypeRepository */
    private $RelatedUserTypeRepositoryObj;

    public function __construct(RelatedUserTypeRepository $RelatedUserTypeRepositoryObj)
    {
        $this->RelatedUserTypeRepositoryObj = $RelatedUserTypeRepositoryObj;
        parent::__construct($RelatedUserTypeRepositoryObj);
    }

    /**
     * Display a listing of the RelatedUserType.
     * GET|HEAD /relatedUserTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserTypeObjArr = $this->RelatedUserTypeRepositoryObj->all();

        return $this->sendResponse($RelatedUserTypeObjArr, 'RelatedUserType(s) retrieved successfully');
    }

    /**
     * Store a newly created RelatedUserType in storage.
     *
     * @param CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj)
    {
        $input = $RelatedUserTypeRequestObj->all();

        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType saved successfully');
    }

    /**
     * Display the specified RelatedUserType.
     * GET|HEAD /relatedUserTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var RelatedUserType $relatedUserType */
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserTypeObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUserType not found'), 404);
        }

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType retrieved successfully');
    }

    /**
     * Update the specified RelatedUserType in storage.
     * PUT/PATCH /relatedUserTypes/{id}
     *
     * @param integer $id
     * @param UpdateRelatedUserTypeRequest $RelatedUserTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateRelatedUserTypeRequest $RelatedUserTypeRequestObj)
    {
        $input = $RelatedUserTypeRequestObj->all();
        /** @var RelatedUserType $RelatedUserTypeObj */
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserTypeObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUserType not found'), 404);
        }
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType updated successfully');
    }

    /**
     * Remove the specified RelatedUserType from storage.
     * DELETE /relatedUserTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var RelatedUserType $RelatedUserTypeObj */
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->findWithoutFail($id);
        if (empty($RelatedUserTypeObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUserType not found'), 404);
        }

        $this->RelatedUserTypeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'RelatedUserType deleted successfully');
    }
}
