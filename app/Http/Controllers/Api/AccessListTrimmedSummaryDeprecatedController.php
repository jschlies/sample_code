<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AccessListTrimmedSummaryRepository;
use App\Waypoint\Repositories\UserRepository;

/**
 * Class AccessListSummaryController
 * @codeCoverageIgnore
 */
class AccessListTrimmedSummaryDeprecatedController extends BaseApiController
{
    /** @var  AccessListTrimmedSummaryRepository */
    private $AccessListTrimmedSummaryRepositoryObj;

    public function __construct(AccessListTrimmedSummaryRepository $AccessListTrimmedSummaryRepository)
    {
        $this->AccessListTrimmedSummaryRepositoryObj = $AccessListTrimmedSummaryRepository;
        parent::__construct($AccessListTrimmedSummaryRepository);
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws App\Waypoint\Exceptions\GeneralException
     *
     * @todo non-standard response = fix me
     */
    public function getAccessListsPerUserForGivenClient($client_id)
    {
        $UserRepositoryObj         = App::make(UserRepository::class);
        $AccessListSummaryObjArray = [];

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $UserObjArr =
                $UserRepositoryObj
                    ->with('accessListTrimmedSummaries')
                    ->findWhere(['client_id' => $client_id]);
        }
        else
        {
            $UserObjArr =
                $UserRepositoryObj
                    ->with('accessListTrimmedSummaries')
                    ->findWhere(['client_id' => $client_id])
                    ->filter(
                        function (User $UserObj)
                        {
                            return ! $UserObj->is_hidden;
                        }
                    );
        }
        /**
         * @todo Make this responce more standard
         * @var User $UserObj
         */
        foreach ($UserObjArr as $UserObj)
        {
            $AccessListSummaryObjArray[$UserObj->id] = $UserObj->accessListTrimmedSummaries->toArray();
        }

        return $this->sendResponse($AccessListSummaryObjArray, 'All users\' access lists provided for this client successfully');
    }
}
