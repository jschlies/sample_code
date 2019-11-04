<?php

namespace App\Waypoint\Http\Controllers\Api;

use \Illuminate\Http\Request;
use App;
use App\Waypoint\Collection;
use App\Waypoint\Events\ClientUpdatedEvent;
use App\Waypoint\Events\UserUpdatedEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateUserRequest;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Repositories\AccessListUserRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Repositories\RoleRepository;
use App\Waypoint\Repositories\UserAdminRepository;
use App\Waypoint\Repositories\UserDetailRepository;
use App\Waypoint\Repositories\UserInvitationRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Repositories\UserSlimRepository;
use App\Waypoint\ResponseUtil;
use BadMethodCallException;
use Carbon\Carbon;
use function collect_waypoint;
use function explode;
use function snake_case;
use function strtoupper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;
use stdClass;

/**
 * Class UserPublicDeprecatedController
 * @codeCoverageIgnore
 */
class UserPublicDeprecatedController extends BaseApiController
{
    use Notifiable;

    /**
     * @var boolean
     */
    protected $controller_allow_cacheing = false;

    /** @var  UserDetailRepository */
    private $UserDetailRepositoryObj;
    /** @var  UserDetailRepository */
    private $UserRepositoryObj;
    /** @var  AccessListUserRepository */
    private $AccessListUserRepositoryObj;
    /** @var  UserInvitationRepository */
    private $UserInvitationRepositoryObj;
    /** @var ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;

    public function __construct(UserDetailRepository $UserDetailRepositoryObj)
    {
        $this->UserDetailRepositoryObj     = $UserDetailRepositoryObj;
        $this->UserRepositoryObj           = App::make(UserRepository::class);
        $this->UserInvitationRepositoryObj = App::make(UserInvitationRepository::class);
        $this->AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
        $this->ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);
        $this->UserSlimRepositoryObj       = App::make(UserSlimRepository::class);
        parent::__construct($UserDetailRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param null $user_id_arr
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws RepositoryException
     */
    public function indexUserDetailForClient(Request $RequestObj, $client_id, $user_id_arr = null)
    {
        $this->UserDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->UserDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $UserDetailObjArr = $this->UserDetailRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );

        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($user_id_arr)
        {
            $UserDetailObjArr = $UserDetailObjArr->whereIn('id', explode(',', $user_id_arr));
        }

