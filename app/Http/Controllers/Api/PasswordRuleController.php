<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\AuthenticatingEntityRepository;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Repositories\PasswordRuleRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class PasswordRuleController
 */
class PasswordRuleController extends BaseApiController
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

        /**
         * remember that
         * $this->PasswordRuleRepositoryObj->>get_password_rules($AuthenticatingEntity->identity_connection);
         * calls auth0 and that call requires $identity_connection so we loop through the hermes $AuthenticatingEntityObjArr
         */
        $AuthenticatingEntityReposoitory = App::make(AuthenticatingEntityRepository::class);
        $PasswordRulesObjArr             =
            $AuthenticatingEntityReposoitory
                ->all()
                ->map(
                    function ($AuthenticatingEntity)
                    {
                        return $this->PasswordRuleRepositoryObj
                            ->get_password_rules(
                                $AuthenticatingEntity->identity_connection
                            );
                    }
                )->flatten();

        /**
         * See HER-3176
         */
        return $this->sendResponse(collect_waypoint($PasswordRulesObjArr), 'PasswordRule(s) retrieved successfully');
    }
}
