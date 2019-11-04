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

use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCalculatedFieldRequest;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldController
 */
final class CalculatedFieldController extends BaseApiController
{
    /** @var  CalculatedFieldRepository */
    private $CalculatedFieldRepositoryObj;

    public function __construct(CalculatedFieldRepository $CalculatedFieldRepositoryObj)
    {
        $this->CalculatedFieldRepositoryObj = $CalculatedFieldRepositoryObj;
        parent::__construct($CalculatedFieldRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedField.
     * GET|HEAD /calculatedFields
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CalculatedFieldRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CalculatedFieldObjArr = $this->CalculatedFieldRepositoryObj->all();

        return $this->sendResponse($CalculatedFieldObjArr, 'CalculatedField(s) retrieved successfully');
    }

    /**
     * Store a newly created CalculatedField in storage.
     *
     * @param CreateCalculatedFieldRequest $CalculatedFieldRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCalculatedFieldRequest $CalculatedFieldRequestObj)
    {
        $input = $CalculatedFieldRequestObj->all();

        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldObj, 'CalculatedField saved successfully');
    }

    /**
     * Display the specified CalculatedField.
     * GET|HEAD /calculatedFields/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CalculatedField $calculatedField */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }

        return $this->sendResponse($CalculatedFieldObj, 'CalculatedField retrieved successfully');
    }

    /**
     * Update the specified CalculatedField in storage.
     * PUT/PATCH /calculatedFields/{id}
     *
     * @param integer $id
     * @param UpdateCalculatedFieldRequest $CalculatedFieldRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCalculatedFieldRequest $CalculatedFieldRequestObj)
    {
        $input = $CalculatedFieldRequestObj->all();
        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->update($input, $id);

        return $this->sendResponse($CalculatedFieldObj, 'CalculatedField updated successfully');
    }

    /**
     * Remove the specified CalculatedField from storage.
     * DELETE /calculatedFields/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($id);
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }

        $this->CalculatedFieldRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CalculatedField deleted successfully');
    }
}