        return $this->sendResponse($UserDetailObjArr, 'User(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param null $user_id_arr
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexUserForClient(Request $RequestObj, $client_id, $user_id_arr = null)
    {
        $this->UserRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->UserRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $UserDetailObjArr = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );

        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($user_id_arr)
        {
            $UserDetailObjArr = $UserDetailObjArr->whereIn('id', explode(',', $user_id_arr));
        }

        return $this->sendResponse($UserDetailObjArr, 'User(s) retrieved successfully');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     */
    public function store(CreateUserRequest $UserRequestObj)
    {
        /**
         * mostly to speed up unittests. We'll kick off UserUpdatedEvent when we're done
         */
        $this->UserRepositoryObj->setSuppressEvents(true);
        $input = $UserRequestObj->all();
        if (
            isset($input['is_hidden']) &&
            $input['is_hidden'] &&
            ! $this->CurrentLoggedInUserObj->hasRole(Role::WAYPOINT_ROOT_ROLE)
        )
        {
            throw new GeneralException('You may not create user with is_hidden=true via this route and your role(s)');
        }

        $UserAdminRepository = App::make(UserAdminRepository::class)->setSuppressEvents(true);
        $this->AccessListUserRepositoryObj->setSuppressEvents(true);

        $NewUserObj = $UserAdminRepository->create($input);
        if (isset($input['access_list_id']))
        {
            foreach (explode(',', $input['access_list_id']) as $access_list_id)
            {
                $this->AccessListUserRepositoryObj->create(
                    [
                        'user_id'        => $NewUserObj->id,
                        'access_list_id' => $access_list_id,
                    ]
                );
            }
        }

        if ((isset($input['send_invitation']) && $input['send_invitation']))
        {
            $one_time_token_arr = $this->UserRepositoryObj->generatePasswordToken($NewUserObj->email);
            $this->UserInvitationRepositoryObj->create(
                [
                    'invitee_user_id'       => $NewUserObj->id,
                    'inviter_user_id'       => $this->getCurrentLoggedInUserObj()->id,
                    'one_time_token_expiry' => $one_time_token_arr['one_time_token_expiry'],
                    'one_time_token'        => $one_time_token_arr['one_time_token'],
                    'inviter_ip'            => ($UserRequestObj->ip() ?: 'unknown'),
                    'invitation_status'     => UserInvitation::INVITATION_STATUS_PENDING,
                ]
            );
            /**
             * roles for reasons that escape me are dealt with in $UserAdminRepository
             */
            if (isset($input['send_invitation']) && $input['send_invitation'])
            {
                $this->post_job_to_queue(
                    [
                        'user_invitation_id' => $NewUserObj->id,
                    ],
                    App\Waypoint\Jobs\InvitationNotificationJob::class,
                    config('queue.queue_lanes.InvitationNotification', false)
                );
            }
        }
        else
        {
            $NewUserObj = $this->UserRepositoryObj->update(
                [
                    'user_invitation_status'      => User::USER_INVITATION_STATUS_NEVER_INVITED,
                    'user_invitation_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                $NewUserObj->id
            );
        }

        event(
            new ClientUpdatedEvent(
                $NewUserObj->client,
                [
                    'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($NewUserObj),
                    'event_trigger_object_class_id' => $NewUserObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'clients' => [
                                'relatedUserTypes_client_.*',
                            ],
                        ],
                ]
            )
        );

        event(
            new UserUpdatedEvent(
                $NewUserObj->refresh(),
                [
                    'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($NewUserObj),
                    'event_trigger_object_class_id' => $NewUserObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'users' => [
                                'assetTypesOfProperties_user_.*',
                                'accessible_property_arr_user_.*',
                                'standardAttributesOfProperties_user_.*',
                                'customAttributesOfProperties_user_',
                                'AccessiblePropertyObjFormattedArr_user_.*',
                                'accessiblePropertyGroups_user_.*',
                                'user_accessable_property_id_arr_.*',
                            ],
                        ],
                    'launch_job_user_id_arr'        => [$NewUserObj->id],
                ]
            )
        );

        return $this->sendResponse(collect_waypoint([$this->UserDetailRepositoryObj->findWithoutFail($NewUserObj->id)])->toArray(), 'User saved successfully');
    }

    /**
     * Display the specified User.
     * GET|HEAD /users/{id}
     *
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($user_id)
    {
        /** @var UserDetail $user */
        $UserDetailObj = $this->UserDetailRepositoryObj->findWithoutFail($user_id);

        $key             = 'user_detail_user_' . $user_id;
        $user_detail_arr = $UserDetailObj->getPreCalcValue($key);
        if ($user_detail_arr === null)
        {
            /** @var UserDetail $user */
            $UserDetailObj = $this->UserDetailRepositoryObj
                ->with('client.properties.assetType')
                ->with('userInvitations')
                ->with('relatedUsers')
                ->with('accessLists.properties.assetType')
                ->findWithoutFail($user_id);

            $user_detail_arr = $UserDetailObj->toArray();
            $UserDetailObj->updatePreCalcValue(
                $key,
                $user_detail_arr
            );
        }
        $payload                = new stdClass();
        $property_key           = 'UserDetail_' . $user_id;
        $payload->$property_key = $user_detail_arr;

