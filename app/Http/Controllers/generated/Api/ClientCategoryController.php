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

use App\Waypoint\Http\Requests\Generated\Api\CreateClientCategoryRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateClientCategoryRequest;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Repositories\ClientCategoryRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ClientCategoryController
 */
final class ClientCategoryController extends BaseApiController
{
    /** @var  ClientCategoryRepository */
    private $ClientCategoryRepositoryObj;

    public function __construct(ClientCategoryRepository $ClientCategoryRepositoryObj)
    {
        $this->ClientCategoryRepositoryObj = $ClientCategoryRepositoryObj;
        parent::__construct($ClientCategoryRepositoryObj);
    }

    /**
     * Display a listing of the ClientCategory.
     * GET|HEAD /clientCategories
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ClientCategoryRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientCategoryRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ClientCategoryObjArr = $this->ClientCategoryRepositoryObj->all();

        return $this->sendResponse($ClientCategoryObjArr, 'ClientCategory(s) retrieved successfully');
    }

    /**
     * Store a newly created ClientCategory in storage.
     *
     * @param CreateClientCategoryRequest $ClientCategoryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateClientCategoryRequest $ClientCategoryRequestObj)
    {
        $input = $ClientCategoryRequestObj->all();

        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->create($input);

        return $this->sendResponse($ClientCategoryObj, 'ClientCategory saved successfully');
    }

    /**
     * Display the specified ClientCategory.
     * GET|HEAD /clientCategories/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ClientCategory $clientCategory */
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->findWithoutFail($id);
        if (empty($ClientCategoryObj))
        {
            return Response::json(ResponseUtil::makeError('ClientCategory not found'), 404);
        }

        return $this->sendResponse($ClientCategoryObj, 'ClientCategory retrieved successfully');
    }

    /**
     * Update the specified ClientCategory in storage.
     * PUT/PATCH /clientCategories/{id}
     *
     * @param integer $id
     * @param UpdateClientCategoryRequest $ClientCategoryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateClientCategoryRequest $ClientCategoryRequestObj)
    {
        $input = $ClientCategoryRequestObj->all();
        /** @var ClientCategory $ClientCategoryObj */
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->findWithoutFail($id);
        if (empty($ClientCategoryObj))
        {
            return Response::json(ResponseUtil::makeError('ClientCategory not found'), 404);
        }
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->update($input, $id);

        return $this->sendResponse($ClientCategoryObj, 'ClientCategory updated successfully');
    }

    /**
     * Remove the specified ClientCategory from storage.
     * DELETE /clientCategories/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ClientCategory $ClientCategoryObj */
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->findWithoutFail($id);
        if (empty($ClientCategoryObj))
        {
            return Response::json(ResponseUtil::makeError('ClientCategory not found'), 404);
        }

        $this->ClientCategoryRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ClientCategory deleted successfully');
    }
}
