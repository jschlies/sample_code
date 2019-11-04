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

use App\Waypoint\Http\Requests\Generated\Api\CreateNativeAccountTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountTypeRequest;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NativeAccountTypeController
 */
final class NativeAccountTypeController extends BaseApiController
{
    /** @var  NativeAccountTypeRepository */
    private $NativeAccountTypeRepositoryObj;

    public function __construct(NativeAccountTypeRepository $NativeAccountTypeRepositoryObj)
    {
        $this->NativeAccountTypeRepositoryObj = $NativeAccountTypeRepositoryObj;
        parent::__construct($NativeAccountTypeRepositoryObj);
    }

    /**
     * Display a listing of the NativeAccountType.
     * GET|HEAD /nativeAccountTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NativeAccountTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeAccountTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeAccountTypeObjArr = $this->NativeAccountTypeRepositoryObj->all();

        return $this->sendResponse($NativeAccountTypeObjArr, 'NativeAccountType(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeAccountType in storage.
     *
     * @param CreateNativeAccountTypeRequest $NativeAccountTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNativeAccountTypeRequest $NativeAccountTypeRequestObj)
    {
        $input = $NativeAccountTypeRequestObj->all();

        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType saved successfully');
    }

    /**
     * Display the specified NativeAccountType.
     * GET|HEAD /nativeAccountTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NativeAccountType $nativeAccountType */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }

        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType retrieved successfully');
    }

    /**
     * Update the specified NativeAccountType in storage.
     * PUT/PATCH /nativeAccountTypes/{id}
     *
     * @param integer $id
     * @param UpdateNativeAccountTypeRequest $NativeAccountTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateNativeAccountTypeRequest $NativeAccountTypeRequestObj)
    {
        $input = $NativeAccountTypeRequestObj->all();
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType updated successfully');
    }

    /**
     * Remove the specified NativeAccountType from storage.
     * DELETE /nativeAccountTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }

        $this->NativeAccountTypeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NativeAccountType deleted successfully');
    }
}
