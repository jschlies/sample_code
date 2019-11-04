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

use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldEquationPropertyRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCalculatedFieldEquationPropertyRequest;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Repositories\CalculatedFieldEquationPropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldEquationPropertyController
 */
final class CalculatedFieldEquationPropertyController extends BaseApiController
{
    /** @var  CalculatedFieldEquationPropertyRepository */
    private $CalculatedFieldEquationPropertyRepositoryObj;

    public function __construct(CalculatedFieldEquationPropertyRepository $CalculatedFieldEquationPropertyRepositoryObj)
    {
        $this->CalculatedFieldEquationPropertyRepositoryObj = $CalculatedFieldEquationPropertyRepositoryObj;
        parent::__construct($CalculatedFieldEquationPropertyRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedFieldEquationProperty.
     * GET|HEAD /calculatedFieldEquationProperties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CalculatedFieldEquationPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldEquationPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CalculatedFieldEquationPropertyObjArr = $this->CalculatedFieldEquationPropertyRepositoryObj->all();

        return $this->sendResponse($CalculatedFieldEquationPropertyObjArr, 'CalculatedFieldEquationProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created CalculatedFieldEquationProperty in storage.
     *
     * @param CreateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj)
    {
        $input = $CalculatedFieldEquationPropertyRequestObj->all();

        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldEquationPropertyObj, 'CalculatedFieldEquationProperty saved successfully');
    }

    /**
     * Display the specified CalculatedFieldEquationProperty.
     * GET|HEAD /calculatedFieldEquationProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CalculatedFieldEquationProperty $calculatedFieldEquationProperty */
        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquationProperty not found'), 404);
        }

        return $this->sendResponse($CalculatedFieldEquationPropertyObj, 'CalculatedFieldEquationProperty retrieved successfully');
    }

    /**
     * Update the specified CalculatedFieldEquationProperty in storage.
     * PUT/PATCH /calculatedFieldEquationProperties/{id}
     *
     * @param integer $id
     * @param UpdateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj)
    {
        $input = $CalculatedFieldEquationPropertyRequestObj->all();
        /** @var CalculatedFieldEquationProperty $CalculatedFieldEquationPropertyObj */
        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquationProperty not found'), 404);
        }
        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->update($input, $id);

        return $this->sendResponse($CalculatedFieldEquationPropertyObj, 'CalculatedFieldEquationProperty updated successfully');
    }

    /**
     * Remove the specified CalculatedFieldEquationProperty from storage.
     * DELETE /calculatedFieldEquationProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CalculatedFieldEquationProperty $CalculatedFieldEquationPropertyObj */
        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldEquationPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquationProperty not found'), 404);
        }

        $this->CalculatedFieldEquationPropertyRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CalculatedFieldEquationProperty deleted successfully');
    }
}