        $payload_arr = stdToArray($payload);
        return $this->sendResponse($payload_arr, 'User retrieved successfully');
    }

    /**
     * Update the specified User in storage.
     * PUT/PATCH /users/{id}
     *
     * @param integer $user_id
     * @param UpdateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws ValidatorException
     */
    public function update($user_id, UpdateUserRequest $UserRequestObj)
    {
        $input = $UserRequestObj->all();
        if (isset($input['access_list_id']) || isset($input['role']))
        {
            throw new GeneralException('you may not access access_list_id or role via this route');
        }
        if (
            isset($input['is_hidden']) &&
            ! $this->CurrentLoggedInUserObj->hasRole(Role::WAYPOINT_ROOT_ROLE)
        )
        {
            throw new GeneralException('You may not update user with is_hidden=true via this route and your role(s)');
        }

        /**
         * Update user details in User Model
         * @var UserDetail $UserDetailObj
         */
        $UserDetailObj = $this->UserDetailRepositoryObj->update($input, $user_id);

        foreach (User::$user_notification_flags as $notification_flag)
        {
            if (isset($input[$notification_flag]))
            {
                $UserDetailObj->updateConfig($notification_flag, $input[$notification_flag]);
            }
        }

        return $this->sendResponse(collect_waypoint([$UserDetailObj]), 'User updated successfully');
    }

    /**
     * @param integer $user_id
     * @param UpdateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws BadMethodCallException
     * @throws GeneralException
     * @throws RepositoryException
     * @throws ValidatorException
     */
    public function updateAdmin($user_id, UpdateUserRequest $UserRequestObj)
    {
        $input = $UserRequestObj->all();
        if (
            isset($input['is_hidden']) &&
            ! $this->CurrentLoggedInUserObj->hasRole(Role::WAYPOINT_ROOT_ROLE)
        )
        {
            throw new GeneralException('You may not update user with is_hidden=true via this route and your role(s)');
        }

        /**
         * since this method does multiple calls to $this->UserDetailRepositoryObj->update(),
         * we will do UpdateUserEvent ourselves
         */
        $this->UserRepositoryObj->setSuppressEvents(true);
        /**
         * Update user details in User Model
         * @var User $UserObj
         */
        $UserObj = $this->UserRepositoryObj->update($input, $user_id);

        foreach (User::$user_notification_flags as $notification_flag)
        {
            if (isset($input[$notification_flag]))
            {
                $UserObj->updateConfig($notification_flag, $input[$notification_flag]);
            }
        }
        if (isset($input['access_list_id']))
        {
            $access_list_id_arr = explode(',', $input['access_list_id']);
            /** @var AccessListUserRepository $AccessListUserRepositoryObj */
            $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
            $AccessListUserRepositoryObj->setSuppressEvents(true);
            foreach ($access_list_id_arr as $access_list_id)
            {
                /**
                 * if the relationship already exists .....
                 */
                if (in_array($access_list_id, $UserObj->accessListUsers->pluck('access_list_id')->toArray()))
                {
                    continue;
                }
                $AccessListUserRepositoryObj->create(
                    [
                        'user_id'        => $UserObj->id,
                        'access_list_id' => $access_list_id,
                    ]
                );
            }
            foreach ($UserObj->accessListUsers as $AccessListUserObj)
            {
                if ( ! in_array($AccessListUserObj->access_list_id, $access_list_id_arr))
                {
                    $AccessListUserRepositoryObj->delete($AccessListUserObj->id);
                }
            }
        }

        $UserSlimObj = $this
            ->UserSlimRepositoryObj
            ->findWithoutFail($user_id);
        /**
         * since this method does multiple calls to $this->UserDetailRepositoryObj->update(),
         * we will do UpdateUserEvent ourselves
         */
        event(
            new UserUpdatedEvent(
                $UserSlimObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($UserSlimObj),
                    'event_trigger_object_class_id' => $UserSlimObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'users' => [
                                'assetTypesOfProperties_user_.*',
                                'accessible_property_arr_user_.*',
                                'standardAttributesOfProperties_user_.*',
                                'customAttributesOfProperties_user_.*',
                                'AccessiblePropertyObjFormattedArr_user_.*',
                                'accessiblePropertyGroups_user_.*',
                                'user_accessable_property_id_arr_.*',
                            ],
                        ],
                    'launch_job_user_id_arr'        => [$UserSlimObj->id],
                ]
            )
        );

        return $this->sendResponse($UserSlimObj, 'User updated successfully');
    }

    /**
     * @param integer $user_id
     * @param UpdateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws BadMethodCallException
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update_is_hidden($user_id, UpdateUserRequest $UserRequestObj)
    {
        $UserAdminRepositoryObj = App::make(UserAdminRepository::class);
        $input                  = $UserRequestObj->all();
        if ( ! isset($input['is_hidden']))
        {
            throw new GeneralException('please provide a is_hidden value');
        }
        if ( ! $UserDetailObj = $UserAdminRepositoryObj->find($user_id))
        {
            throw new GeneralException('No such user');
        }
        if (count($input) !== 1)
        {
            throw new GeneralException('please only provide a is_hidden value');
        }
        if ($input['is_hidden'] == 0 || $input['is_hidden'] == 1)
        {
            $UserAdminRepositoryObj->update($input, $UserDetailObj->id);
        }
        else
        {
            throw new GeneralException('please provide a is_hidden value');
        }
        return $this->sendResponse($UserAdminRepositoryObj->find($user_id), 'User updated successfully');
    }

    /**
     * Remove the specified User from storage.
     * DELETE /users/{id}
     *
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws ValidationException
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function destroy($user_id)
    {
        $this->UserRepositoryObj->delete($user_id);

        return $this->sendResponse($user_id, 'User deleted successfully');
    }

    /**
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws BadMethodCallException
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function showAccessibleGroups($user_id)
    {
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj
            ->find($user_id);

        $key                            = 'accessiblePropertyGroups_user_' . $UserObj->id;
        $AccessiblePropertyGroupsObjArr = $UserObj->getPreCalcValue($key);
        if ($AccessiblePropertyGroupsObjArr === null)
        {
            $AccessiblePropertyGroupsObjArr = $UserObj->getAccessiblePropertyGroupObjArr()->toArray();
            $UserObj->updatePreCalcValue(
                $key,
                $AccessiblePropertyGroupsObjArr
            );
        }

        /**
         * @todo please doc why array and not simply return $AccessiblePropertyGroupObjArr
         */
        $return_me = [
            "accessiblePropertyGroups" => $AccessiblePropertyGroupsObjArr,
        ];

        return $this->sendResponse($return_me, 'User accessible property group(s) retrieved successfully');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Exception
     */
    public function addRoleToUser($client_id, $user_id, $role_name, CreateUserRequest $UserRequestObj)
    {
        $RoleRepositoryObj = App::make(RoleRepository::class);
        $UserRepositoryObj = App::make(UserRepository::class);
        if ( ! $RoleObj = $RoleRepositoryObj->findWhere(['name' => $role_name])->first())
        {
            throw new ModelNotFoundException('no such role');
        }
        /** @var User $UserObj */
        if ( ! $UserObj = $UserRepositoryObj->find($user_id))
        {
            throw new ModelNotFoundException('no such user');
        }
        if ( ! $UserObj->hasRole($role_name))
        {
            $UserObj->attachRole($RoleObj);
        }

        return $this->sendResponse($UserObj->refresh(), 'User saved successfully');
    }

    /**
     * Remove the specified User from storage.
     * DELETE /users/{id}
     *
     * @param integer $client_id
     * @param integer $user_id
     * @param string $role_name
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     * @throws BadMethodCallException
     */
    public function destroyRoleToUser($client_id, $user_id, $role_name, CreateUserRequest $UserRequestObj)
    {
        $RoleRepositoryObj = App::make(RoleRepository::class);
        $UserRepositoryObj = App::make(UserRepository::class);
        if ( ! $RoleObj = $RoleRepositoryObj->findWhere(['name' => $role_name])->first())
        {
            throw new ModelNotFoundException('no such role');
        }
        /** @var User $UserObj */
        if ( ! $UserObj = $UserRepositoryObj->find($user_id))
        {
            throw new ModelNotFoundException('no such user');
        }
        if ($UserObj->hasRole($role_name))
        {
            $UserObj->detachRole($RoleObj);
        }
        return $this->sendResponse($UserObj->refresh(), 'User saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $user_id_arr
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function inviteUser($client_id, $user_id_arr, CreateUserRequest $UserRequestObj)
    {
        $InviteeUserDetailObjArr = new Collection();
        if ($user_id_arr)
        {
            $InviteeUserDetailObjArr = $this->UserDetailRepositoryObj->findWhereIn('id', explode(',', $user_id_arr));
        }
        foreach ($InviteeUserDetailObjArr as $InviteeUserObj)
        {
            $this->secret_token = $this->UserRepositoryObj->generatePasswordToken($InviteeUserObj->email);
            $this->UserInvitationRepositoryObj->create(
                [
                    'invitee_user_id'       => $InviteeUserObj->id,
                    'inviter_user_id'       => $this->getCurrentLoggedInUserObj()->id,
                    'one_time_token_expiry' => $this->secret_token['one_time_token_expiry'],
                    'one_time_token'        => $this->secret_token['one_time_token'],
                    'inviter_ip'            => $UserRequestObj->ip() ?: 'unknown',
                    'invitation_status'     => UserInvitation::INVITATION_STATUS_PENDING,

                ]
            );

            $this->post_job_to_queue(
                [
                    'user_invitation_id' => $InviteeUserObj->id,
                ],
                App\Waypoint\Jobs\InvitationNotificationJob::class,
                config('queue.queue_lanes.InvitationNotification', false)
            );
        }
        return $this->sendResponse($this->UserDetailRepositoryObj->findWhereIn('id', explode(',', $user_id_arr)), 'Invitation(s) saved successfully');
    }

    /**
     * @param integer $client_id
     * @param string $user_id_arr
     * @param CreateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function inviteUserCancel($client_id, $user_id_arr, CreateUserRequest $UserRequestObj)
    {
        $InviteeUserDetailObjArr = new Collection();
        if ($user_id_arr)
        {
            $InviteeUserDetailObjArr = $this->UserDetailRepositoryObj->findWhereIn('id', explode(',', $user_id_arr));
        }
        /** @var User $InviteeUserObj */
        foreach ($InviteeUserDetailObjArr as $InviteeUserObj)
        {
            /** @var UserInvitation $UserInvitationObj */
            foreach ($InviteeUserObj->userInvitations as $UserInvitationObj)
            {
                if ($UserInvitationObj->invitation_status == UserInvitation::INVITATION_STATUS_PENDING)
                {
                    $this->UserInvitationRepositoryObj->update(
                        [
                            'invitation_status' => UserInvitation::INVITATION_STATUS_REVOKED,
                        ],
                        $UserInvitationObj->id
                    );
                }
            }
        }
        return $this->sendResponse($this->UserDetailRepositoryObj->findWhereIn('id', explode(',', $user_id_arr)), 'Invitation(s) saved revoked');
    }

    /**
     * @param integer $user_id
     * @param UpdateUserRequest $UserRequestObj
     * @return JsonResponse|null
     * @throws \Exception
     */
    public function updateNotificationsConfig($user_id, UpdateUserRequest $UserRequestObj)
    {
        try
        {
            $response_package  = [];
            $notifications_arr = $UserRequestObj->all();

            if ( ! $notifications_arr || empty($notifications_arr))
            {
                return Response::json(ResponseUtil::makeError('We did not receive a notification to change, please check the request.', []), 400);
            }

            $UserObj = $this->UserRepositoryObj->find($user_id);

            foreach ($notifications_arr as $type => $value)
            {
                $user_config_arr = $UserObj->getConfigJSON(true);
                $config_key      = strtoupper(snake_case($type));

                if (isset($user_config_arr[$config_key]))
                {
                    $UserObj->updateConfig($config_key, (bool) $value);
                    $response_package[$type] = $value;
                }
                elseif (
                    $config_key == User::MENTIONED_NOTIFICATIONS_FLAG
                    && isset($user_config_arr[User::VARIANCE_MENTIONED_NOTIFICATIONS_FLAG])
                    && isset($user_config_arr[User::OPPORTUNITIES_MENTIONED_NOTIFICATIONS_FLAG])
                )
                {
                    $UserObj->updateConfig(User::VARIANCE_MENTIONED_NOTIFICATIONS_FLAG, (bool) $value);
                    $UserObj->updateConfig(User::OPPORTUNITIES_MENTIONED_NOTIFICATIONS_FLAG, (bool) $value);
                    $response_package[$type] = $value;
                }
                else
                {
                    throw new GeneralException('missing notification type in user config');
                }
            }
            return $this->sendResponse($response_package, 'successful update of ' . $type . ' to ' . $value);
        }
        catch (GeneralException $e)
        {
            // if error with one then send back error response along with all settings unchanged (ie. reversed from what was requested)
            foreach ($notifications_arr as $type => $value)
            {
                $response_package[$type] = ! (bool) $value;
            }
            return Response::json(ResponseUtil::makeError($e->getMessage(), $response_package), 500);
        }
    }

    /**
     * @param integer $report_template_id
     * @return JsonResponse|null
     * @throws ValidatorException
     */
    public function updateDefaultReportTemplate($report_template_id)
    {
        try
        {
            if ( ! $ReportTemplateObj = $this->ReportTemplateRepositoryObj->find($report_template_id))
            {
                throw new GeneralException('cannot find the report template from the id given', 404);
            }

            /** @var UserRepository $UserRepoObj */
            $UserRepoObj       = App::make(UserRepository::class);
            $response_data_arr = $UserRepoObj->updateReportTemplate($report_template_id);
            return $this->sendResponse($response_data_arr, 'successfully changed the user report template to: ' . $ReportTemplateObj->report_template_name);

        }
        catch (GeneralException $e)
        {
            return Response::json(ResponseUtil::makeError($e->getMessage(), []), 404);
        }
    }
}
