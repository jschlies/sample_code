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

use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldVariableRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCalculatedFieldVariableRequest;
use App\Waypoint\Models\CalculatedFieldVariable;
use App\Waypoint\Repositories\CalculatedFieldVariableRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldVariableController
 */
final class CalculatedFieldVariableController extends BaseApiController
{
    /** @var  CalculatedFieldVariableRepository */
    private $CalculatedFieldVariableRepositoryObj;

    public function __construct(CalculatedFieldVariableRepository $CalculatedFieldVariableRepositoryObj)
    {
        $this->CalculatedFieldVariableRepositoryObj = $CalculatedFieldVariableRepositoryObj;
        parent::__construct($CalculatedFieldVariableRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedFieldVariable.
     * GET|HEAD /calculatedFieldVariables
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CalculatedFieldVariableRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldVariableRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CalculatedFieldVariableObjArr = $this->CalculatedFieldVariableRepositoryObj->all();

        return $this->sendResponse($CalculatedFieldVariableObjArr, 'CalculatedFieldVariable(s) retrieved successfully');
    }

    /**
     * Store a newly created CalculatedFieldVariable in storage.
     *
     * @param CreateCalculatedFieldVariableRequest $CalculatedFieldVariableRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCalculatedFieldVariableRequest $CalculatedFieldVariableRequestObj)
    {
        $input = $CalculatedFieldVariableRequestObj->all();

        $CalculatedFieldVariableObj = $this->CalculatedFieldVariableRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldVariableObj, 'CalculatedFieldVariable saved successfully');
    }

    /**
     * Display the specified CalculatedFieldVariable.
     * GET|HEAD /calculatedFieldVariables/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CalculatedFieldVariable $calculatedFieldVariable */
        $CalculatedFieldVariableObj = $this->CalculatedFieldVariableRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldVariableObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldVariable not found'), 404);
        }

        return $this->sendResponse($CalculatedFieldVariableObj, 'CalculatedFieldVariable retrieved successfully');
    }

    /**
     * Update the specified CalculatedFieldVariable in storage.
     * PUT/PATCH /calculatedFieldVariables/{id}
     *
     * @param integer $id
     * @param UpdateCalculatedFieldVariableRequest $CalculatedFieldVariableRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCalculatedFieldVariableRequest $CalculatedFieldVariableRequestObj)
    {
        $input = $CalculatedFieldVariableRequestObj->all();
        /** @var CalculatedFieldVariable $CalculatedFieldVariableObj */
        $CalculatedFieldVariableObj = $this->CalculatedFieldVariableRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldVariableObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldVariable not found'), 404);
        }
        $CalculatedFieldVariableObj = $this->CalculatedFieldVariableRepositoryObj->update($input, $id);

        return $this->sendResponse($CalculatedFieldVariableObj, 'CalculatedFieldVariable updated successfully');
    }

    /**
     * Remove the specified CalculatedFieldVariable from storage.
     * DELETE /calculatedFieldVariables/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CalculatedFieldVariable $CalculatedFieldVariableObj */
        $CalculatedFieldVariableObj = $this->CalculatedFieldVariableRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldVariableObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldVariable not found'), 404);
        }

        $this->CalculatedFieldVariableRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CalculatedFieldVariable deleted successfully');
    }
}
