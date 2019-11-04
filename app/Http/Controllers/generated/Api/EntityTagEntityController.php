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

use App\Waypoint\Http\Requests\Generated\Api\CreateEntityTagEntityRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateEntityTagEntityRequest;
use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Repositories\EntityTagEntityRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EntityTagEntityController
 */
final class EntityTagEntityController extends BaseApiController
{
    /** @var  EntityTagEntityRepository */
    private $EntityTagEntityRepositoryObj;

    public function __construct(EntityTagEntityRepository $EntityTagEntityRepositoryObj)
    {
        $this->EntityTagEntityRepositoryObj = $EntityTagEntityRepositoryObj;
        parent::__construct($EntityTagEntityRepositoryObj);
    }

    /**
     * Display a listing of the EntityTagEntity.
     * GET|HEAD /entityTagEntities
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->EntityTagEntityRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EntityTagEntityRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $EntityTagEntityObjArr = $this->EntityTagEntityRepositoryObj->all();

        return $this->sendResponse($EntityTagEntityObjArr, 'EntityTagEntity(s) retrieved successfully');
    }

    /**
     * Store a newly created EntityTagEntity in storage.
     *
     * @param CreateEntityTagEntityRequest $EntityTagEntityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateEntityTagEntityRequest $EntityTagEntityRequestObj)
    {
        $input = $EntityTagEntityRequestObj->all();

        $EntityTagEntityObj = $this->EntityTagEntityRepositoryObj->create($input);

        return $this->sendResponse($EntityTagEntityObj, 'EntityTagEntity saved successfully');
    }

    /**
     * Display the specified EntityTagEntity.
     * GET|HEAD /entityTagEntities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var EntityTagEntity $entityTagEntity */
        $EntityTagEntityObj = $this->EntityTagEntityRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagEntityObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTagEntity not found'), 404);
        }

        return $this->sendResponse($EntityTagEntityObj, 'EntityTagEntity retrieved successfully');
    }

    /**
     * Update the specified EntityTagEntity in storage.
     * PUT/PATCH /entityTagEntities/{id}
     *
     * @param integer $id
     * @param UpdateEntityTagEntityRequest $EntityTagEntityRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateEntityTagEntityRequest $EntityTagEntityRequestObj)
    {
        $input = $EntityTagEntityRequestObj->all();
        /** @var EntityTagEntity $EntityTagEntityObj */
        $EntityTagEntityObj = $this->EntityTagEntityRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagEntityObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTagEntity not found'), 404);
        }
        $EntityTagEntityObj = $this->EntityTagEntityRepositoryObj->update($input, $id);

        return $this->sendResponse($EntityTagEntityObj, 'EntityTagEntity updated successfully');
    }

    /**
     * Remove the specified EntityTagEntity from storage.
     * DELETE /entityTagEntities/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var EntityTagEntity $EntityTagEntityObj */
        $EntityTagEntityObj = $this->EntityTagEntityRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagEntityObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTagEntity not found'), 404);
        }

        $this->EntityTagEntityRepositoryObj->delete($id);

        return $this->sendResponse($id, 'EntityTagEntity deleted successfully');
    }
}
