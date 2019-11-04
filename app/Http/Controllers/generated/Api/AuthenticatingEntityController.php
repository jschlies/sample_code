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

use App\Waypoint\Http\Requests\Generated\Api\CreateAuthenticatingEntityRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAuthenticatingEntityRequest;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Repositories\AuthenticatingEntityRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AuthenticatingEntityController
 */
final class AuthenticatingEntityController extends BaseApiController
{
    /** @var  AuthenticatingEntityRepository */
    private $AuthenticatingEntityRepositoryObj;

    public function __construct(AuthenticatingEntityRepository $AuthenticatingEntityRepositoryObj)
    {
        $this->AuthenticatingEntityRepositoryObj = $AuthenticatingEntityRepositoryObj;
        parent::__construct($AuthenticatingEntityRepositoryObj);
    }

    /**
     * Display a listing of the AuthenticatingEntity.
     * GET|HEAD /authenticatingEntities
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AuthenticatingEntityRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AuthenticatingEntityRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AuthenticatingEntityObjArr = $this->AuthenticatingEntityRepositoryObj->all();

        return $this->sendResponse($AuthenticatingEntityObjArr, 'AuthenticatingEntity(s) retrieved successfully');
    }

    /**
     * Store a newly created AuthenticatingEntity in storage.
     *
     * @param CreateAuthenticatingEntityRequest $AuthenticatingEntityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAuthenticatingEntityRequest $AuthenticatingEntityRequestObj)
    {
        $input = $AuthenticatingEntityRequestObj->all();

        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->create($input);

        return $this->sendResponse($AuthenticatingEntityObj, 'AuthenticatingEntity saved successfully');
    }

    /**
     * Display the specified AuthenticatingEntity.
     * GET|HEAD /authenticatingEntities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AuthenticatingEntity $authenticatingEntity */
        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->findWithoutFail($id);
        if (empty($AuthenticatingEntityObj))
        {
            return Response::json(ResponseUtil::makeError('AuthenticatingEntity not found'), 404);
        }

        return $this->sendResponse($AuthenticatingEntityObj, 'AuthenticatingEntity retrieved successfully');
    }

    /**
     * Update the specified AuthenticatingEntity in storage.
     * PUT/PATCH /authenticatingEntities/{id}
     *
     * @param integer $id
     * @param UpdateAuthenticatingEntityRequest $AuthenticatingEntityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAuthenticatingEntityRequest $AuthenticatingEntityRequestObj)
    {
        $input = $AuthenticatingEntityRequestObj->all();
        /** @var AuthenticatingEntity $AuthenticatingEntityObj */
        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->findWithoutFail($id);
        if (empty($AuthenticatingEntityObj))
        {
            return Response::json(ResponseUtil::makeError('AuthenticatingEntity not found'), 404);
        }
        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->update($input, $id);

        return $this->sendResponse($AuthenticatingEntityObj, 'AuthenticatingEntity updated successfully');
    }

    /**
     * Remove the specified AuthenticatingEntity from storage.
     * DELETE /authenticatingEntities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AuthenticatingEntity $AuthenticatingEntityObj */
        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->findWithoutFail($id);
        if (empty($AuthenticatingEntityObj))
        {
            return Response::json(ResponseUtil::makeError('AuthenticatingEntity not found'), 404);
        }

        $this->AuthenticatingEntityRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AuthenticatingEntity deleted successfully');
    }
}
