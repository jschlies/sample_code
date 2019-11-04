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

use App\Waypoint\Http\Requests\Generated\Api\CreatePasswordRuleRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePasswordRuleRequest;
use App\Waypoint\Models\PasswordRule;
use App\Waypoint\Repositories\PasswordRuleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PasswordRuleController
 */
final class PasswordRuleController extends BaseApiController
{
    /** @var  PasswordRuleRepository */
    private $PasswordRuleRepositoryObj;

    public function __construct(PasswordRuleRepository $PasswordRuleRepositoryObj)
    {
        $this->PasswordRuleRepositoryObj = $PasswordRuleRepositoryObj;
        parent::__construct($PasswordRuleRepositoryObj);
    }

    /**
     * Display a listing of the PasswordRule.
     * GET|HEAD /passwordRules
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PasswordRuleRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PasswordRuleRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PasswordRuleObjArr = $this->PasswordRuleRepositoryObj->all();

        return $this->sendResponse($PasswordRuleObjArr, 'PasswordRule(s) retrieved successfully');
    }

    /**
     * Store a newly created PasswordRule in storage.
     *
     * @param CreatePasswordRuleRequest $PasswordRuleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function store(CreatePasswordRuleRequest $PasswordRuleRequestObj)
    {
        $input = $PasswordRuleRequestObj->all();

        $PasswordRuleObj = $this->PasswordRuleRepositoryObj->create($input);

        return $this->sendResponse($PasswordRuleObj, 'PasswordRule saved successfully');
    }

    /**
     * Display the specified PasswordRule.
     * GET|HEAD /passwordRules/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function show($id)
    {
        /** @var PasswordRule $passwordRule */
        $PasswordRuleObj = $this->PasswordRuleRepositoryObj->findWithoutFail($id);
        if (empty($PasswordRuleObj))
        {
            return Response::json(ResponseUtil::makeError('PasswordRule not found'), 404);
        }

        return $this->sendResponse($PasswordRuleObj, 'PasswordRule retrieved successfully');
    }

    /**
     * Update the specified PasswordRule in storage.
     * PUT/PATCH /passwordRules/{id}
     *
     * @param integer $id
     * @param UpdatePasswordRuleRequest $PasswordRuleRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws Exception
     */
    public function update($id, UpdatePasswordRuleRequest $PasswordRuleRequestObj)
    {
        $input = $PasswordRuleRequestObj->all();
        /** @var PasswordRule $PasswordRuleObj */
        $PasswordRuleObj = $this->PasswordRuleRepositoryObj->findWithoutFail($id);
        if (empty($PasswordRuleObj))
        {
            return Response::json(ResponseUtil::makeError('PasswordRule not found'), 404);
        }
        $PasswordRuleObj = $this->PasswordRuleRepositoryObj->update($input, $id);

        return $this->sendResponse($PasswordRuleObj, 'PasswordRule updated successfully');
    }

    /**
     * Remove the specified PasswordRule from storage.
     * DELETE /passwordRules/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws Exception
     */
    public function destroy($id)
    {
        /** @var PasswordRule $PasswordRuleObj */
        $PasswordRuleObj = $this->PasswordRuleRepositoryObj->findWithoutFail($id);
        if (empty($PasswordRuleObj))
        {
            return Response::json(ResponseUtil::makeError('PasswordRule not found'), 404);
        }

        $this->PasswordRuleRepositoryObj->delete($id);

        return $this->sendResponse($id, 'PasswordRule deleted successfully');
    }
}
