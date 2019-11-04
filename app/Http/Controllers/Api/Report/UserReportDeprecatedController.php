<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App\Waypoint\Collection;
use App\Waypoint\Models\User;
use App\Waypoint\SpreadsheetCollection;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * @codeCoverageIgnore
 */
class UserReportDeprecatedController extends BaseApiController
{
    /**
     * @todo - either merge this (and other Report controllers) into app/Http/Controllers/ApiRequest or
     *       come up w/ a naming system for all controllers
     */
    /** @var  UserRepository */
    private $UserRepositoryObj;

    /**
     * UserReportController constructor.
     * @param UserRepository $UserRepositoryObj
     */
    public function __construct(UserRepository $UserRepositoryObj)
    {
        $this->UserRepositoryObj = $UserRepositoryObj;

        parent::__construct($UserRepositoryObj);
    }

    /**
     * Display a report of the Properties.
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->UserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->UserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var Collection $UserObjArr */
        $UserObjArr = $this->UserRepositoryObj->findByField('client_id', $client_id)->sortBy('name');

        $user_report_line = new  SpreadsheetCollection();
        /** @var User $UserObj */
        foreach ($UserObjArr as $UserObj)
        {
            /**
             * Keep this in sync with UserReportController
             */
            $user_report_line[] = [
                'id'                 => $UserObj->id,
                'name'               => $UserObj->name,
                'email'              => $UserObj->email,
                'active_status'      => $UserObj->active_status,
                'active_status_date' => $UserObj->active_status_date,
                'roles'              => $UserObj->getRolesAsString(),
                'access_lists'       => $UserObj->getAccessListsAsString(),
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($user_report_line, 'user(s) data retrieved successfully');
        }
        $user_report_line->toCSVReport(
            $this->UserRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s')
        );
    }
}
