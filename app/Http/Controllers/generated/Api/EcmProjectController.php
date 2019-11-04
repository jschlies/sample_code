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

use App\Waypoint\Http\Requests\Generated\Api\CreateEcmProjectRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateEcmProjectRequest;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Repositories\EcmProjectRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EcmProjectController
 */
final class EcmProjectController extends BaseApiController
{
    /** @var  EcmProjectRepository */
    private $EcmProjectRepositoryObj;

    public function __construct(EcmProjectRepository $EcmProjectRepositoryObj)
    {
        $this->EcmProjectRepositoryObj = $EcmProjectRepositoryObj;
        parent::__construct($EcmProjectRepositoryObj);
    }

    /**
     * Display a listing of the EcmProject.
     * GET|HEAD /ecmProjects
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->EcmProjectRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EcmProjectRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $EcmProjectObjArr = $this->EcmProjectRepositoryObj->all();

        return $this->sendResponse($EcmProjectObjArr, 'EcmProject(s) retrieved successfully');
    }

    /**
     * Store a newly created EcmProject in storage.
     *
     * @param CreateEcmProjectRequest $EcmProjectRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateEcmProjectRequest $EcmProjectRequestObj)
    {
        $input = $EcmProjectRequestObj->all();

        $EcmProjectObj = $this->EcmProjectRepositoryObj->create($input);

        return $this->sendResponse($EcmProjectObj, 'EcmProject saved successfully');
    }

    /**
     * Display the specified EcmProject.
     * GET|HEAD /ecmProjects/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var EcmProject $ecmProject */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }

        return $this->sendResponse($EcmProjectObj, 'EcmProject retrieved successfully');
    }

    /**
     * Update the specified EcmProject in storage.
     * PUT/PATCH /ecmProjects/{id}
     *
     * @param integer $id
     * @param UpdateEcmProjectRequest $EcmProjectRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateEcmProjectRequest $EcmProjectRequestObj)
    {
        $input = $EcmProjectRequestObj->all();
        /** @var EcmProject $EcmProjectObj */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }
        $EcmProjectObj = $this->EcmProjectRepositoryObj->update($input, $id);

        return $this->sendResponse($EcmProjectObj, 'EcmProject updated successfully');
    }

    /**
     * Remove the specified EcmProject from storage.
     * DELETE /ecmProjects/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var EcmProject $EcmProjectObj */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }

        $this->EcmProjectRepositoryObj->delete($id);

        return $this->sendResponse($id, 'EcmProject deleted successfully');
    }
}
