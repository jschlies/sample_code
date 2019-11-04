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

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceApprovalRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceApprovalRequest;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceApprovalController
 */
final class AdvancedVarianceApprovalController extends BaseApiController
{
    /** @var  AdvancedVarianceApprovalRepository */
    private $AdvancedVarianceApprovalRepositoryObj;

    public function __construct(AdvancedVarianceApprovalRepository $AdvancedVarianceApprovalRepositoryObj)
    {
        $this->AdvancedVarianceApprovalRepositoryObj = $AdvancedVarianceApprovalRepositoryObj;
        parent::__construct($AdvancedVarianceApprovalRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVarianceApproval.
     * GET|HEAD /advancedVarianceApprovals
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceApprovalRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceApprovalRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceApprovalObjArr = $this->AdvancedVarianceApprovalRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceApprovalObjArr, 'AdvancedVarianceApproval(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVarianceApproval in storage.
     *
     * @param CreateAdvancedVarianceApprovalRequest $AdvancedVarianceApprovalRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAdvancedVarianceApprovalRequest $AdvancedVarianceApprovalRequestObj)
    {
        $input = $AdvancedVarianceApprovalRequestObj->all();

        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceApprovalObj, 'AdvancedVarianceApproval saved successfully');
    }

    /**
     * Display the specified AdvancedVarianceApproval.
     * GET|HEAD /advancedVarianceApprovals/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AdvancedVarianceApproval $advancedVarianceApproval */
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceApprovalObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceApproval not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceApprovalObj, 'AdvancedVarianceApproval retrieved successfully');
    }

    /**
     * Update the specified AdvancedVarianceApproval in storage.
     * PUT/PATCH /advancedVarianceApprovals/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceApprovalRequest $AdvancedVarianceApprovalRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAdvancedVarianceApprovalRequest $AdvancedVarianceApprovalRequestObj)
    {
        $input = $AdvancedVarianceApprovalRequestObj->all();
        /** @var AdvancedVarianceApproval $AdvancedVarianceApprovalObj */
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceApprovalObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceApproval not found'), 404);
        }
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceApprovalObj, 'AdvancedVarianceApproval updated successfully');
    }

    /**
     * Remove the specified AdvancedVarianceApproval from storage.
     * DELETE /advancedVarianceApprovals/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AdvancedVarianceApproval $AdvancedVarianceApprovalObj */
        $AdvancedVarianceApprovalObj = $this->AdvancedVarianceApprovalRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceApprovalObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceApproval not found'), 404);
        }

        $this->AdvancedVarianceApprovalRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AdvancedVarianceApproval deleted successfully');
    }
}
