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

use App\Waypoint\Http\Requests\Generated\Api\CreateTenantIndustryRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateTenantIndustryRequest;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Repositories\TenantIndustryRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TenantIndustryController
 */
final class TenantIndustryController extends BaseApiController
{
    /** @var  TenantIndustryRepository */
    private $TenantIndustryRepositoryObj;

    public function __construct(TenantIndustryRepository $TenantIndustryRepositoryObj)
    {
        $this->TenantIndustryRepositoryObj = $TenantIndustryRepositoryObj;
        parent::__construct($TenantIndustryRepositoryObj);
    }

    /**
     * Display a listing of the TenantIndustry.
     * GET|HEAD /tenantIndustries
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->TenantIndustryRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->TenantIndustryRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $TenantIndustryObjArr = $this->TenantIndustryRepositoryObj->all();

        return $this->sendResponse($TenantIndustryObjArr, 'TenantIndustry(s) retrieved successfully');
    }

    /**
     * Store a newly created TenantIndustry in storage.
     *
     * @param CreateTenantIndustryRequest $TenantIndustryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateTenantIndustryRequest $TenantIndustryRequestObj)
    {
        $input = $TenantIndustryRequestObj->all();

        $TenantIndustryObj = $this->TenantIndustryRepositoryObj->create($input);

        return $this->sendResponse($TenantIndustryObj, 'TenantIndustry saved successfully');
    }

    /**
     * Display the specified TenantIndustry.
     * GET|HEAD /tenantIndustries/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var TenantIndustry $tenantIndustry */
        $TenantIndustryObj = $this->TenantIndustryRepositoryObj->findWithoutFail($id);
        if (empty($TenantIndustryObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }

        return $this->sendResponse($TenantIndustryObj, 'TenantIndustry retrieved successfully');
    }

    /**
     * Update the specified TenantIndustry in storage.
     * PUT/PATCH /tenantIndustries/{id}
     *
     * @param integer $id
     * @param UpdateTenantIndustryRequest $TenantIndustryRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateTenantIndustryRequest $TenantIndustryRequestObj)
    {
        $input = $TenantIndustryRequestObj->all();
        /** @var TenantIndustry $TenantIndustryObj */
        $TenantIndustryObj = $this->TenantIndustryRepositoryObj->findWithoutFail($id);
        if (empty($TenantIndustryObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }
        $TenantIndustryObj = $this->TenantIndustryRepositoryObj->update($input, $id);

        return $this->sendResponse($TenantIndustryObj, 'TenantIndustry updated successfully');
    }

    /**
     * Remove the specified TenantIndustry from storage.
     * DELETE /tenantIndustries/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var TenantIndustry $TenantIndustryObj */
        $TenantIndustryObj = $this->TenantIndustryRepositoryObj->findWithoutFail($id);
        if (empty($TenantIndustryObj))
        {
            return Response::json(ResponseUtil::makeError('TenantIndustry not found'), 404);
        }

        $this->TenantIndustryRepositoryObj->delete($id);

        return $this->sendResponse($id, 'TenantIndustry deleted successfully');
    }
}
