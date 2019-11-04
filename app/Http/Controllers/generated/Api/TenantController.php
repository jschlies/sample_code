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

use App\Waypoint\Http\Requests\Generated\Api\CreateTenantRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantRequest;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Repositories\TenantRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantController
 */
final class TenantController extends BaseApiController
{
    /** @var  TenantRepository */
    private $TenantRepositoryObj;

    public function __construct(TenantRepository $TenantRepositoryObj)
    {
        $this->TenantRepositoryObj = $TenantRepositoryObj;
        parent::__construct($TenantRepositoryObj);
    }

    /**
     * Display a listing of the Tenant.
     * GET|HEAD /tenants
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->TenantRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $TenantObjArr = $this->TenantRepositoryObj->all();

        return $this->sendResponse($TenantObjArr, 'Tenant(s) retrieved successfully');
    }

    /**
     * Store a newly created Tenant in storage.
     *
     * @param CreateTenantRequest $TenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateTenantRequest $TenantRequestObj)
    {
        $input = $TenantRequestObj->all();

        $TenantObj = $this->TenantRepositoryObj->create($input);

        return $this->sendResponse($TenantObj, 'Tenant saved successfully');
    }

    /**
     * Display the specified Tenant.
     * GET|HEAD /tenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Tenant $tenant */
        $TenantObj = $this->TenantRepositoryObj->findWithoutFail($id);
        if (empty($TenantObj))
        {
            return Response::json(ResponseUtil::makeError('Tenant not found'), 404);
        }

        return $this->sendResponse($TenantObj, 'Tenant retrieved successfully');
    }

    /**
     * Update the specified Tenant in storage.
     * PUT/PATCH /tenants/{id}
     *
     * @param integer $id
     * @param UpdateTenantRequest $TenantRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateTenantRequest $TenantRequestObj)
    {
        $input = $TenantRequestObj->all();
        /** @var Tenant $TenantObj */
        $TenantObj = $this->TenantRepositoryObj->findWithoutFail($id);
        if (empty($TenantObj))
        {
            return Response::json(ResponseUtil::makeError('Tenant not found'), 404);
        }
        $TenantObj = $this->TenantRepositoryObj->update($input, $id);

        return $this->sendResponse($TenantObj, 'Tenant updated successfully');
    }

    /**
     * Remove the specified Tenant from storage.
     * DELETE /tenants/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Tenant $TenantObj */
        $TenantObj = $this->TenantRepositoryObj->findWithoutFail($id);
        if (empty($TenantObj))
        {
            return Response::json(ResponseUtil::makeError('Tenant not found'), 404);
        }

        $this->TenantRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Tenant deleted successfully');
    }
}
