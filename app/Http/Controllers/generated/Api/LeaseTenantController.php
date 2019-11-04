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

use App\Waypoint\Http\Requests\Generated\Api\CreateLeaseTenantRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateLeaseTenantRequest;
use App\Waypoint\Models\LeaseTenant;
use App\Waypoint\Repositories\LeaseTenantRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class LeaseTenantController
 */
final class LeaseTenantController extends BaseApiController
{
    /** @var  LeaseTenantRepository */
    private $LeaseTenantRepositoryObj;

    public function __construct(LeaseTenantRepository $LeaseTenantRepositoryObj)
    {
        $this->LeaseTenantRepositoryObj = $LeaseTenantRepositoryObj;
        parent::__construct($LeaseTenantRepositoryObj);
    }

    /**
     * Display a listing of the LeaseTenant.
     * GET|HEAD /leaseTenants
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->LeaseTenantRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->LeaseTenantRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $LeaseTenantObjArr = $this->LeaseTenantRepositoryObj->all();

        return $this->sendResponse($LeaseTenantObjArr, 'LeaseTenant(s) retrieved successfully');
    }

    /**
     * Store a newly created LeaseTenant in storage.
     *
     * @param CreateLeaseTenantRequest $LeaseTenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateLeaseTenantRequest $LeaseTenantRequestObj)
    {
        $input = $LeaseTenantRequestObj->all();

        $LeaseTenantObj = $this->LeaseTenantRepositoryObj->create($input);

        return $this->sendResponse($LeaseTenantObj, 'LeaseTenant saved successfully');
    }

    /**
     * Display the specified LeaseTenant.
     * GET|HEAD /leaseTenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var LeaseTenant $leaseTenant */
        $LeaseTenantObj = $this->LeaseTenantRepositoryObj->findWithoutFail($id);
        if (empty($LeaseTenantObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseTenant not found'), 404);
        }

        return $this->sendResponse($LeaseTenantObj, 'LeaseTenant retrieved successfully');
    }

    /**
     * Update the specified LeaseTenant in storage.
     * PUT/PATCH /leaseTenants/{id}
     *
     * @param integer $id
     * @param UpdateLeaseTenantRequest $LeaseTenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateLeaseTenantRequest $LeaseTenantRequestObj)
    {
        $input = $LeaseTenantRequestObj->all();
        /** @var LeaseTenant $LeaseTenantObj */
        $LeaseTenantObj = $this->LeaseTenantRepositoryObj->findWithoutFail($id);
        if (empty($LeaseTenantObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseTenant not found'), 404);
        }
        $LeaseTenantObj = $this->LeaseTenantRepositoryObj->update($input, $id);

        return $this->sendResponse($LeaseTenantObj, 'LeaseTenant updated successfully');
    }

    /**
     * Remove the specified LeaseTenant from storage.
     * DELETE /leaseTenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var LeaseTenant $LeaseTenantObj */
        $LeaseTenantObj = $this->LeaseTenantRepositoryObj->findWithoutFail($id);
        if (empty($LeaseTenantObj))
        {
            return Response::json(ResponseUtil::makeError('LeaseTenant not found'), 404);
        }

        $this->LeaseTenantRepositoryObj->delete($id);

        return $this->sendResponse($id, 'LeaseTenant deleted successfully');
    }
}
