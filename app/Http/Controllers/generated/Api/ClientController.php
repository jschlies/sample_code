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

use App\Waypoint\Http\Requests\Generated\Api\CreateClientRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateClientRequest;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\ClientRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ClientController
 */
final class ClientController extends BaseApiController
{
    /** @var  ClientRepository */
    private $ClientRepositoryObj;

    public function __construct(ClientRepository $ClientRepositoryObj)
    {
        $this->ClientRepositoryObj = $ClientRepositoryObj;
        parent::__construct($ClientRepositoryObj);
    }

    /**
     * Display a listing of the Client.
     * GET|HEAD /clients
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ClientObjArr = $this->ClientRepositoryObj->all();

        return $this->sendResponse($ClientObjArr, 'Client(s) retrieved successfully');
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param CreateClientRequest $ClientRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateClientRequest $ClientRequestObj)
    {
        $input = $ClientRequestObj->all();

        $ClientObj = $this->ClientRepositoryObj->create($input);

        return $this->sendResponse($ClientObj, 'Client saved successfully');
    }

    /**
     * Display the specified Client.
     * GET|HEAD /clients/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var Client $client */
        $ClientObj = $this->ClientRepositoryObj->findWithoutFail($id);
        if (empty($ClientObj))
        {
            return Response::json(ResponseUtil::makeError('Client not found'), 404);
        }

        return $this->sendResponse($ClientObj, 'Client retrieved successfully');
    }

    /**
     * Update the specified Client in storage.
     * PUT/PATCH /clients/{id}
     *
     * @param integer $id
     * @param UpdateClientRequest $ClientRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateClientRequest $ClientRequestObj)
    {
        $input = $ClientRequestObj->all();
        /** @var Client $ClientObj */
        $ClientObj = $this->ClientRepositoryObj->findWithoutFail($id);
        if (empty($ClientObj))
        {
            return Response::json(ResponseUtil::makeError('Client not found'), 404);
        }
        $ClientObj = $this->ClientRepositoryObj->update($input, $id);

        return $this->sendResponse($ClientObj, 'Client updated successfully');
    }

    /**
     * Remove the specified Client from storage.
     * DELETE /clients/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var Client $ClientObj */
        $ClientObj = $this->ClientRepositoryObj->findWithoutFail($id);
        if (empty($ClientObj))
        {
            return Response::json(ResponseUtil::makeError('Client not found'), 404);
        }

        $this->ClientRepositoryObj->delete($id);

        return $this->sendResponse($id, 'Client deleted successfully');
    }
}
