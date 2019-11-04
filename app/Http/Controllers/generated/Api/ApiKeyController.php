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

use App\Waypoint\Http\Requests\Generated\Api\CreateApiKeyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateApiKeyRequest;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Repositories\ApiKeyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ApiKeyController
 */
final class ApiKeyController extends BaseApiController
{
    /** @var  ApiKeyRepository */
    private $ApiKeyRepositoryObj;

    public function __construct(ApiKeyRepository $ApiKeyRepositoryObj)
    {
        $this->ApiKeyRepositoryObj = $ApiKeyRepositoryObj;
        parent::__construct($ApiKeyRepositoryObj);
    }

    /**
     * Display a listing of the ApiKey.
     * GET|HEAD /apiKeys
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ApiKeyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ApiKeyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ApiKeyObjArr = $this->ApiKeyRepositoryObj->all();

        return $this->sendResponse($ApiKeyObjArr, 'ApiKey(s) retrieved successfully');
    }

    /**
     * Store a newly created ApiKey in storage.
     *
     * @param CreateApiKeyRequest $ApiKeyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateApiKeyRequest $ApiKeyRequestObj)
    {
        $input = $ApiKeyRequestObj->all();

        $ApiKeyObj = $this->ApiKeyRepositoryObj->create($input);

        return $this->sendResponse($ApiKeyObj, 'ApiKey saved successfully');
    }

    /**
     * Display the specified ApiKey.
     * GET|HEAD /apiKeys/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var ApiKey $apiKey */
        $ApiKeyObj = $this->ApiKeyRepositoryObj->findWithoutFail($id);
        if (empty($ApiKeyObj))
        {
            return Response::json(ResponseUtil::makeError('ApiKey not found'), 404);
        }

        return $this->sendResponse($ApiKeyObj, 'ApiKey retrieved successfully');
    }

    /**
     * Update the specified ApiKey in storage.
     * PUT/PATCH /apiKeys/{id}
     *
     * @param integer $id
     * @param UpdateApiKeyRequest $ApiKeyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateApiKeyRequest $ApiKeyRequestObj)
    {
        $input = $ApiKeyRequestObj->all();
        /** @var ApiKey $ApiKeyObj */
        $ApiKeyObj = $this->ApiKeyRepositoryObj->findWithoutFail($id);
        if (empty($ApiKeyObj))
        {
            return Response::json(ResponseUtil::makeError('ApiKey not found'), 404);
        }
        $ApiKeyObj = $this->ApiKeyRepositoryObj->update($input, $id);

        return $this->sendResponse($ApiKeyObj, 'ApiKey updated successfully');
    }

    /**
     * Remove the specified ApiKey from storage.
     * DELETE /apiKeys/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var ApiKey $ApiKeyObj */
        $ApiKeyObj = $this->ApiKeyRepositoryObj->findWithoutFail($id);
        if (empty($ApiKeyObj))
        {
            return Response::json(ResponseUtil::makeError('ApiKey not found'), 404);
        }

        $this->ApiKeyRepositoryObj->delete($id);

        return $this->sendResponse($id, 'ApiKey deleted successfully');
    }
}
