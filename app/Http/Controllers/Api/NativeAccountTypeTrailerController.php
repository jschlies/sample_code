<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateNativeAccountTypeTrailerRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeAccountTypeTrailerRequest;
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Repositories\NativeAccountTypeTrailerRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NativeAccountTypeTrailerController
 */
class NativeAccountTypeTrailerController extends BaseApiController
{
    /** @var  NativeAccountTypeTrailerRepository */
    private $NativeAccountTypeTrailerRepositoryObj;

    public function __construct(NativeAccountTypeTrailerRepository $NativeAccountTypeTrailerRepositoryObj)
    {
        $this->NativeAccountTypeTrailerRepositoryObj = $NativeAccountTypeTrailerRepositoryObj;
        parent::__construct($NativeAccountTypeTrailerRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param $client_id
     * @param $native_account_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id, $native_account_type_id)
    {
        $this->NativeAccountTypeTrailerRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeAccountTypeTrailerRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeAccountTypeRepositoryObj = \App::make(NativeAccountTypeRepository::class);
        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = $NativeAccountTypeRepositoryObj->find($native_account_type_id);

        return $this->sendResponse($NativeAccountTypeObj->nativeAccountTypeTrailers, 'NativeAccountTypeTrailer(s) retrieved successfully');
    }

    /**
     * @param CreateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj
     * @param $client_id
     * @param $native_account_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj, $client_id, $native_account_type_id)
    {
        $input = $NativeAccountTypeTrailerRequestObj->all();

        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountTypeTrailerObj, 'NativeAccountTypeTrailer saved successfully');
    }

    /**
     * @param $client_id
     * @param $native_account_type_id
     * @param $native_account_type_trailer_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $native_account_type_id, $native_account_type_trailer_id)
    {
        /** @var NativeAccountTypeTrailer $nativeAccountTypeTrailer */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($native_account_type_trailer_id);
        if (empty($NativeAccountTypeTrailerObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeTrailer not found'), 404);
        }

        return $this->sendResponse($NativeAccountTypeTrailerObj, 'NativeAccountTypeTrailer retrieved successfully');
    }

    /**
     * Update the specified NativeAccountTypeTrailer in storage.
     * PUT/PATCH /nativeAccountTypeTrailers/{id}
     *
     * @param integer $id
     * @param UpdateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function update(UpdateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj, $client_id, $native_account_type_id, $native_account_type_trailer_id)
    {
        $input = $NativeAccountTypeTrailerRequestObj->all();
        /** @var NativeAccountTypeTrailer $NativeAccountTypeTrailerObj */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($native_account_type_trailer_id);
        if (empty($NativeAccountTypeTrailerObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeTrailer not found'), 404);
        }
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->update($input, $native_account_type_trailer_id);

        return $this->sendResponse($NativeAccountTypeTrailerObj, 'NativeAccountTypeTrailer updated successfully');
    }

    /**
     * Remove the specified NativeAccountTypeTrailer from storage.
     * DELETE /nativeAccountTypeTrailers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $native_account_type_id, $native_account_type_trailer_id)
    {
        /** @var NativeAccountTypeTrailer $NativeAccountTypeTrailerObj */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($native_account_type_trailer_id);
        if (empty($NativeAccountTypeTrailerObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeTrailer not found'), 404);
        }

        $this->NativeAccountTypeTrailerRepositoryObj->delete($native_account_type_trailer_id);

        return $this->sendResponse($native_account_type_trailer_id, 'NativeAccountTypeTrailer deleted successfully');
    }
}
