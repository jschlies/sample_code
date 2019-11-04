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
final class NativeAccountTypeTrailerController extends BaseApiController
{
    /** @var  NativeAccountTypeTrailerRepository */
    private $NativeAccountTypeTrailerRepositoryObj;

    public function __construct(NativeAccountTypeTrailerRepository $NativeAccountTypeTrailerRepositoryObj)
    {
        $this->NativeAccountTypeTrailerRepositoryObj = $NativeAccountTypeTrailerRepositoryObj;
        parent::__construct($NativeAccountTypeTrailerRepositoryObj);
    }

    /**
     * Display a listing of the NativeAccountTypeTrailer.
     * GET|HEAD /nativeAccountTypeTrailers
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->NativeAccountTypeTrailerRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->NativeAccountTypeTrailerRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $NativeAccountTypeTrailerObjArr = $this->NativeAccountTypeTrailerRepositoryObj->all();

        return $this->sendResponse($NativeAccountTypeTrailerObjArr, 'NativeAccountTypeTrailer(s) retrieved successfully');
    }

    /**
     * Store a newly created NativeAccountTypeTrailer in storage.
     *
     * @param CreateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj)
    {
        $input = $NativeAccountTypeTrailerRequestObj->all();

        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->create($input);

        return $this->sendResponse($NativeAccountTypeTrailerObj, 'NativeAccountTypeTrailer saved successfully');
    }

    /**
     * Display the specified NativeAccountTypeTrailer.
     * GET|HEAD /nativeAccountTypeTrailers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var NativeAccountTypeTrailer $nativeAccountTypeTrailer */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($id);
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
     * @throws Exception
     */
    public function update($id, UpdateNativeAccountTypeTrailerRequest $NativeAccountTypeTrailerRequestObj)
    {
        $input = $NativeAccountTypeTrailerRequestObj->all();
        /** @var NativeAccountTypeTrailer $NativeAccountTypeTrailerObj */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountTypeTrailerObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeTrailer not found'), 404);
        }
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->update($input, $id);

        return $this->sendResponse($NativeAccountTypeTrailerObj, 'NativeAccountTypeTrailer updated successfully');
    }

    /**
     * Remove the specified NativeAccountTypeTrailer from storage.
     * DELETE /nativeAccountTypeTrailers/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var NativeAccountTypeTrailer $NativeAccountTypeTrailerObj */
        $NativeAccountTypeTrailerObj = $this->NativeAccountTypeTrailerRepositoryObj->findWithoutFail($id);
        if (empty($NativeAccountTypeTrailerObj))
        {
            return Response::json(ResponseUtil::makeError('NativeAccountTypeTrailer not found'), 404);
        }

        $this->NativeAccountTypeTrailerRepositoryObj->delete($id);

        return $this->sendResponse($id, 'NativeAccountTypeTrailer deleted successfully');
    }
}
