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

use App\Waypoint\Http\Requests\Generated\Api\CreateLeaseRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateLeaseRequest;
use App\Waypoint\Models\Lease;
use App\Waypoint\Repositories\LeaseRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class LeaseController
 */
final class LeaseController extends BaseApiController
{
    /** @var  LeaseRepository */
    private $LeaseRepositoryObj;

    public function __construct(LeaseRepository $LeaseRepositoryObj)
    {
        $this->LeaseRepositoryObj = $LeaseRepositoryObj;
        parent::__construct($LeaseRepositoryObj);
    }

    /**
     * Display a listing of the Lease.
     * GET|HEAD /leases
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->LeaseRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->LeaseRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $LeaseObjArr = $this->LeaseRepositoryObj->all();

        return $this->sendResponse($LeaseObjArr, 'Lease(s) retrieved successfully');
    }

    /**
     * Store a newly created Lease in storage.
     *
     * @param CreateLeaseRequest $LeaseRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateLeaseRequest $LeaseRequestObj)
    {
        $input = $LeaseRequestObj->all();

        $LeaseObj = $this->LeaseRepositoryObj->create($input);

        return $this->sendResponse($LeaseObj, 'Lease saved successfully');
    }

    /**
     * Display the specified Lease.
     * GET|HEAD /leases/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Lease $lease */
        $LeaseObj = $this->LeaseRepositoryObj->findWithoutFail($id);
        if (empty($LeaseObj))
        {
            return Response::json(ResponseUtil::makeError('Lease not found'), 404);
        }

        return $this->sendResponse($LeaseObj, 'Lease retrieved successfully');
    }

    /**
     * Update the specified Lease in storage.
     * PUT/PATCH /leases/{id}
     *
     * @param integer $id
     * @param UpdateLeaseRequest $LeaseRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateLeaseRequest $LeaseRequestObj)
    {
        $input = $LeaseRequestObj->all();
        /** @var Lease $LeaseObj */
        $LeaseObj = $this->LeaseRepositoryObj->findWithoutFail($id);
        if (empty($LeaseObj))
        {
            return Response::json(ResponseUtil::makeError('Lease not found'), 404);
        }
        $LeaseObj = $this->LeaseRepositoryObj->update($input, $id);

        return $this->sendResponse($LeaseObj, 'Lease updated successfully');
    }

    /**
     * Remove the specified Lease from storage.
     * DELETE /leases/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Lease $LeaseObj */
        $LeaseObj = $this->LeaseRepositoryObj->findWithoutFail($id);
        if (empty($LeaseObj))
        {
            return Response::json(ResponseUtil::makeError('Lease not found'), 404);
        }

        $this->LeaseRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Lease deleted successfully');
    }
}
