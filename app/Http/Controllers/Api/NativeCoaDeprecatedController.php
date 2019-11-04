<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateNativeCoaRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeCoaRequest;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Repositories\NativeCoaFullRepository;
use App\Waypoint\Repositories\NativeCoaRepository;
use App\Waypoint\Repositories\NativeAccountRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * @codeCoverageIgnore
 */
class NativeCoaDeprecatedController extends BaseApiController
{
    /** @var  NativeAccountRepository */
    private $NativeCoaRepositoryObj;
    /** @var  NativeAccountRepository */
    private $NativeCoaFullRepositoryObj;

    /**
     * NativeCoaController constructor.
     * @param NativeAccountRepository $NativeCoaRepositoryObj
     */
    public function __construct(NativeCoaRepository $NativeCoaRepositoryObj)
    {
        $this->NativeCoaRepositoryObj     = $NativeCoaRepositoryObj;
        $this->NativeCoaFullRepositoryObj = App::make(NativeCoaFullRepository::class);
        parent::__construct($NativeCoaRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param $native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $native_coa_id)
    {
        /** @var NativeCoa $NativeCoaObj */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->findWithoutFail($native_coa_id);
        if (empty($NativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }
        return $this->sendResponse($NativeCoaObj, 'NativeCoa retrieved successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request, $client_id)
    {
        $this->NativeCoaRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->NativeCoaRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $NativeCoaObjArr = $this->NativeCoaRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        return $this->sendResponse($NativeCoaObjArr, 'NativeCoa(s) retrieved successfully');
    }

    /**
     * @param CreateNativeCoaRequest $CreateNativeCoaRequestObj
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateNativeCoaRequest $CreateNativeCoaRequestObj, $client_id)
    {
        $input        = $CreateNativeCoaRequestObj->all();
        $NativeCoaObj = $this->NativeCoaRepositoryObj->create($input);

        return $this->sendResponse($NativeCoaObj, 'NativeCoa saved successfully');
    }

    /**
     * @param integer $client_id
     * @param $native_coa_id
     * @param UpdateNativeCoaRequest $NativeCoaRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update($client_id, $native_coa_id, UpdateNativeCoaRequest $NativeCoaRequestObj)
    {
        $input = $NativeCoaRequestObj->all();
        /** @var NativeCoa $NativeCoaObj */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->findWithoutFail($native_coa_id);
        if (empty($NativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }
        $NativeCoaObj = $this->NativeCoaRepositoryObj->update($input, $native_coa_id);

        return $this->sendResponse($NativeCoaObj, 'NativeCoa updated successfully');
    }

    /**
     * @param $client_id
     * @param $native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $native_coa_id)
    {
        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj = $this->NativeCoaRepositoryObj->findWithoutFail($native_coa_id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }
        $NativeAccountObj->delete();

        return $this->sendResponse($native_coa_id, 'NativeCoa deleted successfully');
    }

    /**
     * @param Request $Request
     * @param $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $Request, $client_id)
    {
        $this->NativeCoaFullRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->NativeCoaFullRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $NativeCoaFullObjArr = $this->NativeCoaFullRepositoryObj->findWhere(
            ['client_id' => $client_id]
        );

        return $this->sendResponse($NativeCoaFullObjArr, 'NativeCoaFull(s) retrieved successfully');
    }
}
