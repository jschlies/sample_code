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

use App\Waypoint\Http\Requests\Generated\Api\CreateNativeAccountAmountRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountAmountRequest;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NativeAccountAmountController
 */
final class NativeAccountAmountController extends BaseApiController
{
    /** @var  NativeAccountAmountRepository */
    private $NativeAccountAmountRepositoryObj;

    public function __construct(NativeAccountAmountRepository $NativeAccountAmountRepositoryObj)
    {
        $this->NativeAccountAmountRepositoryObj = $NativeAccountAmountRepositoryObj;
        parent::__construct($NativeAccountAmountRepositoryObj);
    }

    /**
     * Display a listing of the NativeAccountAmount.
     * GET|HEAD /nativeAccountAmounts
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NativeAccountAmountRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeAccountAmountRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeAccountAmountObjArr = $this->NativeAccountAmountRepositoryObj->all();

        return $this->sendResponse($NativeAccountAmountObjArr, 'NativeAccountAmount(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeAccountAmount in storage.
     *
     * @param CreateNativeAccountAmountRequest $NativeAccountAmountRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNativeAccountAmountRequest $NativeAccountAmountRequestObj)
    {
        $input = $NativeAccountAmountRequestObj->all();

        $NativeAccountAmountObj = $this->NativeAccountAmountRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountAmountObj, 'NativeAccountAmount saved successfully');
    }

    /**
     * Display the specified NativeAccountAmount.
     * GET|HEAD /nativeAccountAmounts/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NativeAccountAmount $nativeAccountAmount */
        $NativeAccountAmountObj = $this->NativeAccountAmountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountAmountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountAmount not found'), 404);
        }

        return $this->sendResponse($NativeAccountAmountObj, 'NativeAccountAmount retrieved successfully');
    }

    /**
     * Update the specified NativeAccountAmount in storage.
     * PUT/PATCH /nativeAccountAmounts/{id}
     *
     * @param integer $id
     * @param UpdateNativeAccountAmountRequest $NativeAccountAmountRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateNativeAccountAmountRequest $NativeAccountAmountRequestObj)
    {
        $input = $NativeAccountAmountRequestObj->all();
        /** @var NativeAccountAmount $NativeAccountAmountObj */
        $NativeAccountAmountObj = $this->NativeAccountAmountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountAmountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountAmount not found'), 404);
        }
        $NativeAccountAmountObj = $this->NativeAccountAmountRepositoryObj->update($input, $id);

        return $this->sendResponse($NativeAccountAmountObj, 'NativeAccountAmount updated successfully');
    }

    /**
     * Remove the specified NativeAccountAmount from storage.
     * DELETE /nativeAccountAmounts/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NativeAccountAmount $NativeAccountAmountObj */
        $NativeAccountAmountObj = $this->NativeAccountAmountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountAmountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountAmount not found'), 404);
        }

        $this->NativeAccountAmountRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NativeAccountAmount deleted successfully');
    }
}
