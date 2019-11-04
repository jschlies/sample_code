<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Api\CreateFavoriteGroupRequest;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\FavoriteGroup;
use App\Waypoint\Repositories\FavoriteRepository;
use App\Waypoint\Repositories\FavoriteGroupRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Collection;

/**
 * Class FavoriteGroupController
 * @codeCoverageIgnore
 */
class FavoriteGroupDeprecatedController extends BaseApiController
{
    /** @var  FavoriteRepository */
    private $FavoriteGroupRepositoryObj;

    /**
     * FavoriteGroupDeprecatedController constructor.
     * @param FavoriteGroupRepository $FavoriteGroupRepositoryObj
     */
    public function __construct(FavoriteGroupRepository $FavoriteGroupRepositoryObj)
    {
        $this->FavoriteGroupRepositoryObj = $FavoriteGroupRepositoryObj;
        parent::__construct($FavoriteGroupRepositoryObj);
    }

    /**
     * Display a listing of the AccessListUser.
     * GET|HEAD /favoriteGroups
     *
     * @param CreateFavoriteGroupRequest $CreateFavoriteGroupApiRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(CreateFavoriteGroupRequest $CreateFavoriteGroupApiRequestObj)
    {
        $this->FavoriteGroupRepositoryObj->pushCriteria(new RequestCriteria($CreateFavoriteGroupApiRequestObj));
        $this->FavoriteGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($CreateFavoriteGroupApiRequestObj));
        $accessListUsers = $this->FavoriteGroupRepositoryObj->all();

        return $this->sendResponse($accessListUsers, 'ImageGroup(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param CreateFavoriteGroupRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getFavoriteGroupsForClient($client_id, CreateFavoriteGroupRequest $request)
    {
        $this->FavoriteGroupRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->FavoriteGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $FavoriteGroupObjArr = new Collection();

        /** @var FavoriteGroup $FavoriteGroupObj */
        foreach ($this->FavoriteGroupRepositoryObj->findWhere(
            [
                'name' => EntityTag::FAVORITES,
            ]
        ) as $FavoriteGroupObj)
        {
            $FavoriteGroupObj->setClientId($client_id);
            $FavoriteGroupObjArr[] = $FavoriteGroupObj;
        }

        return $this->sendResponse($FavoriteGroupObjArr, 'FavoriteGroup(s) (Client) retrieved successfully');
    }

    /**
     * @param integer $user_id
     * @param CreateFavoriteGroupRequest $request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getFavoriteGroupsForUser($user_id, CreateFavoriteGroupRequest $request)
    {
        $this->FavoriteGroupRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->FavoriteGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $FavoriteGroupObjArr = new Collection();

        /** @var FavoriteGroup $FavoriteGroupObj */
        foreach ($this->FavoriteGroupRepositoryObj->findWhere(
            [
                'name' => EntityTag::FAVORITES,
            ]
        ) as $FavoriteGroupObj)
        {
            $FavoriteGroupObj->setUserId($user_id);
            $FavoriteGroupObjArr[] = $FavoriteGroupObj;
        }

        return $this->sendResponse($FavoriteGroupObjArr, 'FavoriteGroup(s) (User) retrieved successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function getAvailable()
    {
        return $this->sendResponse(
            [
                "Available" => FavoriteGroup::$favorite_values,
            ],
            'Favorite(s) Available'
        );
    }
}
