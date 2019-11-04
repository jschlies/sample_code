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

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceRequest;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceController
 */
final class AdvancedVarianceController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceRepositoryObj = $AdvancedVarianceRepositoryObj;
        parent::__construct($AdvancedVarianceRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVariance.
     * GET|HEAD /advancedVariances
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceObjArr, 'AdvancedVariance(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVariance in storage.
     *
     * @param CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        $input = $AdvancedVarianceRequestObj->all();

        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance saved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AdvancedVariance $advancedVariance */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance retrieved successfully');
    }

    /**
     * Update the specified AdvancedVariance in storage.
     * PUT/PATCH /advancedVariances/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        $input = $AdvancedVarianceRequestObj->all();
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance updated successfully');
    }

    /**
     * Remove the specified AdvancedVariance from storage.
     * DELETE /advancedVariances/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        $this->AdvancedVarianceRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AdvancedVariance deleted successfully');
    }
}
