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

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceThresholdRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceThresholdRequest;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Repositories\AdvancedVarianceThresholdRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceThresholdController
 */
final class AdvancedVarianceThresholdController extends BaseApiController
{
    /** @var  AdvancedVarianceThresholdRepository */
    private $AdvancedVarianceThresholdRepositoryObj;

    public function __construct(AdvancedVarianceThresholdRepository $AdvancedVarianceThresholdRepositoryObj)
    {
        $this->AdvancedVarianceThresholdRepositoryObj = $AdvancedVarianceThresholdRepositoryObj;
        parent::__construct($AdvancedVarianceThresholdRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVarianceThreshold.
     * GET|HEAD /advancedVarianceThresholds
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceThresholdRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceThresholdRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceThresholdObjArr = $this->AdvancedVarianceThresholdRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceThresholdObjArr, 'AdvancedVarianceThreshold(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVarianceThreshold in storage.
     *
     * @param CreateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj)
    {
        $input = $AdvancedVarianceThresholdRequestObj->all();

        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceThresholdObj, 'AdvancedVarianceThreshold saved successfully');
    }

    /**
     * Display the specified AdvancedVarianceThreshold.
     * GET|HEAD /advancedVarianceThresholds/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AdvancedVarianceThreshold $advancedVarianceThreshold */
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceThresholdObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceThreshold not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceThresholdObj, 'AdvancedVarianceThreshold retrieved successfully');
    }

    /**
     * Update the specified AdvancedVarianceThreshold in storage.
     * PUT/PATCH /advancedVarianceThresholds/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj)
    {
        $input = $AdvancedVarianceThresholdRequestObj->all();
        /** @var AdvancedVarianceThreshold $AdvancedVarianceThresholdObj */
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceThresholdObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceThreshold not found'), 404);
        }
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceThresholdObj, 'AdvancedVarianceThreshold updated successfully');
    }

    /**
     * Remove the specified AdvancedVarianceThreshold from storage.
     * DELETE /advancedVarianceThresholds/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AdvancedVarianceThreshold $AdvancedVarianceThresholdObj */
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceThresholdObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceThreshold not found'), 404);
        }

        $this->AdvancedVarianceThresholdRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AdvancedVarianceThreshold deleted successfully');
    }
}
