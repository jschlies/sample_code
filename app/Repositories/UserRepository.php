<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Auth0\Auth0ApiManagementUser;
use App\Waypoint\Collection;
use App\Waypoint\Events\UserCreatedEvent;
use App\Waypoint\Events\UserUpdatedEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\PolicyException;
use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Models\HeartbeatDetail;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Models\UserSummary;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use ArrayObject;
use Auth0\Login\Auth0JWTUser;
use Auth0\Login\Auth0User;
use Auth0\Login\Contract\Auth0UserRepository;
use Auth;
use Cache;
use Carbon\Carbon;
use Cookie;
use DB;
use Exception;
use function preg_match;
use function waypoint_generate_uuid;
use Log;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserRepository extends UserRepositoryBase implements Auth0UserRepository
{
    /** @var Auth0ApiManagementUser|Auth0ApiManagementUserMock */
    private static $Auth0ApiManagementUserObj = null;

    /**
     * @return Auth0ApiManagementUser|Auth0ApiManagementUserMock
     */
    public static function getAuth0ApiManagementUserObj()
    {
        if ( ! self::$Auth0ApiManagementUserObj)
        {
            self::$Auth0ApiManagementUserObj = new Auth0ApiManagementUser();
        }
        return self::$Auth0ApiManagementUserObj;
    }

    /**
     * @param Auth0ApiManagementUser|Auth0ApiManagementUserMock
     */
    public static function setAuth0ApiManagementUserObj($Auth0ApiManagementUserObj)
    {
        self::$Auth0ApiManagementUserObj = $Auth0ApiManagementUserObj;
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return User
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $UserObj                = $this->find($id);
        $original_active_status = $UserObj->active_status;

        $UserObj = parent::update($attributes, $id);

        /**
         * if role = string = users highest role
         * if role is comma delimited string  - all this users roles
         */
        if (isset($attributes['role']))
        {
            $role_arr = explode(',', $attributes['role']);
            if (count($role_arr) == 1)
            {
                if ($role_arr[0] == Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                {
                    $role_arr[] = Role::CLIENT_GENERIC_USER_ROLE;
                }
                elseif ($role_arr[0] == Role::WAYPOINT_ASSOCIATE_ROLE)
                {
                    $role_arr[]   = Role::CLIENT_ADMINISTRATIVE_USER_ROLE;
                    $attributes[] = Role::CLIENT_GENERIC_USER_ROLE;
                }
                elseif ($role_arr[0] == Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)
                {
                    $role_arr[] = Role::WAYPOINT_ASSOCIATE_ROLE;
                    $role_arr[] = Role::CLIENT_ADMINISTRATIVE_USER_ROLE;
                    $role_arr[] = Role::CLIENT_GENERIC_USER_ROLE;
                }
            }
            foreach ($role_arr as $role)
            {
                if ( ! in_array($role, $UserObj->getRoleNamesArr()))
                {
                    $UserObj->attachRole(Role::where('name', $role)->first());
                }
            }
            foreach ($UserObj->getRoleNamesArr() as $role)
            {
                if ( ! in_array($role, $role_arr))
                {
                    $UserObj->detachRole(Role::where('name', $role)->first());

                }
            }
        }

        if (isset($attributes['access_list_id_arr']))
        {
            /**
             * Add if missing '
             *
             * @var AccessListUserRepository $AccessListUserRepositoryObj
             */
            $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
            foreach (explode(',', $attributes['access_list_id_arr']) as $access_list_id)
            {
                $access_list_id = intval($access_list_id);
                if ( ! in_array($access_list_id, $UserObj->accessListUsers->pluck('access_list_id')->toArray()))
                {
                    $AccessListUserRepositoryObj->create(
                        [
                            'user_id'        => $UserObj->id,
                            'access_list_id' => $access_list_id,
                        ]
                    );
                }
            }
            /**
             * delete if not in explode(',', $input['access_list_id_arr'])
             */
            foreach ($UserObj->accessListUsers->pluck('access_list_id')->toArray() as $access_list_id)
            {
                if ( ! in_array($access_list_id, explode(',', $attributes['access_list_id_arr'])))
                {
                    $AccessListUserRepositoryObj->delete($access_list_id);
                }
            }
        }

        foreach (User::$user_notification_flags as $notification_flag)
        {
            if (isset($input[$notification_flag]))
            {
                $UserObj->updateConfig($notification_flag, $attributes[$notification_flag]);
            }
        }

        /**
         * maybe we just updated this guy to ACTIVE
         */
        try
        {
            if (
                $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE &&
                $original_active_status !== $UserObj->active_status &&
                filter_var($UserObj->email, FILTER_VALIDATE_EMAIL) &&
                ! preg_match("/\d$/", $UserObj->email) &&
                ! preg_match("/dummy\.waypointbuilding\.com$/", $UserObj->email)
            )
            {
                /**
                 * looks like that the active_status of user has changed to User::ACTIVE_STATUS_ACTIVE,
                 * let's make sure the user is in Auth0. If no, create him/her
                 */
                if ($UserObj->authenticatingEntity->identity_connection == AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
                {
                    if ( ! $this->getUsersFromAuth0WithEmail($UserObj->email, $UserObj->authenticatingEntity->identity_connection))
                    {
                        $this->addUserToAuth0(
                            $UserObj->id,
                            $UserObj->email,
                            isset($attributes['password']) ? $attributes['password'] : null,
                            $UserObj->authenticatingEntity->identity_connection
                        );
                    }
                }
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 403, $e);
        }

        if ( ! $this->isSuppressEvents())
        {
            /**
             * Note how \App\Waypoint\Events\UserUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            /** @noinspection PhpUndefinedClassInspection */
            event(
                new UserUpdatedEvent(
                    $UserObj,
                    [
                        'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'              => waypoint_generate_uuid(),
                        'event_trigger_class'           => self::class,
                        'event_trigger_class_instance'  => get_class($this),
                        'event_trigger_object_class'    => get_class($UserObj),
                        'event_trigger_object_class_id' => $UserObj->id,
                        'event_trigger_absolute_class'  => __CLASS__,
                        'event_trigger_file'            => __FILE__,
                        'event_trigger_line'            => __LINE__,
                        'wipe_out_list'                 =>
                            [
                                'clients' => [
                                    'relatedUserTypes_client_.*',
                                ],
                                'users'   => [
                                    'assetTypesOfProperties_user_.*',
                                    'accessible_property_arr_user_.*',
                                    'standardAttributesOfProperties_user_.*',
                                    'customAttributesOfProperties_user_',
                                    'AccessiblePropertyObjFormattedArr_user_.*',
                                    'accessiblePropertyGroups_user_.*',
                                    'user_accessable_property_id_arr_.*',
                                ],
                            ],
                        'launch_job_user_id_arr'        => [$UserObj->id],
                    ]
                )
            );
        }
        Cache::tags('User_' . $UserObj->client_id)->flush();

        return $UserObj;
    }

    /**
     * @param int $id
     * @return bool
     * @throws ValidationException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function delete($id)
    {
        /**
         * WE DO NOT DELETE USERS FROM THE HOMESTEAD DB AT THIS TIME. We merely set their
         * active_status = User::ACTIVE_STATUS_INACTIVE
         *
         * However we DO DELETE them from Auth0 if they no longer have
         * an active user record, rememeber multiclient users
         */
        /** @var User $DeactivationCandidateUserObj */
        $DeactivationCandidateUserObj = $this->find($id);
        $DeactivationCandidateUserObj =
            $this->update(
                [
                    'active_status'      => User::ACTIVE_STATUS_INACTIVE,
                    'active_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                $DeactivationCandidateUserObj->id
            );

        if ($DeactivationCandidateUserObj->user_invitation_status == User::USER_INVITATION_STATUS_PENDING)
        {
            $DeactivationCandidateUserObj =
                $this->update(
                    [
                        'user_invitation_status'      => User::USER_INVITATION_STATUS_EXPIRED,
                        'user_invitation_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    ],
                    $DeactivationCandidateUserObj->id
                );
        }

        if (
            $this->findWhere(
                [
                    'email'         => $DeactivationCandidateUserObj->email,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                ]
            )->count() == 0
        )
        {
            if (self::getAuth0ApiManagementUserObj()
                    ->get_user_with_email($DeactivationCandidateUserObj->email, $DeactivationCandidateUserObj->authenticatingEntity->identity_connection))
            {
                self::getAuth0ApiManagementUserObj()
                    ->delete_user_with_email($DeactivationCandidateUserObj->email, $DeactivationCandidateUserObj->authenticatingEntity->identity_connection);
            }
        }

        $ApiKeyRepositoryObj = App::make(ApiKeyRepository::class);
        if ($DeactivationCandidateUserApiKeyObj = $ApiKeyRepositoryObj->findWhere(
            [
                'user_id' => $DeactivationCandidateUserObj->id,
            ]
        )->first())
        {
            $ApiKeyRepositoryObj->delete($DeactivationCandidateUserApiKeyObj->id);
        }

        if ( ! $this->isSuppressEvents())
        {
            /**
             * We NEVER DELETE USERS
             */
        }
        Cache::tags('User_' . $DeactivationCandidateUserObj->client_id)->flush();

        return true;
    }

    /**
     * @param $user_id
     * @param $new_password
     * @return array|bool|\stdClass
     * @throws GeneralException
     */
    public function updatePassword($user_id, $new_password)
    {
        $UserObj = $this->find($user_id);

        return self::getAuth0ApiManagementUserObj()
                   ->update_user_password_with_email(
                       $UserObj->email,
                       $new_password,
                       $UserObj->authenticatingEntity->identity_connection
                   );
    }

    /**
     * @return User|bool|mixed|null
     */
    public function getLoggedInUser()
    {
        $auth0_user_info_arr = App::make('auth0')->getUser();

        if (
            ! $auth0_user_info_arr &&
            ! $Auth0UserObj = $this->getUserByUserInfo($auth0_user_info_arr))
        {
            /**
             * no Auth0 user info available. Perhaps this user has 'logged in' via
             * the apikey login route
             */
            if ($user_id = session('api_key_user_id', null))
            {
                $UserObj =
                    $this->find($user_id);
                return $UserObj;
            }
            return false;
        }

        /**
         * build the user_info_arr
         */
        $Auth0UserObj = $this->getUserByUserInfo($auth0_user_info_arr);

        $lower_case_email = strtolower($Auth0UserObj->getUserInfo()['email']);
        if ($lower_case_email == User::SUPERUSER_EMAIL)
        {
            /** @var Collection $UserObjArr */
            $UserObjArr = $this->findWhere(
                [
                    'email' => User::SUPERUSER_EMAIL,
                ]);
            if ($UserObjArr->count() !== 1)
            {
                /**
                 * multiple superusers!!!!!!!!
                 */
                return false;
            }
            $UserObj = $UserObjArr->first();

            $UserObj->setAuth0UserObj($Auth0UserObj);
            return $UserObj;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        elseif ($client_id = Cookie::get('CLIENT_ID_COOKIE'))
        {
            /** @var Collection $UserObjArr */
            $UserObjArr = $this
                ->with('client')
                ->findWhere(
                    [
                        'client_id'     => $client_id,
                        'email'         => $lower_case_email,
                        'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    ]
                );
            if ($UserObjArr->count() > 1)
            {
                throw new GeneralException('Multiple users for client');
            }
            elseif ($UserObjArr->count() == 1)
            {
                $UserObj = $UserObjArr->first();
                $UserObj->setAuth0UserObj($Auth0UserObj);
                return $UserObj;
            }
        }
        else
        {
            /**
             *
             * hmmm, since no Cookie::get('CLIENT_ID_COOKIE') exists, let's
             * just guess the first $UserObj
             * @var Collection $UserObjArr
             */
            $UserObjArr = $this->findWhere(
                [
                    'email'         => $lower_case_email,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                ]);
            if ($UserObjArr->count() > 0)
            {
                $UserObj = $UserObjArr->first();
                $UserObj->setAuth0UserObj($Auth0UserObj);
                return $UserObj;
            }
        }

        /**
         * We are running this in a batch/unit test context.
         */
        if ($Auth0UserObj = Auth::getUser())
        {
            /**
             * We are running this in a batch/unit test context.
             */
            $UserObjArr = $this->findWhere(
                [
                    'client_id' => $Auth0UserObj->client_id,
                    'email'     => $Auth0UserObj->email,
                ]
            );
            if ($UserObjArr->count() > 0)
            {
                return $UserObjArr[0];
            }
        }
        return false;
    }

    /**
     * @param $jwt
     * @return Auth0JWTUser
     */
    public function getUserByDecodedJWT($jwt)
    {
        return new Auth0JWTUser($jwt);
    }

    /**
     * @param $user_info_arr
     * @return \Auth0\Login\Auth0User
     */
    public function getUserByUserInfo($user_info_arr)
    {
        return new Auth0User($user_info_arr['profile'], $user_info_arr['accessToken']);
    }

    /**
     * @param \Auth0\Login\Contract\ $identifier
     * @return User|null
     */
    public function getUserByIdentifier($identifier)
    {
        /**
         * Get the user_info_arr info of the user_info_arr logged in (probably in session)
         */
        if ( ! $user_info_arr = App::make('auth0')->getUser())
        {
            /**
             * no Auth0 user info available. Perhaps this user has 'logged in' via
             * the apikey login route
             */
            if ($user_id = session('api_key_user_id', null))
            {
                $UserObj = $this->find($user_id);
                return $UserObj;
            }
            return null;
        }
        /**
         * build the user_info_arr
         */
        $auth0User = $this->getUserByUserInfo($user_info_arr);

        /**
         * if it is not the same user_info_arr as logged in, it is not valid
         */
        $AuthIdentifier = $auth0User->getAuthIdentifier();
        if ($auth0User && $AuthIdentifier == $identifier)
        {
            if ($auth0User->getUserInfo()['email'] == User::SUPERUSER_EMAIL)
            {
                /** @var User $UserObj */
                $UserObj = $this->findWhere(
                    [
                        'client_id'     => 1,
                        'email'         => User::SUPERUSER_EMAIL,
                        'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    ]
                )->first();

                $UserObj->setAuth0UserObj($auth0User);
                return $UserObj;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            elseif ($client_id = Cookie::get('CLIENT_ID_COOKIE'))
            {
                /** @var User $UserObj */
                if ($UserObj = $this->findWhere(
                    [
                        'client_id'     => $client_id,
                        'email'         => strtolower($auth0User->getUserInfo()['email']),
                        'active_status' => User::ACTIVE_STATUS_ACTIVE,

                    ]
                )->first()
                )
                {
                    $UserObj->setAuth0UserObj($auth0User);
                    return $UserObj;
                }
            }
            else
            {
                /** @var User $UserObj */
                if ($UserObj = $this->findWhere(
                    [
                        'email'         => strtolower($auth0User->getUserInfo()['email']),
                        'active_status' => User::ACTIVE_STATUS_ACTIVE,

                    ]
                )->first())
                {
                    $UserObj->setAuth0UserObj($auth0User);
                    return $UserObj;
                }
            }
        }
        return null;
    }

    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return User::class;
    }

    /**
     * @param array $attributes
     * @return User|UserSummary|HeartbeatDetail|Heartbeat
     * @throws GeneralException
     * @throws \Exception
     */
    public function create(array $attributes)
    {
        /**
         * @todo Do I really want to beginTransaction? Con consistant w/ other parts of repositories
         */
        DB::beginTransaction();
        try
        {
            if ( ! isset($attributes['client_id']))
            {
                throw new GeneralException('client_id is a required value');
            }
            if (isset($attributes['role']))
            {
                if ( ! $attributes['role'])
                {
                    throw new GeneralException('role cannot be blank');
                }
                foreach (explode(',', $attributes['role']) as $role)
                {
                    if ( ! in_array($role, Role::$waypoint_system_roles))
                    {
                        throw new GeneralException('role is not a valid value');
                    }
                    if ( ! $role == Role::WAYPOINT_ROOT_ROLE)
                    {
                        throw new GeneralException('role is not a valid value');
                    }
                }
            }
            else
            {
                $attributes['role'] = Role::CLIENT_GENERIC_USER_ROLE;
            }

            if ( ! isset($attributes['email']))
            {
                throw new GeneralException('email is required', 403);
            }
            $attributes['email'] = strtolower($attributes['email']);

            if (isset($attributes['send_invitation']) && $attributes['send_invitation'])
            {
                $attributes['user_invitation_status']      = User::USER_INVITATION_STATUS_PENDING;
                $attributes['user_invitation_status_date'] = Carbon::now()->format('Y-m-d H:i:s');

            }
            elseif (isset($attributes['password']) && $attributes['password'])
            {
                $attributes['user_invitation_status']      = User::USER_INVITATION_STATUS_ADDED_VIA_ADMIN;
                $attributes['user_invitation_status_date'] = Carbon::now()->format('Y-m-d H:i:s');
            }
            else
            {
                $attributes['user_invitation_status']      = User::USER_INVITATION_STATUS_NEVER_INVITED;
                $attributes['user_invitation_status_date'] = Carbon::now()->format('Y-m-d H:i:s');
            }

            /**
             * because in MySQL, you can't default a blob
             */
            if ( ! isset($attributes['config_json']) || ! $attributes['config_json'])
            {
                $attributes['config_json'] = json_encode(new ArrayObject());
            }
            if ( ! isset($attributes['image_json']) || ! $attributes['image_json'])
            {
                $attributes['image_json'] = json_encode(new ArrayObject());
            }

            if ($UserObj = $this->findWhere(
                [
                    'client_id' => $attributes['client_id'],
                    'email'     => $attributes['email'],
                ]
            )->first())
            {
                throw new GeneralException('You cannot create a user that exists with this email');
            }
            /**
             * @todo Wierd how 'role' is a beginTransactionram here but not part of the User Model. Maybe this needs to be cleaned up,
             *       maybe I worry too much.
             */

            if ( ! isset($attributes['active_status']) && ! $attributes['active_status'])
            {
                $attributes['active_status'] = User::ACTIVE_STATUS_ACTIVE;
            }

            $AuthenticatingEntityRepositoryObj = App::make(AuthenticatingEntityRepository::class);
            if ( ! isset($attributes['authenticating_entity_id']) || ! $attributes['authenticating_entity_id'])
            {
                $MatchingAuthenticatingEntityObjArr = new Collection();
                foreach ($AuthenticatingEntityRepositoryObj->findWhere(['is_default' => false]) as $AuthenticatingEntityObj)
                {
                    if (preg_match($AuthenticatingEntityObj->email_regex, $attributes['email']))
                    {
                        $MatchingAuthenticatingEntityObjArr[] = $AuthenticatingEntityObj;
                    };
                }
                if ($MatchingAuthenticatingEntityObjArr->count() == 0)
                {
                    $attributes['authenticating_entity_id'] = $AuthenticatingEntityRepositoryObj->findWhere(['is_default' => true])->first()->id;
                }
                elseif ($MatchingAuthenticatingEntityObjArr->count() == 1)
                {
                    $attributes['authenticating_entity_id'] = $MatchingAuthenticatingEntityObjArr->first()->id;
                }
                else
                {
                    throw new GeneralException('AuthenticatingEntity mismatch');
                }
            }
            else
            {
                if ($AuthenticatingEntityRepositoryObj->find($attributes['authenticating_entity_id']))
                {
                    throw new GeneralException('AuthenticatingEntity mismatch');
                }
            }

            /**
             * NOTE NOTE NOTE that this kicks off CalculateVariousPropertyListsJob
             * @var User $UserObj
             */
            $UserObj = parent::create($attributes);

            foreach (explode(',', $attributes['role']) as $role)
            {
                $UserObj->attachRole(Role::where('name', $role)->first(), $this->suppress_events);
            }
            if ( ! $UserObj->hasRole(Role::CLIENT_GENERIC_USER_ROLE))
            {
                /**
                 * everyone gets this role
                 */
                $UserObj->attachRole(Role::where('name', App\Waypoint\Models\Role::CLIENT_GENERIC_USER_ROLE)->first(), $this->suppress_events);
            }

            if ($UserObj->authenticatingEntity->identity_connection == AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
            {
                if ( ! $this->getUsersFromAuth0WithEmail($attributes['email'], $UserObj->authenticatingEntity->identity_connection))
                {
                    $this->addUserToAuth0(
                        $UserObj->id,
                        $UserObj->email,
                        isset($attributes['password']) ? $attributes['password'] : null,
                        $UserObj->authenticatingEntity->identity_connection
                    );
                }
            }

            $UserObj->refresh();

            $UserObj->setAllNotificationConfigs(true);

            $UserObj->updateConfig('USER_PROFILE_NOTIFICATIONS', true);

            $this->initNativeAccountDropdownDefaults($UserObj);

            DB::commit();

            /**
             * since Users is an oddball model. we need to explicitly call UserCreatedEvent
             * @todo test this
             */
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpFullyQualifiedNameUsageInspection */
                event(
                    new UserCreatedEvent(
                        $UserObj,
                        [
                            'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                            'event_trigger_id'              => waypoint_generate_uuid(),
                            'event_trigger_class'           => self::class,
                            'event_trigger_class_instance'  => get_class($this),
                            'event_trigger_object_class'    => get_class($UserObj),
                            'event_trigger_object_class_id' => $UserObj->id,
                            'event_trigger_absolute_class'  => __CLASS__,
                            'event_trigger_file'            => __FILE__,
                            'event_trigger_line'            => __LINE__,
                            'wipe_out_list'                 =>
                                [
                                    'clients'         => [],
                                    'users'           => [],
                                    'properties'      => [],
                                    'property_groups' => [],
                                ],
                        ]
                    )
                );
            }
            Cache::tags('User_' . $UserObj->client_id)->flush();

            return $UserObj;
        }
        catch (GeneralException $e)
        {
            DB::rollBack();
            throw $e;
        }
        catch (Exception $e)
        {
            DB::rollBack();
            throw new GeneralException($e->getMessage(), 403, $e);
        }
    }

    public function generate_one_time_token($user_id)
    {

    }

    /**
     * @return mixed
     */
    public function getAuth0Api()
    {
        return $this->auth0Api;
    }

    /**
     * @param mixed $auth0Api
     */
    public function setAuth0Api($auth0Api)
    {
        $this->auth0Api = $auth0Api;
    }

    /**
     * @param integer $client_id
     * @param string $email
     * @param string $email_regex
     * @return Collection|static
     * @throws GeneralException
     */
    public function getUsersByClientEmailOrRegex($client_id, $email, $email_regex)
    {

        if ($email && $email_regex)
        {
            throw new GeneralException("cannot pass email and email_regex");
        }
        if ($client_id)
        {
            if ($email)
            {
                $UsersToDeleteObjArr = $this->findWhere(
                    [
                        ['email', '=', $email,],
                        ['active_status', '=', User::ACTIVE_STATUS_ACTIVE,],
                        ['client_id', '=', $client_id,],
                    ]
                );
            }
            else
            {
                $UsersToDeleteObjArr = $this->all()->filter(
                    function (User $UserObj) use ($email_regex, $client_id)
                    {
                        return
                            preg_match($email_regex, $UserObj->email) &&
                            $UserObj->client_id == $client_id;
                    }
                );
            }
        }
        else
        {
            if ($email)
            {
                $UsersToDeleteObjArr = $this->findWhere(
                    [
                        ['email', '=', $email,],
                    ]
                );
            }
            else
            {
                $UsersToDeleteObjArr = $this->all()->filter(
                    function (User $UserObj) use ($email_regex, $client_id)
                    {
                        return
                            preg_match($email_regex, $UserObj->email);
                    }
                );
            }
        }
        return $UsersToDeleteObjArr;
    }

    /**
     * @param [] $UsersToDeactivateObjArr
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function deactivateUsers($UsersToDeactivateObjArr)
    {
        foreach ($UsersToDeactivateObjArr as $UsersToDeactivateObj)
        {
            $this->update(
                [
                    'active_status'      => User::ACTIVE_STATUS_INACTIVE,
                    'active_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                $UsersToDeactivateObj->id
            );
        }
    }

    /**
     * @param string $email
     * @throws GeneralException
     */
    public function deleteUserFromAuth0($email, $identity_connection)
    {
        self::getAuth0ApiManagementUserObj()->delete_user_with_email($email, $identity_connection);
    }

    /**
     * @return array
     * @throws GeneralException
     */
    public function getAllUsersFromAuth0(): array
    {
        return self::getAuth0ApiManagementUserObj()->get_all_users();
    }

    /**
     * @param string $email
     * @return array
     * @throws GeneralException
     */
    public function getUsersFromAuth0WithEmail($email, $identity_connection): array
    {
        return self::getAuth0ApiManagementUserObj()->get_user_with_email($email, $identity_connection);
    }

    /**
     * @param integer $client_id
     * @param string $email
     * @return User
     */
    public function getActiveUserWithClientIdAndEmail($client_id, $email)
    {
        return $this->findWhere(
            [
                ['client_id', '=', $client_id],
                ['email', '=', $email],
                ['active_status', '=', User::ACTIVE_STATUS_ACTIVE],
            ]
        )->first();
    }

    /**
     * @param integer $client_id
     * @param string $email
     * @return User
     */
    public function getUserWithClientIdAndEmail($client_id, $email)
    {
        return $this->findWhere(
            [
                ['client_id', '=', $client_id],
                ['email', '=', $email],
            ]
        )->first();
    }

    public function addUserToAuth0($user_id, $email, $password = null, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        if ( ! $password)
        {
            $password = waypoint_generate_uuid();
        }
        /**
         * see https://github.com/auth0/auth0-PHP
         * see https://auth0.com/docs/api/info
         */
        $response = self::getAuth0ApiManagementUserObj()->create_user(
            [
                'email'          => $email,
                'password'       => $password,
                'email_verified' => false,
            ],
            $identity_connection
        );

        /**
         * creation_auth0_response is protected in UserRepository
         * so we update via UserAdminRepository and refresh()
         *
         * be sure to setSuppressEvents if needed
         * @var UserAdminRepository $UserAdminRepositoryObj
         */
        $UserAdminRepositoryObj = App::make(UserAdminRepository::class);
        $UserAdminRepositoryObj->setSuppressEvents($this->suppress_events);
        $UserAdminRepositoryObj->update(
            [
                'creation_auth0_response' => json_encode((array) $response),
            ],
            $user_id
        );
    }

    /**
     * @param $email
     * @return array
     */
    public function generatePasswordToken($email)
    {
        $token = substr(md5($email . config('waypoint.password_change_token_secret_word') . mt_rand()), 0, 20);
        return
            [
                'one_time_token'        => $token,
                'one_time_token_expiry' => Carbon::now()
                                                 ->addSeconds(config('waypoint.password_change_token_ttl', 600))
                                                 ->format('Y-m-d H:i:s'),
            ];
    }

    /**
     * @param $token
     * @return array|bool
     */
    public function validatePasswordToken($token)
    {
        $UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);

        /** @var App\Waypoint\Models\UserInvitation $UserInvitationObj */
        if ( ! $UserInvitationObj = $UserInvitationRepositoryObj->findWhere(['one_time_token' => $token])->first())
        {
            return false;
        }
        if ($UserInvitationObj->one_time_token_expiry->timestamp < Carbon::now()->timestamp)
        {
            return false;
        }
        if ($UserInvitationObj->invitation_status !== UserInvitation::INVITATION_STATUS_PENDING)
        {
            return false;
        }
        return $UserInvitationObj->inviteeUser;
    }

    /**
     * @param $token
     * @param $new_password
     * @return User|bool
     * @throws GeneralException
     * @throws PolicyException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function updatePasswordWithToken($token, $new_password)
    {
        $UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);
        try
        {
            if ( ! $UserObj = $this->validatePasswordToken($token))
            {
                return false;
            }

            /**
             * update Auth0
             */
            if ($UserObj->authenticatingEntity->identity_connection == AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
            {
                self::getAuth0ApiManagementUserObj()->update_user_password_with_email($UserObj->email, $new_password, $UserObj->authenticatingEntity->identity_connection);
            }

            /**
             *
             * mark all INVITATION_STATUS_PENDING invites into INVITATION_STATUS_ACCEPTED
             */
            foreach ($UserObj->userInvitations as $UserInvitationObj)
            {
                if ($UserInvitationObj->invitation_status == UserInvitation::INVITATION_STATUS_PENDING)
                {
                    $UserInvitationRepositoryObj->update(
                        [
                            'invitation_status' => UserInvitation::INVITATION_STATUS_ACCEPTED,
                        ],
                        $UserInvitationObj->id
                    );
                }
            }
            $this->update(
                [
                    'user_invitation_status' => User::USER_INVITATION_STATUS_ACCEPTED,
                ],
                $UserObj->id
            );
        }
        catch (Exception $e)
        {
            Log::error('unexpected failure in updatePasswordWithToken');
            return false;
        }

        return true;
    }

    /**
     * @param int $user_id
     * @return App\Waypoint\Models\ReportTemplate
     */
    public function getDefaultAnalyticsReportTemplateWithUserId(int $user_id)
    {
        if ( ! $UserObj = $this->find($user_id))
        {
            throw new GeneralException('cannot find user from this id');
        }

        $report_template_id = $UserObj->getConfigValue(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG);

        if ( ! $ReportTemplateObj = App\Waypoint\Models\ReportTemplate::find($report_template_id))
        {
            throw new GeneralException('cannot find report template with the id given', 404);
        }

        return $ReportTemplateObj;
    }

    /**
     * @return mixed
     */
    public function getDefaultAnalyticsReportTemplate()
    {
        // be carful here in a migration / unit test context, gathering this config now uses the logged in user which
        // can be problematic depending who's been logged in
        // TODO (Alex) - make additional tool to gather this config buy just passing in the user id
        $report_template_id = $this->getLoggedInUser()->getConfigValue(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG);

        if ( ! $ReportTemplateObj = App\Waypoint\Models\ReportTemplate::find($report_template_id))
        {
            throw new GeneralException('cannot find report template with the id given', 404);
        }
        return $ReportTemplateObj;
    }

    /**
     * @param int $report_template_id
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function updateReportTemplate(int $report_template_id)
    {
        $FilteredAndOrderedNativeAccountTypesArr = [];

        /** @var ReportTemplate $ReportTemplateObj */
        if ( ! $ReportTemplateObj = App\Waypoint\Models\ReportTemplate::find($report_template_id))
        {
            throw new GeneralException('cannot find report template from id given', 404);
        }

        /** @var User $UserObj */
        $UserObj = $this->getLoggedInUser();

        // set the default report template
        $UserObj->updateConfig(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG, $ReportTemplateObj->id);

        $UserObj->refresh();

        $UserConfigArr = $UserObj->getConfigJSON(true);

        // conform native account type filtering and ordering, inherited from the client config object
        $ClientDefaultNativeAccountTypesNamesArr =
            collect_waypoint(
                $UserObj->client->getConfigJSON(true)
                [NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
                [AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]
            )
                ->pluck('id')
                ->toArray();

        foreach ($ClientDefaultNativeAccountTypesNamesArr as $id)
        {
            $FilteredAndOrderedNativeAccountTypesArr[] =
                $UserObj->getNativeAccountTypeSummaryWithReportTemplateAccountGroupFromNativeAccountType($id)->toArrayWithAdditionalAttributes();
        }

        // set user config slice with the refreshed native account and report tempalte account groups
        $UserConfigArr
        [NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
        [Ledger::ANALYTICS_CONFIG_KEY]
            = $FilteredAndOrderedNativeAccountTypesArr;

        $accountTypeFilters = [];
        foreach ($FilteredAndOrderedNativeAccountTypesArr as $NativeAccountTypeArr)
        {
            $accountTypeFilters[$NativeAccountTypeArr['native_account_type_name']] = $NativeAccountTypeArr['report_template_account_group_id'];
        }

        $UserConfigArr
        [Client::WAYPOINT_LEDGER_DROPDOWNS]
        [Client::DEFAULTS_CONFIG_KEY]
        ['accountTypeFilters']
            = $accountTypeFilters;

        $UserConfigArr
        [Client::WAYPOINT_LEDGER_DROPDOWNS]
        [Client::DEFAULTS_CONFIG_KEY]
        ['activeAccountTab']
            = array_get(array_first($FilteredAndOrderedNativeAccountTypesArr), 'native_account_type_name', '');

        $UserObj->config_json = json_encode($UserConfigArr);
        $UserObj->save();

        return [
            'USER_CONFIG' => $UserConfigArr,
        ];
    }

    /**
     * @param User $UserObj
     * @return bool
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function initNativeAccountDropdownDefaults(User $UserObj)
    {
        /** @var ClientRepository $ClientRepositoryObj */
        $ClientRepositoryObj = App::make(ClientRepository::class);
        /** @var NativeAccountTypeRepository $NativeAccountTypeSummaryRepositoryObj */
        $NativeAccountTypeSummaryRepositoryObj = App::make(NativeAccountTypeSummaryRepository::class);
        /** @var Client $ClientObj */
        $ClientObj = $ClientRepositoryObj->find($UserObj->client_id);

        $user_config_arr   = $UserObj->getConfigJSON(true);
        $client_config_arr = $ClientObj->getConfigJSON(true);

        // if client doesn't have native accounts then setup for ADVANCED_VARIANCE only
        if ( ! isset($client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]))
        {
            $client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]
                = $ClientObj->nativeAccountTypeSummaries->toArray();
        }

        // if the user default report template is not present then gather the default client report template
        if ( ! $UserObj->defaultsAnalyticsReportTemplateExists())
        {
            $user_config_arr
            [User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG]
                = ReportTemplate::where(
                [
                    ['client_id', '=', $ClientObj->id],
                    ['is_default_analytics_report_template', '=', 1],
                ]
            )->first()->id;
        }

        // get user default report template
        if (
        ! $DefaultReportTemplateObj
            = App::make(ReportTemplateRepository::class)
                 ->find($user_config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG])
        )
        {
            throw new GeneralException('cannot find report template from the id given');
        }

        // get native account types based on user default report template
        $NativeAccountTypeSummaries
            = $NativeAccountTypeSummaryRepositoryObj
            ->getForReportTemplate($DefaultReportTemplateObj->id);

        // TODO (Alex) filter the native account types for the user based on the (potentially abridged) list
        //      of native account types on the client object

        $user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY]
            = $NativeAccountTypeSummaries->toArray();

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['activeAccountTab']
            = $NativeAccountTypeSummaries->first()['native_account_type_name'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters']
            = $NativeAccountTypeSummaries->mapWithKeys(function ($item)
        {
            return [
                $item['native_account_type_name'] => $item['report_template_account_group_id'],
            ];
        })->all();

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear'];

        $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['code']
            = $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['code'];

        $UserObj->config_json = json_encode($user_config_arr);
        $UserObj->save();

    }
}
