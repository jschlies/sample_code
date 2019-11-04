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

use App\Waypoint\Http\Requests\Generated\Api\CreateEntityTagRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateEntityTagRequest;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Repositories\EntityTagRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EntityTagController
 */
final class EntityTagController extends BaseApiController
{
    /** @var  EntityTagRepository */
    private $EntityTagRepositoryObj;

    public function __construct(EntityTagRepository $EntityTagRepositoryObj)
    {
        $this->EntityTagRepositoryObj = $EntityTagRepositoryObj;
        parent::__construct($EntityTagRepositoryObj);
    }

    /**
     * Display a listing of the EntityTag.
     * GET|HEAD /entityTags
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->EntityTagRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EntityTagRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $EntityTagObjArr = $this->EntityTagRepositoryObj->all();

        return $this->sendResponse($EntityTagObjArr, 'EntityTag(s) retrieved successfully');
    }

    /**
     * Store a newly created EntityTag in storage.
     *
     * @param CreateEntityTagRequest $EntityTagRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateEntityTagRequest $EntityTagRequestObj)
    {
        $input = $EntityTagRequestObj->all();

        $EntityTagObj = $this->EntityTagRepositoryObj->create($input);

        return $this->sendResponse($EntityTagObj, 'EntityTag saved successfully');
    }

    /**
     * Display the specified EntityTag.
     * GET|HEAD /entityTags/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var EntityTag $entityTag */
        $EntityTagObj = $this->EntityTagRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTag not found'), 404);
        }

        return $this->sendResponse($EntityTagObj, 'EntityTag retrieved successfully');
    }

    /**
     * Update the specified EntityTag in storage.
     * PUT/PATCH /entityTags/{id}
     *
     * @param integer $id
     * @param UpdateEntityTagRequest $EntityTagRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateEntityTagRequest $EntityTagRequestObj)
    {
        $input = $EntityTagRequestObj->all();
        /** @var EntityTag $EntityTagObj */
        $EntityTagObj = $this->EntityTagRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTag not found'), 404);
        }
        $EntityTagObj = $this->EntityTagRepositoryObj->update($input, $id);

        return $this->sendResponse($EntityTagObj, 'EntityTag updated successfully');
    }

    /**
     * Remove the specified EntityTag from storage.
     * DELETE /entityTags/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var EntityTag $EntityTagObj */
        $EntityTagObj = $this->EntityTagRepositoryObj->findWithoutFail($id);
        if (empty($EntityTagObj))
        {
            return Response::json(ResponseUtil::makeError('EntityTag not found'), 404);
        }

        $this->EntityTagRepositoryObj->delete($id);

        return $this->sendResponse($id, 'EntityTag deleted successfully');
    }
}
