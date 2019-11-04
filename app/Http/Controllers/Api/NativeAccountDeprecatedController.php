<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountRequest;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Repositories\NativeAccountRepository;
use Illuminate\Http\JsonResponse;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;
use View;

/**
 * Class NativeAccountDeprecatedController
 * @codeCoverageIgnore
 */
class NativeAccountDeprecatedController extends BaseApiController
{
    /** @var  NativeAccountRepository */
    private $NativeAccountRepositoryObj;

    /**
     * NativeCoaController constructor.
     * @param NativeAccountRepository $NativeAccountRepositoryObj
     */
    public function __construct(NativeAccountRepository $NativeAccountRepositoryObj)
    {
        $this->NativeAccountRepositoryObj = $NativeAccountRepositoryObj;
        parent::__construct($NativeAccountRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param $native_coa_id
     * @param $native_account_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $native_coa_id, $native_account_id)
    {
        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($native_account_id);
        if ( ! $NativeAccountObj)
        {
            return Response::json(ResponseUtil::makeError('NativeCoaLedger not found'), 404);
        }
        return $this->sendResponse($NativeAccountObj->toArray(), 'NativeCoaLedger retrieved successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @param $native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request, $client_id, $native_coa_id)
    {
        $this->NativeAccountRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->NativeAccountRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $NativeAccountObjArr = $this->NativeAccountRepositoryObj->findWhere([['native_coa_id', '=', $native_coa_id]]);

        return $this->sendResponse($NativeAccountObjArr->toArray(), 'NativeCoaLedger(s) retrieved successfully');
    }

    /**
     * @param CreateAccessListRequest $CreateAccessListRequestObj
     * @param integer $client_id
     * @param $native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateAccessListRequest $CreateAccessListRequestObj, $client_id, $native_coa_id)
    {
        $input         = $CreateAccessListRequestObj->all();
        $AccessListObj = $this->NativeAccountRepositoryObj->create($input);

        return $this->sendResponse($AccessListObj->toArray(), 'AccessList saved successfully');
    }

    /**
     * @param integer $client_id
     * @param $native_coa_id
     * @param $native_account_id
     * @param UpdateNativeAccountRequest $NativeAccountRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update($client_id, $native_coa_id, $native_account_id, UpdateNativeAccountRequest $NativeAccountRequestObj)
    {
        $input = $NativeAccountRequestObj->all();
        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($native_account_id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccount not found'), 404);
        }
        $NativeAccountObj = $this->NativeAccountRepositoryObj->update($input, $native_account_id);

        return $this->sendResponse($NativeAccountObj->toArray(), 'NativeAccount updated successfully');
    }

    /**
     * @param integer $client_id
     * @param $native_coa_id
     * @param $native_account_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $native_coa_id, $native_account_id)
    {
        /** @var NativeAccount NativeAccountObj */
        $NativeAccountObj = $this->NativeAccountRepositoryObj->findWithoutFail($native_account_id);
        if (empty($NativeAccountObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }
        $NativeAccountObj->delete();

        return $this->sendResponse($native_account_id, 'AccessList deleted successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @return \Illuminate\Contracts\View\View
     */
    public function renderMappingsPerClient(Request $request, $client_id)
    {
        $client_mapping_arr = $this->NativeAccountRepositoryObj->mappingForClient($client_id);
        $client_mapping_arr = array_map(
            function ($val)
            {
                return json_decode(json_encode($val), true);
            }, $client_mapping_arr
        );

        return View::make(
            'pages.client_mappings',
            [
                'keys'            => array_keys($client_mapping_arr[0]),
                'client_mappings' => $client_mapping_arr,
            ]
        );
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @param integer $property_id
     * @return \Illuminate\Contracts\View\View
     */
    public function renderMappingsPerClientProperty(Request $request, $client_id, $property_id)
    {
        $client_mapping_arr = $this->NativeAccountRepositoryObj->mappingsPerClientProperty($client_id, $property_id);
        $client_mapping_arr = array_map(
            function ($val)
            {
                return json_decode(json_encode($val), true);
            }, $client_mapping_arr
        );

        return View::make(
            'pages.client_mappings',
            [
                'keys'            => array_keys($client_mapping_arr[0]),
                'client_mappings' => $client_mapping_arr,
            ]
        );
    }
}
