<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Repositories\PasswordRuleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class PasswordRuleController
 * @codeCoverageIgnore
 */
class PasswordRuleDeprecatedController extends BaseApiController
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
     * @throws \Exception
     */
    public function index(Request $RequestObj)
    {
        $this->PasswordRuleRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PasswordRuleRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        return $this->sendResponse($this->PasswordRuleRepositoryObj->get_password_rules(), 'PasswordRule(s) retrieved successfully');
    }
}
