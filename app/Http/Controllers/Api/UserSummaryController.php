<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\UpdateUserRequest;
use App\Waypoint\Model;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserSummary;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\UserFlatRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Repositories\UserSummaryRepository;
use App\Waypoint\ResponseUtil;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Response;

/**
 * Class UserSummaryController
 */
class UserSummaryController extends BaseApiController
{
    /** @var  UserRepository */
    private $UserRepositoryObj;
    /** @var  UserSummaryRepository */
    private $UserSummaryRepositoryObj;
    /** @var  UserFlatRepository */
    private $UserFlatRepositoryObj;

    public function __construct(UserSummaryRepository $UserSummaryRepository)
    {
        $this->UserSummaryRepositoryObj = $UserSummaryRepository;
        $this->UserRepositoryObj        = App::make(UserRepository::class);
        $this->UserFlatRepositoryObj    = App::make(UserFlatRepository::class);
        parent::__construct($UserSummaryRepository);
    }

    /**
     * @param Request $Request
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws RepositoryException
     */
    public function index(Request $Request, $client_id)
    {
        $this->UserSummaryRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->UserSummaryRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $UserSummaryObjArr = $this->UserSummaryRepositoryObj->findWhere(['client_id' => $client_id]);
        }
        else
        {
            $UserSummaryObjArr = $this->UserSummaryRepositoryObj->findWhere(['client_id' => $client_id])->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }
        return $this->sendResponse($UserSummaryObjArr, 'UserSummary(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $user_id)
    {
        /** @var UserSummary $UserSummaryObj */
        $UserSummaryObj = $this->UserSummaryRepositoryObj->find($user_id);
        if (empty($UserSummaryObj))
        {
            return Response::json(ResponseUtil::makeError('UserSummary not found'), 404);
        }

        return $this->sendResponse($UserSummaryObj, 'UserSummary retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return JsonResponse
     * @throws GeneralException
     */
    public function indexForClient($client_id)
    {
        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $return_me = $this->UserSummaryRepositoryObj
                ->findWhere(['client_id' => $client_id])->toArray();
        }
        else
        {
            $minutes   = config('cache.cache_on', false)
                ? config('cache.cache_tags.Client.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
                :
                0;
            $key = 'indexForClient_client_id_' . $client_id.'_'.md5(__FILE__.__LINE__);
            $return_me =
                Cache::tags([
                                'Client_' . $client_id,
                                'User_' . $client_id,
                                'Non-Session',
                            ])
                     ->remember(
                         $key,
                         $minutes,
                         function () use ($client_id)
                         {
                             $UserSummaryRepositoryObj = App::make(UserSummaryRepository::class);
                             $UserSummaryObjArr        = $UserSummaryRepositoryObj->findWhere(['client_id' => $client_id, 'is_hidden' => false]);
                             return $UserSummaryObjArr->toArray();
                         }
                     );
        }
        return $this->sendResponse($return_me, 'UserSummary(s) retrieved successfully');
    }

    /**
     * @param UpdateUserRequest $UpdateUserRequest
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @todo push this logic into UserRepositoryObj
     */
    public function deactivateUsers($client_id, UpdateUserRequest $UpdateUserRequest)
    {
        $input = $UpdateUserRequest->all();

        $client_id         = isset($input['client_id']) ? $input['client_id'] : null;
        $email             = isset($input['email']) ? $input['email'] : null;
        $email_regex       = isset($input['email_regex']) ? $input['email_regex'] : null;
        $delete_from_auth0 = isset($input['delete_from_auth0']) ? $input['delete_from_auth0'] : null;
        $dry_run           = isset($input['dry_run']) ? $input['dry_run'] : null;

        if ($email && $email_regex)
        {
            throw new GeneralException("cannot pass email and email_regex");
        }
        if ( ! $email && ! $email_regex)
        {
            throw new GeneralException("must pass email or email_regex");
        }
        if ($email_regex && $email_regex[0] !== $email_regex[strlen($email_regex) - 1])
        {
            throw new GeneralException("email_regex must include delimiters");
        }
        if ($email_regex && preg_match($email_regex, null) === false)
        {
            throw new GeneralException("invalid email_regex");
        }

        $return_me = [];
        /** @var User[] $UsersToDeactivateObjArr */
        $UsersToDeactivateObjArr = $this->UserRepositoryObj->getUsersByClientEmailOrRegex(
            $client_id,
            $email,
            $email_regex
        );
        if (count($UsersToDeactivateObjArr))
        {
            if ($dry_run)
            {
                foreach ($UsersToDeactivateObjArr as $UserToDeactivateObj)
                {
                    $return_me[] = '(Dry run) Marked inactive ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')' . PHP_EOL;
                }
            }
            else
            {
                $this->UserRepositoryObj->deactivateUsers($UsersToDeactivateObjArr);
                foreach ($UsersToDeactivateObjArr as $UserToDeactivateObj)
                {
                    $return_me[] = 'Marked inactive ' . $UserToDeactivateObj->firstname . ' ' . $UserToDeactivateObj->lastname . ' (' . $UserToDeactivateObj->email . ')' . PHP_EOL;
                }
            }
        }

        if ($delete_from_auth0)
        {
            foreach ($this->UserRepositoryObj->getAllUsersFromAuth0() as $Auth0UserObj)
            {
                if (
                    ($email && $email == $Auth0UserObj->email) ||
                    ($email_regex && preg_match($email_regex, $Auth0UserObj->email))
                )
                {
                    if ( ! $this->UserRepositoryObj->findWhere(
                        [
                            ['email', '=', $email,],
                            ['active_status', '=', User::ACTIVE_STATUS_ACTIVE,],
                        ]
                    )->first())
                    {
                        if ($dry_run)
                        {
                            $return_me[] = '(Dry run) Deleted from Auth0 ' . $Auth0UserObj->email . PHP_EOL;

                        }
                        else
                        {
                            $this->UserRepositoryObj->deleteUserFromAuth0($Auth0UserObj->email, $UserToDeactivateObj->authenticatingEntity->identity_connection);
                            $return_me[] = 'Deleted from Auth0 ' . $Auth0UserObj->email . PHP_EOL;
                        }
                    }
                }
            }
        }
        foreach ($UsersToDeactivateObjArr as $UserToDeactivateObj)
        {
            event(
                new App\Waypoint\Events\PreCalcUsersEvent(
                    $UserToDeactivateObj->client,
                    [
                        'event_trigger_message'            => '',
                        'event_trigger_id'                 => waypoint_generate_uuid(),
                        'event_trigger_class'              => self::class,
                        'event_trigger_class_instance'     => get_class($this),
                        'event_trigger_object_class'       => get_class($UserToDeactivateObj),
                        'event_trigger_object_class_id'    => $UserToDeactivateObj->id,
                        'event_trigger_absolute_class'     => __CLASS__,
                        'event_trigger_file'               => __FILE__,
                        'event_trigger_line'               => __LINE__,
                        'launch_job_property_group_id_arr' => [$UserToDeactivateObj->id],
                    ]
                )
            );
        }
        return $this->sendResponse($return_me, 'Users deactivated and/or deleted');
    }

    /**
     * @param $RequestObj
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function downloadUsersForClient(Request $RequestObj, $client_id)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new GeneralException('No such client' . ' ' . __FILE__ . ':' . __LINE__);
        }
        if ($this->getCurrentLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            $UsersCollection = $this->UserRepositoryObj->findWhere(['client_id' => $client_id]);
        }
        else
        {
            $UsersCollection = $this->UserRepositoryObj->findWhere(['client_id' => $client_id])->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );
        }

        /** @var User $UserObj */
        foreach ($UsersCollection as &$UserObj)
        {
            $UserObj->access_list_names_arr = $UserObj->accessLists->pluck('name')->toArray();
        }

        if ($UsersCollection->count() == 0)
        {
            throw new GeneralException('Client has no users' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($UsersCollection->toArray(), 'User(s) retrieved successfully');
        }

        $filename = $ClientObj->name . ' - User Management Export - ' . Carbon::now()->format('Y-m-d H:i:s');
        return Spreadsheet::downloadUserManagementSpreadsheet($UsersCollection->toArray(), $filename);
    }
}
