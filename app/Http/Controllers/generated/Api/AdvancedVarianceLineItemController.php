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

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceLineItemRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceLineItemRequest;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceLineItemController
 */
final class AdvancedVarianceLineItemController extends BaseApiController
{
    /** @var  AdvancedVarianceLineItemRepository */
    private $AdvancedVarianceLineItemRepositoryObj;

    public function __construct(AdvancedVarianceLineItemRepository $AdvancedVarianceLineItemRepositoryObj)
    {
        $this->AdvancedVarianceLineItemRepositoryObj = $AdvancedVarianceLineItemRepositoryObj;
        parent::__construct($AdvancedVarianceLineItemRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVarianceLineItem.
     * GET|HEAD /advancedVarianceLineItems
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->AdvancedVarianceLineItemRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceLineItemRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceLineItemObjArr = $this->AdvancedVarianceLineItemRepositoryObj->all();

        return $this->sendResponse($AdvancedVarianceLineItemObjArr, 'AdvancedVarianceLineItem(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVarianceLineItem in storage.
     *
     * @param CreateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj)
    {
        $input = $AdvancedVarianceLineItemRequestObj->all();

        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceLineItemObj, 'AdvancedVarianceLineItem saved successfully');
    }

    /**
     * Display the specified AdvancedVarianceLineItem.
     * GET|HEAD /advancedVarianceLineItems/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var AdvancedVarianceLineItem $advancedVarianceLineItem */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceLineItemObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceLineItem not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceLineItemObj, 'AdvancedVarianceLineItem retrieved successfully');
    }

    /**
     * Update the specified AdvancedVarianceLineItem in storage.
     * PUT/PATCH /advancedVarianceLineItems/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateAdvancedVarianceLineItemRequest $AdvancedVarianceLineItemRequestObj)
    {
        $input = $AdvancedVarianceLineItemRequestObj->all();
        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceLineItemObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceLineItem not found'), 404);
        }
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceLineItemObj, 'AdvancedVarianceLineItem updated successfully');
    }

    /**
     * Remove the specified AdvancedVarianceLineItem from storage.
     * DELETE /advancedVarianceLineItems/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceLineItemObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceLineItem not found'), 404);
        }

        $this->AdvancedVarianceLineItemRepositoryObj->delete($id);

        return $this->sendResponse($id, 'AdvancedVarianceLineItem deleted successfully');
    }
}
