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

use App\Waypoint\Http\Requests\Generated\Api\CreateAssetTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAssetTypeRequest;
use App\Waypoint\Models\AssetType;
use App\Waypoint\Repositories\AssetTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AssetTypeController
 */
final class AssetTypeController extends BaseApiController
{
    /** @var  AssetTypeRepository */
    private $AssetTypeRepositoryObj;

    public function __construct(AssetTypeRepository $AssetTypeRepositoryObj)
    {
        $this->AssetTypeRepositoryObj = $AssetTypeRepositoryObj;
        parent::__construct($AssetTypeRepositoryObj);
    }

    /**
     * Display a listing of the AssetType.
     * GET|HEAD /assetTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AssetTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AssetTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AssetTypeObjArr = $this->AssetTypeRepositoryObj->all();

        return $this->sendResponse($AssetTypeObjArr, 'AssetType(s) retrieved successfully');
    }

    /**
     * Store a newly created AssetType in storage.
     *
     * @param CreateAssetTypeRequest $AssetTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAssetTypeRequest $AssetTypeRequestObj)
    {
        $input = $AssetTypeRequestObj->all();

        $AssetTypeObj = $this->AssetTypeRepositoryObj->create($input);

        return $this->sendResponse($AssetTypeObj, 'AssetType saved successfully');
    }

    /**
     * Display the specified AssetType.
     * GET|HEAD /assetTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AssetType $assetType */
        $AssetTypeObj = $this->AssetTypeRepositoryObj->findWithoutFail($id);
        if (empty($AssetTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AssetType not found'), 404);
        }

        return $this->sendResponse($AssetTypeObj, 'AssetType retrieved successfully');
    }

    /**
     * Update the specified AssetType in storage.
     * PUT/PATCH /assetTypes/{id}
     *
     * @param integer $id
     * @param UpdateAssetTypeRequest $AssetTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAssetTypeRequest $AssetTypeRequestObj)
    {
        $input = $AssetTypeRequestObj->all();
        /** @var AssetType $AssetTypeObj */
        $AssetTypeObj = $this->AssetTypeRepositoryObj->findWithoutFail($id);
        if (empty($AssetTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AssetType not found'), 404);
        }
        $AssetTypeObj = $this->AssetTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($AssetTypeObj, 'AssetType updated successfully');
    }

    /**
     * Remove the specified AssetType from storage.
     * DELETE /assetTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AssetType $AssetTypeObj */
        $AssetTypeObj = $this->AssetTypeRepositoryObj->findWithoutFail($id);
        if (empty($AssetTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AssetType not found'), 404);
        }

        $this->AssetTypeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AssetType deleted successfully');
    }
}
