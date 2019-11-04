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

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceExplanationTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceExplanationTypeRequest;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Repositories\AdvancedVarianceExplanationTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceExplanationTypeController
 */
final class AdvancedVarianceExplanationTypeController extends BaseApiController
{
    /** @var  AdvancedVarianceExplanationTypeRepository */
    private $AdvancedVarianceExplanationTypeRepositoryObj;

    public function __construct(AdvancedVarianceExplanationTypeRepository $AdvancedVarianceExplanationTypeRepositoryObj)
    {
        $this->AdvancedVarianceExplanationTypeRepositoryObj = $AdvancedVarianceExplanationTypeRepositoryObj;
        parent::__construct($AdvancedVarianceExplanationTypeRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVarianceExplanationType.
     * GET|HEAD /advancedVarianceExplanationTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceExplanationTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceExplanationTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceExplanationTypeObjArr = $this->AdvancedVarianceExplanationTypeRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceExplanationTypeObjArr, 'AdvancedVarianceExplanationType(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVarianceExplanationType in storage.
     *
     * @param CreateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj)
    {
        $input = $AdvancedVarianceExplanationTypeRequestObj->all();

        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType saved successfully');
    }

    /**
     * Display the specified AdvancedVarianceExplanationType.
     * GET|HEAD /advancedVarianceExplanationTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AdvancedVarianceExplanationType $advancedVarianceExplanationType */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType retrieved successfully');
    }

    /**
     * Update the specified AdvancedVarianceExplanationType in storage.
     * PUT/PATCH /advancedVarianceExplanationTypes/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj)
    {
        $input = $AdvancedVarianceExplanationTypeRequestObj->all();
        /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType updated successfully');
    }

    /**
     * Remove the specified AdvancedVarianceExplanationType from storage.
     * DELETE /advancedVarianceExplanationTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }

        $this->AdvancedVarianceExplanationTypeRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AdvancedVarianceExplanationType deleted successfully');
    }
}
