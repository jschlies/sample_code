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

use App\Waypoint\Http\Requests\Generated\Api\CreateNativeCoaRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateNativeCoaRequest;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Repositories\NativeCoaRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class NativeCoaController
 */
final class NativeCoaController extends BaseApiController
{
    /** @var  NativeCoaRepository */
    private $NativeCoaRepositoryObj;

    public function __construct(NativeCoaRepository $NativeCoaRepositoryObj)
    {
        $this->NativeCoaRepositoryObj = $NativeCoaRepositoryObj;
        parent::__construct($NativeCoaRepositoryObj);
    }

    /**
     * Display a listing of the NativeCoa.
     * GET|HEAD /nativeCoas
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NativeCoaRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeCoaRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeCoaObjArr = $this->NativeCoaRepositoryObj->all();

        return $this->sendResponse($NativeCoaObjArr, 'NativeCoa(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeCoa in storage.
     *
     * @param CreateNativeCoaRequest $NativeCoaRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNativeCoaRequest $NativeCoaRequestObj)
    {
        $input = $NativeCoaRequestObj->all();

        $NativeCoaObj = $this->NativeCoaRepositoryObj->create($input);

        return $this->sendResponse($NativeCoaObj, 'NativeCoa saved successfully');
    }

    /**
     * Display the specified NativeCoa.
     * GET|HEAD /nativeCoas/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NativeCoa $nativeCoa */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($NativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }

        return $this->sendResponse($NativeCoaObj, 'NativeCoa retrieved successfully');
    }

    /**
     * Update the specified NativeCoa in storage.
     * PUT/PATCH /nativeCoas/{id}
     *
     * @param integer $id
     * @param UpdateNativeCoaRequest $NativeCoaRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateNativeCoaRequest $NativeCoaRequestObj)
    {
        $input = $NativeCoaRequestObj->all();
        /** @var NativeCoa $NativeCoaObj */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($NativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }
        $NativeCoaObj = $this->NativeCoaRepositoryObj->update($input, $id);

        return $this->sendResponse($NativeCoaObj, 'NativeCoa updated successfully');
    }

    /**
     * Remove the specified NativeCoa from storage.
     * DELETE /nativeCoas/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NativeCoa $NativeCoaObj */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->findWithoutFail($id);
        if (empty($NativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('NativeCoa not found'), 404);
        }

        $this->NativeCoaRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NativeCoa deleted successfully');
    }
}
