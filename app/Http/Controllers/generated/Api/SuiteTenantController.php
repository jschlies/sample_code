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

use App\Waypoint\Http\Requests\Generated\Api\CreateSuiteTenantRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateSuiteTenantRequest;
use App\Waypoint\Models\SuiteTenant;
use App\Waypoint\Repositories\SuiteTenantRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class SuiteTenantController
 */
final class SuiteTenantController extends BaseApiController
{
    /** @var  SuiteTenantRepository */
    private $SuiteTenantRepositoryObj;

    public function __construct(SuiteTenantRepository $SuiteTenantRepositoryObj)
    {
        $this->SuiteTenantRepositoryObj = $SuiteTenantRepositoryObj;
        parent::__construct($SuiteTenantRepositoryObj);
    }

    /**
     * Display a listing of the SuiteTenant.
     * GET|HEAD /suiteTenants
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->SuiteTenantRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->SuiteTenantRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $SuiteTenantObjArr = $this->SuiteTenantRepositoryObj->all();

        return $this->sendResponse($SuiteTenantObjArr, 'SuiteTenant(s) retrieved successfully');
    }

    /**
     * Store a newly created SuiteTenant in storage.
     *
     * @param CreateSuiteTenantRequest $SuiteTenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateSuiteTenantRequest $SuiteTenantRequestObj)
    {
        $input = $SuiteTenantRequestObj->all();

        $SuiteTenantObj = $this->SuiteTenantRepositoryObj->create($input);

        return $this->sendResponse($SuiteTenantObj, 'SuiteTenant saved successfully');
    }

    /**
     * Display the specified SuiteTenant.
     * GET|HEAD /suiteTenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var SuiteTenant $suiteTenant */
        $SuiteTenantObj = $this->SuiteTenantRepositoryObj->findWithoutFail($id);
        if (empty($SuiteTenantObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteTenant not found'), 404);
        }

        return $this->sendResponse($SuiteTenantObj, 'SuiteTenant retrieved successfully');
    }

    /**
     * Update the specified SuiteTenant in storage.
     * PUT/PATCH /suiteTenants/{id}
     *
     * @param integer $id
     * @param UpdateSuiteTenantRequest $SuiteTenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateSuiteTenantRequest $SuiteTenantRequestObj)
    {
        $input = $SuiteTenantRequestObj->all();
        /** @var SuiteTenant $SuiteTenantObj */
        $SuiteTenantObj = $this->SuiteTenantRepositoryObj->findWithoutFail($id);
        if (empty($SuiteTenantObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteTenant not found'), 404);
        }
        $SuiteTenantObj = $this->SuiteTenantRepositoryObj->update($input, $id);

        return $this->sendResponse($SuiteTenantObj, 'SuiteTenant updated successfully');
    }

    /**
     * Remove the specified SuiteTenant from storage.
     * DELETE /suiteTenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var SuiteTenant $SuiteTenantObj */
        $SuiteTenantObj = $this->SuiteTenantRepositoryObj->findWithoutFail($id);
        if (empty($SuiteTenantObj))
        {
            return Response::json(ResponseUtil::makeError('SuiteTenant not found'), 404);
        }

        $this->SuiteTenantRepositoryObj->delete($id);

        return $this->sendResponse($id, 'SuiteTenant deleted successfully');
    }
}
