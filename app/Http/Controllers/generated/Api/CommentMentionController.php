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

use App\Waypoint\Http\Requests\Generated\Api\CreateCommentMentionRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCommentMentionRequest;
use App\Waypoint\Models\CommentMention;
use App\Waypoint\Repositories\CommentMentionRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CommentMentionController
 */
final class CommentMentionController extends BaseApiController
{
    /** @var  CommentMentionRepository */
    private $CommentMentionRepositoryObj;

    public function __construct(CommentMentionRepository $CommentMentionRepositoryObj)
    {
        $this->CommentMentionRepositoryObj = $CommentMentionRepositoryObj;
        parent::__construct($CommentMentionRepositoryObj);
    }

    /**
     * Display a listing of the CommentMention.
     * GET|HEAD /commentMentions
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->CommentMentionRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CommentMentionRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CommentMentionObjArr = $this->CommentMentionRepositoryObj->all();

        return $this->sendResponse($CommentMentionObjArr, 'CommentMention(s) retrieved successfully');
    }

    /**
     * Store a newly created CommentMention in storage.
     *
     * @param CreateCommentMentionRequest $CommentMentionRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreateCommentMentionRequest $CommentMentionRequestObj)
    {
        $input = $CommentMentionRequestObj->all();

        $CommentMentionObj = $this->CommentMentionRepositoryObj->create($input);

        return $this->sendResponse($CommentMentionObj, 'CommentMention saved successfully');
    }

    /**
     * Display the specified CommentMention.
     * GET|HEAD /commentMentions/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var CommentMention $commentMention */
        $CommentMentionObj = $this->CommentMentionRepositoryObj->findWithoutFail($id);
        if (empty($CommentMentionObj))
        {
            return Response::json(ResponseUtil::makeError('CommentMention not found'), 404);
        }

        return $this->sendResponse($CommentMentionObj, 'CommentMention retrieved successfully');
    }

    /**
     * Update the specified CommentMention in storage.
     * PUT/PATCH /commentMentions/{id}
     *
     * @param integer $id
     * @param UpdateCommentMentionRequest $CommentMentionRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdateCommentMentionRequest $CommentMentionRequestObj)
    {
        $input = $CommentMentionRequestObj->all();
        /** @var CommentMention $CommentMentionObj */
        $CommentMentionObj = $this->CommentMentionRepositoryObj->findWithoutFail($id);
        if (empty($CommentMentionObj))
        {
            return Response::json(ResponseUtil::makeError('CommentMention not found'), 404);
        }
        $CommentMentionObj = $this->CommentMentionRepositoryObj->update($input, $id);

        return $this->sendResponse($CommentMentionObj, 'CommentMention updated successfully');
    }

    /**
     * Remove the specified CommentMention from storage.
     * DELETE /commentMentions/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var CommentMention $CommentMentionObj */
        $CommentMentionObj = $this->CommentMentionRepositoryObj->findWithoutFail($id);
        if (empty($CommentMentionObj))
        {
            return Response::json(ResponseUtil::makeError('CommentMention not found'), 404);
        }

        $this->CommentMentionRepositoryObj->delete($id);

        return $this->sendResponse($id, 'CommentMention deleted successfully');
    }
}
