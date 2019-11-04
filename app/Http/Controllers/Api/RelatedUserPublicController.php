<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateRelatedUserRequest;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Response;

/**
 * Class RelatedUserPublicController
 */
class RelatedUserPublicController extends BaseApiController
{
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;

    public function __construct(RelatedUserRepository $RelatedUserRepositoryObj)
    {
        $this->RelatedUserRepositoryObj = $RelatedUserRepositoryObj;
        parent::__construct($RelatedUserRepositoryObj);
    }

    /**
     * Display a listing of the RelatedUser.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(['client_id' => $client_id]);
        }
        else
        {
            $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(['client_id' => $client_id])->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }

        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexRelatedUsersForUser(Request $RequestObj, $client_id, $user_id)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(
            [
                'user_id' => $user_id,
            ]
        );

        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexRelatedUsersForProperty(Request $RequestObj, $client_id, $property_id)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $PropertyObj = App::make(PropertyRepository::class)->find($property_id);

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $RelatedUserObjArr = $PropertyObj->getRelatedUsers();
        }
        else
        {
            $RelatedUserObjArr = $PropertyObj->getRelatedUsers()->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }

        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexOpportunities(Request $RequestObj, $client_id, $user_id)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserRepositoryObj = App::make(RelatedUserRepository::class);

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $RelatedUserObjArr = $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(
                [
                    'user_id'              => $user_id,
                    'related_user_type_id' => $RelatedUserRepositoryObj->findWhere(
                        [
                            'client_id'           => $client_id,
                            'related_object_type' => Opportunity::class,
                        ]
                    ),
                ]
            );
        }
        else
        {
            $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(
                [
                    'user_id'              => $user_id,
                    'related_user_type_id' => $RelatedUserRepositoryObj->findWhere(
                        [
                            'client_id'           => $client_id,
                            'related_object_type' => Opportunity::class,
                        ]
                    ),
                ]
            )->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }

        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws RepositoryException
     */
    public function indexAdvancedVariances(Request $RequestObj, $client_id, $user_id)
    {
        $this->RelatedUserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserRepositoryObj = App::make(RelatedUserRepository::class);

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(
                [
                    'user_id'              => $user_id,
                    'related_user_type_id' => $RelatedUserRepositoryObj->findWhere(
                        [
                            'client_id'           => $client_id,
                            'related_object_type' => AdvancedVariance::class,
                        ]
                    ),
                ]
            );
        }
        else
        {
            $RelatedUserObjArr = $this->RelatedUserRepositoryObj->findWhere(
                [
                    'user_id'              => $user_id,
                    'related_user_type_id' => $RelatedUserRepositoryObj->findWhere(
                        [
                            'client_id'           => $client_id,
                            'related_object_type' => AdvancedVariance::class,
                        ]
                    ),
                ]
            )->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }
        return $this->sendResponse($RelatedUserObjArr, 'RelatedUser(s) retrieved successfully');
    }

    /**
     * Store a newly created RelatedUser in storage.
     *
     * @param CreateRelatedUserRequest $RelatedUserRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store(CreateRelatedUserRequest $RelatedUserRequestObj)
    {
        $input          = $RelatedUserRequestObj->all();
        $RelatedUserObj = $this->RelatedUserRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserObj, 'RelatedUser saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $related_user_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $related_user_id)
    {
        /** @var RelatedUser $RelatedUserObj */
        $RelatedUserObj = $this->RelatedUserRepositoryObj->findWithoutFail($related_user_id);
        if (empty($RelatedUserObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUser not found'), 404);
        }
        $RelatedUserObj->delete();

        return $this->sendResponse($related_user_id, 'RelatedUser deleted successfully');
    }
}
