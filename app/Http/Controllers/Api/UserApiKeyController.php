<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\UserApiKey;
use App\Waypoint\Repositories\ApiKeyRepository;
use App\Waypoint\ResponseUtil;
use Response;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class ApiKeyDetailController
 *
 * see https://github.com/chrisbjr/api-guard
 */
class UserApiKeyController extends BaseApiController
{
    /** @var  ApiKeyRepository */
    private $ApiKeyRepositoryObj;

    public function __construct(ApiKeyRepository $ApiKeyRepositoryObj)
    {
        $this->ApiKeyRepositoryObj = $ApiKeyRepositoryObj;
    }

    /**
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function show_for_user($user_id)
    {
        /** @var UserApiKey $UserApiKeyRepositoryObjObj */
        $UserApiKeyRepositoryObjObj = $this->ApiKeyRepositoryObj->find($user_id);
        if (empty($UserApiKeyRepositoryObjObj))
        {
            return Response::json(ResponseUtil::makeError('ApiKey not found'), 404);
        }
        return $this->sendResponse($UserApiKeyRepositoryObjObj, 'ApiKey retrieved successfully');
    }

    /**
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function store($user_id)
    {
        /** @var UserApiKey $UserApiKeyRepositoryObjObj */
        $UserApiKeyRepositoryObjObj = $this->ApiKeyRepositoryObj->find($user_id);
        if (empty($UserApiKeyRepositoryObjObj))
        {
            return Response::json(ResponseUtil::makeError('ApiKey not found'), 404);
        }
        return $this->sendResponse($UserApiKeyRepositoryObjObj, 'ApiKey retrieved successfully');
    }
}
