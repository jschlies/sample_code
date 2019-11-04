<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\AccessListSummary;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AccessListSummaryRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class AccessListSummaryController
 * @codeCoverageIgnore
 */
class AccessListSummaryDeprecatedController extends BaseApiController
{
    /** @var  AccessListSummaryRepository */
    private $AccessListSummaryRepositoryObj;

    public function __construct(AccessListSummaryRepository $AccessListSummaryRepository)
    {
        $this->AccessListSummaryRepositoryObj = $AccessListSummaryRepository;
        parent::__construct($AccessListSummaryRepository);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request)
    {
        $this->AccessListSummaryRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->AccessListSummaryRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $AccessListSummaryObjArr = $this->AccessListSummaryRepositoryObj->all();

        return $this->sendResponse($AccessListSummaryObjArr, 'AccessListsSummary(s) retrieved successfully');
    }

    /**
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function getAccessListSummaryForUser($user_id)
    {
        $UserRepositoryObj = App::make(UserRepository::class);
        /** @var User $UserObj */
        $UserObj = $UserRepositoryObj
            ->with('accessListSummaries')
            ->findWithoutFail($user_id);
        if (empty($UserObj))
        {
            return Response::json(ResponseUtil::makeError('User not found'), 404);
        }

        return $this->sendResponse($UserObj->accessListSummaries->toArray(), 'AccessListSummary(s) retrieved successfully');
    }

    /**
     * Display the specified AccessList.
     * GET|HEAD /accessListsSummary/{id}
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($id)
    {
        /** @var AccessListSummary $AccessListSummaryObj */
        $AccessListSummaryObj = $this->AccessListSummaryRepositoryObj->findWithoutFail($id);
        if (empty($AccessListSummaryObj))
        {
            return \Response::json(ResponseUtil::makeError('AccessListSummary not found'), 404);
        }

        return $this->sendResponse($AccessListSummaryObj, 'AccessListSummary retrieved successfully');
    }
}
