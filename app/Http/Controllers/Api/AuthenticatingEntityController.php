<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;

use App\Waypoint\Http\Requests\Generated\Api\CreateAuthenticatingEntityRequest;
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
class AuthenticatingEntityController extends BaseApiController
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function store(CreateAuthenticatingEntityRequest $AuthenticatingEntityRequestObj)
    {
        $input = $AuthenticatingEntityRequestObj->all();

        $AuthenticatingEntityObj = $this->AuthenticatingEntityRepositoryObj->create($input);

        return $this->sendResponse($AuthenticatingEntityObj, 'AuthenticatingEntity saved successfully');
    }

    /**
     * Remove the specified AuthenticatingEntity from storage.
     * DELETE /authenticatingEntities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
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
