<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Http\Request;
use App\Waypoint\Http\Requests\Generated\Api\CreateClientCategoryRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateClientCategoryRequest;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Repositories\ClientCategoryRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ClientCategoryController
 * @codeCoverageIgnore
 */
class ClientCategoryDeprecatedController extends BaseApiController
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
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ClientCategoryRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientCategoryRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        return $this->sendResponse($this->getCurrentLoggedInUserObj()->client->clientCategories, 'ClientCategory(s) retrieved successfully');
    }

    /**
     * Store a newly created ClientCategory in storage.
     *
     * @param CreateClientCategoryRequest $ClientCategoryRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store(CreateClientCategoryRequest $ClientCategoryRequestObj)
    {
        $input             = $ClientCategoryRequestObj->all();
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->create($input);

        return $this->sendResponse($ClientCategoryObj, 'ClientCategory saved successfully');
    }

    /**
     * Display the specified ClientCategory.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($id)
    {
        /** @var ClientCategory $ClientCategoryObj */
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->findWithoutFail($id);
        if (empty($ClientCategoryObj))
        {
            return Response::json(ResponseUtil::makeError('ClientCategory not found'), 404);
        }

        return $this->sendResponse($ClientCategoryObj, 'ClientCategory retrieved successfully');
    }

    /**
     * Update the specified ClientCategory in storage.
     *
     * @param integer $id
     * @param UpdateClientCategoryRequest $ClientCategoryRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
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
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($id)
    {
        /** @var ClientCategory $ClientCategoryObj */
        $ClientCategoryObj = $this->ClientCategoryRepositoryObj->findWithoutFail($id);
        if (empty($ClientCategoryObj))
        {
            return Response::json(ResponseUtil::makeError('ClientCategory not found'), 404);
        }
        $ClientCategoryObj->delete();

        return $this->sendResponse($id, 'ClientCategory deleted successfully');
    }
}
