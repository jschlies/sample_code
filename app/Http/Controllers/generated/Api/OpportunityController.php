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

use App\Waypoint\Http\Requests\Generated\Api\CreateOpportunityRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateOpportunityRequest;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Repositories\OpportunityRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class OpportunityController
 */
final class OpportunityController extends BaseApiController
{
    /** @var  OpportunityRepository */
    private $OpportunityRepositoryObj;

    public function __construct(OpportunityRepository $OpportunityRepositoryObj)
    {
        $this->OpportunityRepositoryObj = $OpportunityRepositoryObj;
        parent::__construct($OpportunityRepositoryObj);
    }

    /**
     * Display a listing of the Opportunity.
     * GET|HEAD /opportunities
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->OpportunityRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->OpportunityRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $OpportunityObjArr = $this->OpportunityRepositoryObj->all();

        return $this->sendResponse($OpportunityObjArr, 'Opportunity(s) retrieved successfully');
    }

    /**
     * Store a newly created Opportunity in storage.
     *
     * @param CreateOpportunityRequest $OpportunityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateOpportunityRequest $OpportunityRequestObj)
    {
        $input = $OpportunityRequestObj->all();

        $OpportunityObj = $this->OpportunityRepositoryObj->create($input);

        return $this->sendResponse($OpportunityObj, 'Opportunity saved successfully');
    }

    /**
     * Display the specified Opportunity.
     * GET|HEAD /opportunities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Opportunity $opportunity */
        $OpportunityObj = $this->OpportunityRepositoryObj->findWithoutFail($id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($OpportunityObj, 'Opportunity retrieved successfully');
    }

    /**
     * Update the specified Opportunity in storage.
     * PUT/PATCH /opportunities/{id}
     *
     * @param integer $id
     * @param UpdateOpportunityRequest $OpportunityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateOpportunityRequest $OpportunityRequestObj)
    {
        $input = $OpportunityRequestObj->all();
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->findWithoutFail($id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }
        $OpportunityObj = $this->OpportunityRepositoryObj->update($input, $id);

        return $this->sendResponse($OpportunityObj, 'Opportunity updated successfully');
    }

    /**
     * Remove the specified Opportunity from storage.
     * DELETE /opportunities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Opportunity $OpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->findWithoutFail($id);
        if (empty($OpportunityObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        $this->OpportunityRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Opportunity deleted successfully');
    }
}
