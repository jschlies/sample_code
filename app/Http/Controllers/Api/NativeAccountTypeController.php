<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateNativeAccountTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountTypeRequest;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeAccountTypeDetail;
use App\Waypoint\Repositories\NativeAccountTypeDetailRepository;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

class NativeAccountTypeController extends BaseApiController
{
    /** @var  NativeAccountTypeRepository */
    private $NativeAccountTypeRepositoryObj;
    /** @var  NativeAccountTypeDetailRepository */
    private $NativeAccountTypeDetailRepositoryObj;

    /**
     * NativeAccountTypeDetailController constructor.
     * @param NativeAccountTypeDetailRepository $NativeAccountTypeDetailRepository
     */
    public function __construct(NativeAccountTypeDetailRepository $NativeAccountTypeDetailRepository)
    {
        $this->NativeAccountTypeRepositoryObj       = \App::make(NativeAccountTypeRepository::class);
        $this->NativeAccountTypeDetailRepositoryObj = $NativeAccountTypeDetailRepository;
        parent::__construct($NativeAccountTypeDetailRepository);
    }

    /**
     * @param integer $client_id
     * @param integer $native_account_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show($client_id, $native_account_type_id)
    {
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($native_account_type_id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }
        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $native_account_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showDetail($client_id, $native_account_type_id)
    {
        /** @var NativeAccountTypeDetail $NativeAccountTypeDetailObj */
        $NativeAccountTypeDetailObj = $this->NativeAccountTypeDetailRepositoryObj->findWithoutFail($native_account_type_id);
        if (empty($NativeAccountTypeDetailObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeDetail not found'), 404);
        }
        return $this->sendResponse($NativeAccountTypeDetailObj, 'NativeAccountTypeDetail retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param Request $request
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index($client_id, Request $request)
    {
        $this->NativeAccountTypeRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->NativeAccountTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $NativeAccountTypeObjArr = $this->NativeAccountTypeRepositoryObj->getForClient($client_id);

        return $this->sendResponse($NativeAccountTypeObjArr, 'NativeAccountType(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param Request $request
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexDetail($client_id, Request $request)
    {
        $this->NativeAccountTypeDetailRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->NativeAccountTypeDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $NativeAccountTypeDetailObjArr = $this->NativeAccountTypeDetailRepositoryObj->getForClient($client_id);

        return $this->sendResponse($NativeAccountTypeDetailObjArr, 'NativeAccountTypeDetail(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeAccountType in storage.
     *
     * @param CreateNativeAccountTypeRequest $CreateNativeAccountTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(CreateNativeAccountTypeRequest $CreateNativeAccountTypeRequestObj)
    {
        $input                = $CreateNativeAccountTypeRequestObj->all();
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType saved successfully');
    }

    /**
     * Update the specified NativeAccountType in storage.
     * PUT/PATCH /nativeAccountTypes/{id}
     *
     * @param integer $client_id
     * @param integer $native_account_type_id
     * @param UpdateNativeAccountTypeRequest $NativeAccountTypeRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function update($client_id, $native_account_type_id, UpdateNativeAccountTypeRequest $NativeAccountTypeRequestObj)
    {
        $input = $NativeAccountTypeRequestObj->all();
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($native_account_type_id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->update($input, $native_account_type_id);

        return $this->sendResponse($NativeAccountTypeObj, 'NativeAccountType updated successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $native_account_type_id)
    {
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $this->NativeAccountTypeRepositoryObj->findWithoutFail($native_account_type_id);
        if (empty($NativeAccountTypeObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountType not found'), 404);
        }
        $NativeAccountTypeObj->delete();

        return $this->sendResponse($native_account_type_id, 'NativeAccountType deleted successfully');
    }
}