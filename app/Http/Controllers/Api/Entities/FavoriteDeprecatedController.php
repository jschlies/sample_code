<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Api\CreateFavoriteRequest;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Repositories\FavoriteRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use InfyOm\Generator\Utils\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class FavoriteDeprecatedController
 * @codeCoverageIgnore
 */
class FavoriteDeprecatedController extends BaseApiController
{
    /** @var  FavoriteRepository */
    private $FavoriteRepositoryObj;

    /**
     * FavoriteDeprecatedController constructor.
     * @param FavoriteRepository $FavoriteRepository
     */
    public function __construct(FavoriteRepository $FavoriteRepository)
    {
        $this->FavoriteRepositoryObj = $FavoriteRepository;
        parent::__construct($FavoriteRepository);
    }

    /**
     * Display a listing of the AccessListUser.
     * GET|HEAD /favorites
     *
     * @param CreateFavoriteRequest $CreateFavoritesGroupApiRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(CreateFavoriteRequest $CreateFavoritesGroupApiRequestObj)
    {
        $this->FavoriteRepositoryObj->pushCriteria(new RequestCriteria($CreateFavoritesGroupApiRequestObj));
        $this->FavoriteRepositoryObj->pushCriteria(new LimitOffsetCriteria($CreateFavoritesGroupApiRequestObj));
        $FavoriteObjArr = $this->FavoriteRepositoryObj->all();

        return $this->sendResponse($FavoriteObjArr, 'Favorite(s) retrieved successfully');
    }

    /**
     * @param integer $favorite_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function show($favorite_id)
    {
        /** @var Favorite $FavoriteObj */
        if ( ! $FavoriteObj = $this->FavoriteRepositoryObj->find($favorite_id))
        {
            return Response::json(ResponseUtil::makeError('Favorite not found'), 404);
        }
        return $this->sendResponse($FavoriteObj, 'Favorite retrieved successfully');
    }

    /**
     * Store a newly created Favorite in storage.
     * POST /favorites
     *
     * @param CreateFavoriteRequest $FavoriteRepositoryRequest
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function store(CreateFavoriteRequest $FavoriteRepositoryRequest)
    {
        $input       = $FavoriteRepositoryRequest->all();
        $FavoriteObj = $this->FavoriteRepositoryObj->create($input);

        event(
            new PreCalcUsersEvent(
                $FavoriteObj->get_thing_pointed_at()->client,
                [
                    'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($FavoriteObj),
                    'event_trigger_object_class_id' => $FavoriteObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'users' => [$FavoriteObj->user->id],
                        ],
                ]
            )
        );

        return $this->sendResponse($FavoriteObj, 'Favorite saved successfully');
    }

    /**
     * Remove the specified Favorite from storage.
     * DELETE /favorites/{favorite_id}
     *
     * @param integer $favorite_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function destroy($favorite_id)
    {
        /** @var Favorite $FavoriteObj */
        $FavoriteObj = $this->FavoriteRepositoryObj->findWithoutFail($favorite_id);
        $FavoriteObj->delete();

        event(
            new PreCalcUsersEvent(
                $FavoriteObj->get_thing_pointed_at()->client,
                [
                    'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($FavoriteObj),
                    'event_trigger_object_class_id' => $FavoriteObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'users' => [$FavoriteObj->user->id],
                        ],
                ]
            )
        );

        return $this->sendResponse($favorite_id, 'Favorite deleted successfully');
    }

    /**
     * get available types of favorites
     * GET favoriteGroups/available
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function getAvailable()
    {
        return $this->sendResponse(EntityTag::$favorite_values, 'Favorite(s) Available');
    }
}
