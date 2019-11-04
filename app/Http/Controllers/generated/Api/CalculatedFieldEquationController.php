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

use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldEquationRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCalculatedFieldEquationRequest;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Repositories\CalculatedFieldEquationRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldEquationController
 */
final class CalculatedFieldEquationController extends BaseApiController
{
    /** @var  CalculatedFieldEquationRepository */
    private $CalculatedFieldEquationRepositoryObj;

    public function __construct(CalculatedFieldEquationRepository $CalculatedFieldEquationRepositoryObj)
    {
        $this->CalculatedFieldEquationRepositoryObj = $CalculatedFieldEquationRepositoryObj;
        parent::__construct($CalculatedFieldEquationRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedFieldEquation.
     * GET|HEAD /calculatedFieldEquations
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CalculatedFieldEquationRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldEquationRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CalculatedFieldEquationObjArr = $this->CalculatedFieldEquationRepositoryObj->all();

        return $this->sendResponse($CalculatedFieldEquationObjArr, 'CalculatedFieldEquation(s) retrieved successfully');
    }

    /**
     * Store a newly created CalculatedFieldEquation in storage.
     *
     * @param CreateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj)
    {
        $input = $CalculatedFieldEquationRequestObj->all();

        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldEquationObj, 'CalculatedFieldEquation saved successfully');
    }

    /**
     * Display the specified CalculatedFieldEquation.
     * GET|HEAD /calculatedFieldEquations/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CalculatedFieldEquation $calculatedFieldEquation */
        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquation not found'), 404);
        }

        return $this->sendResponse($CalculatedFieldEquationObj, 'CalculatedFieldEquation retrieved successfully');
    }

    /**
     * Update the specified CalculatedFieldEquation in storage.
     * PUT/PATCH /calculatedFieldEquations/{id}
     *
     * @param integer $id
     * @param UpdateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj)
    {
        $input = $CalculatedFieldEquationRequestObj->all();
        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquation not found'), 404);
        }
        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->update($input, $id);

        return $this->sendResponse($CalculatedFieldEquationObj, 'CalculatedFieldEquation updated successfully');
    }

    /**
     * Remove the specified CalculatedFieldEquation from storage.
     * DELETE /calculatedFieldEquations/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquation not found'), 404);
        }

        $this->CalculatedFieldEquationRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CalculatedFieldEquation deleted successfully');
    }
}
