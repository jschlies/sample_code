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

use App\Waypoint\Http\Requests\Generated\Api\CreateNativeAccountRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountRequest;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Repositories\NativeAccountRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NativeAccountController
 */
final class NativeAccountController extends BaseApiController
{
    /** @var  NativeAccountRepository */
    private $NativeAccountRepositoryObj;

    public function __construct(NativeAccountRepository $NativeAccountRepositoryObj)
    {
        $this->NativeAccountRepositoryObj = $NativeAccountRepositoryObj;
        parent::__construct($NativeAccountRepositoryObj);
    }

    /**
     * Display a listing of the NativeAccount.
     * GET|HEAD /nativeAccounts
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NativeAccountRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeAccountRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeAccountObjArr = $this->NativeAccountRepositoryObj->all();

        return $this->sendResponse($NativeAccountObjArr, 'NativeAccount(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeAccount in storage.
     *
     * @param CreateNativeAccountRequest $NativeAccountRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNativeAccountRequest $NativeAccountRequestObj)
    {
        $input = $NativeAccountRequestObj->all();

        $NativeAccountObj = $this->NativeAccountRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountObj, 'NativeAccount saved successfully');
    }

    /**
     * Display the specified NativeAccount.
     * GET|HEAD /nativeAccounts/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NativeAccount $nativeAccount */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccount not found'), 404);
        }

        return $this->sendResponse($NativeAccountObj, 'NativeAccount retrieved successfully');
    }

    /**
     * Update the specified NativeAccount in storage.
     * PUT/PATCH /nativeAccounts/{id}
     *
     * @param integer $id
     * @param UpdateNativeAccountRequest $NativeAccountRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateNativeAccountRequest $NativeAccountRequestObj)
    {
        $input = $NativeAccountRequestObj->all();
        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccount not found'), 404);
        }
        $NativeAccountObj = $this->NativeAccountRepositoryObj->update($input, $id);

        return $this->sendResponse($NativeAccountObj, 'NativeAccount updated successfully');
    }

    /**
     * Remove the specified NativeAccount from storage.
     * DELETE /nativeAccounts/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccount not found'), 404);
        }

        $this->NativeAccountRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NativeAccount deleted successfully');
    }
}
