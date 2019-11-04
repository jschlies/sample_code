<?php

namespace App\Waypoint\Http\Controllers;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Console\Commands\ListUsersCommand;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\PolicyException;
use App\Waypoint\Http\ApiGuardAuth;
use App\Waypoint\Http\Requests\Api\ApikeyLoginRequest;
use App\Waypoint\Http\Requests\Api\CreateLogoutRequest;
use App\Waypoint\Http\Requests\Generated\Api\CreateUserRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateUserRequest;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use Auth0\Login\Auth0Controller as Auth0ControllerBase;
use Auth0\Login\Auth0Service;
use Auth0\SDK\Exception\CoreException;
use Auth;
use Carbon\Carbon;
use Cookie;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Prettus\Validator\Exceptions\ValidatorException;
use Redirect;
use Response;

class Auth0Controller extends Auth0ControllerBase
{
    /** @var UserRepository */
    protected $UserRepositoryObj;
    protected $password_rules;

    /** @var bool */
    protected $error = false;

    /** @var bool */
    protected $warning = false;

    /** @var null|string */
    protected $message = null;

    public function __construct(UserRepository $UserRepositoryObj)
    {
        parent::__construct($UserRepositoryObj);
        $this->UserRepositoryObj = $UserRepositoryObj;
        $this->password_rules    = new Collection();

        /**
         * we need this here because, remember that Auth0Controller does not extend App\Waypoint\Http\ApiController
         */
        if (
            env('APP_ENV', false) == 'local' &&
            config('waypoint.use_mock_objects', false)
        )
        {
            if (config('waypoint.use_auth0apimanagementusermock', false))
            {
                ListUsersCommand::setAuth0ManagementUsersObj(new Auth0ApiManagementUserMock());
                UserRepository::setAuth0ApiManagementUserObj(new Auth0ApiManagementUserMock());
            }
            if (config('waypoint.use_nativecoaledgermockrepository', false))
            {
                PasswordRuleRepository::setAuth0ApiManagementConnectionObj(new Auth0ApiManagementConnectionMock());
            }
            if (config('waypoint.use_nativecoaledgermockrepository', false))
            {
                LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
            }
            if (config('waypoint.use_nativecoaledgermockrepository', false))
            {
                AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
            }
        }
    }

    /**
     * Callback action that should be called by auth0, logs the user in
     *
     * /**
     * @param CreateLogoutRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws GeneralException
     */
    public function callbackWithRequest(CreateLogoutRequest $request)
    {
        try
        {
            /**
             * remember that $this->userRepository is Auth0UserRepository via the Auth0 SDK repository
             */
            parent::callback();

            if ( ! $auth0_user_info = App::make('auth0')->getUser())
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User mismatch!!!!!!! No such user in Auth0'
                );
            }
            /**
             * remember that $this->userRepository is Auth0UserRepository via the Auth0 SDK repository
             */
            if ( ! $Auth0UserObj = $this->userRepository->getUserByUserInfo($auth0_user_info))
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User mismatch!!!!!!! No such user in Auth0'
                );
            }
            if ( ! $UserObj = $this->UserRepositoryObj->getLoggedInUser())
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User mismatch!!!!!!! Email ' . $Auth0UserObj->getUserInfo()['email'] .
                                                 ' got past Auth0 but authenticating_entity reported by Auth0 does not match user record - Open a HERMES ticket'
                );
            }
            if ( ! $UserObj->authenticatingEntity)
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User mismatch!!!!!!! Email ' . $Auth0UserObj->getUserInfo()['email'] .
                                                 ' got past Auth0 but authenticating_entity reported by Auth0 does not match user record - Open a HERMES ticket'
                );
            }
            if ( ! isset($Auth0UserObj->getUserInfo()['identities'][0]['connection']))
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User mismatch!!!!!!! Email ' . $Auth0UserObj->getUserInfo()['email'] .
                                                 ' got past Auth0 but authenticating_entity reported by Auth0 does not match user record - Open a HERMES ticket'
                );
            }
            if ($UserObj->authenticatingEntity->identity_connection !== $Auth0UserObj->getUserInfo()['identities'][0]['connection'])
            {
                $this->destroy_session_and_wipe_cookies($request);
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('User authenticating_entity mismatch!!!!!!! Email ' . $UserObj->email .
                                                 ' got somehow past Auth0 but authenticating_entity reported by Auth0 does not match user record (' .
                                                 $UserObj->authenticatingEntity->name . '). User redirected to /logout/clearState with distain - Open a HERMES ticket'
                );
            }
            if (
                ! $UserObj ||
                $UserObj->active_status !== User::ACTIVE_STATUS_ACTIVE
            )
            {
                /**
                 * no such user just logged in, invalidate session (if exists) and send him/her to /logout/clearState
                 */
                $this->destroy_session_and_wipe_cookies($request);

                return Redirect::intended('/');
            }

            /**
             * get all user records of this user across all clients
             */
            $AllUserRecordsUserObjArr = $this->UserRepositoryObj->findWhere(['email' => $UserObj->email]);

            /**
             * ensure that this user, across all his/her clients, had only one authenticating_entity
             */
            if ($AllUserRecordsUserObjArr->pluck('authenticating_entity_id')->unique('id')->count() > 1)
            {
                /**
                 * remember that the ExceptionHandler will report a AuthorizationException to Rollbar and then will \Redirect::intended('/');
                 */
                throw new AuthorizationException('authenticating_entity mismatch!!!!!!! user ' . $UserObj->email .
                                                 ' has multiple authenticating_entities per hermes db.' .
                                                 ' User redirected to /logout/clearState and session terminated with extreme prejudice - Open a HERMES ticket'
                );
            }

            /**
             * At thie point, the user is 100% authenticated via both Auth0 adn the waypoint
             * users table, get all user records of this user across all clients
             */
            $AllUserRecordsUserObjArr->map(
                function ($LocalUserObj)
                {
                    if ( ! $LocalUserObj->first_login_date)
                    {
                        $LocalUserObj->first_login_date = Carbon::now()->format('Y-m-d H:i:s');
                    }
                    if (
                        $LocalUserObj->user_invitation_status !== User::USER_INVITATION_STATUS_ADDED_VIA_ADMIN ||
                        $LocalUserObj->user_invitation_status !== User::USER_INVITATION_STATUS_PENDING ||
                        $LocalUserObj->user_invitation_status !== User::USER_INVITATION_STATUS_NEVER_INVITED
                    )
                    {
                        $LocalUserObj->user_invitation_status      = User::USER_INVITATION_STATUS_ACCEPTED;
                        $LocalUserObj->user_invitation_status_date = Carbon::now()->format('Y-m-d H:i:s');
                    }
                    $LocalUserObj->last_login_date = Carbon::now()->format('Y-m-d H:i:s');
                    $LocalUserObj->save();
                }
            );

            if ( ! isset($_REQUEST['state']))
            {
                return Redirect::intended('/');
            }
            return Redirect::intended($_REQUEST['state']);
        }
        catch (CoreException $e)
        {
            /**
             * User is already logged so simply send him/her to homepage.
             * This probably was requested via bookmark or simply typing in address to
             */
            if ($e->getMessage() == 'Can\'t initialize a new session while there is one active session already')
            {
                return Redirect::intended('/');
            }

            throw new GeneralException($e->getMessage(), 400, $e);
        }
        catch (Exception $ExceptionObj)
        {
            throw $ExceptionObj;
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param CreateLogoutRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \RuntimeException
     */
    public function logout(CreateLogoutRequest $request)
    {
        /** @var Auth0Service $Auth0Service */
        $Auth0Service = new Auth0Service();
        $CLIENT_ID    = $request->cookies->get('CLIENT_ID_COOKIE');
        $Auth0Service->logout();

        if ($request->hasSession())
        {
            $request->session()->invalidate();
        }
        /**
         * delete all cookies except CLIENT_ID_COOKIE
         *
         * See https://github.com/laravel/framework/issues/18833
         */
        Cookie::queue(
            Cookie::forget('XSRF-TOKEN')
        );
        Cookie::queue(
            Cookie::forget('laravel_session')
        );
        return $this->sendResponse([], 'Logout successful')
                    ->withCookie(Cookie::forever('CLIENT_ID_COOKIE', $CLIENT_ID));
    }

    /**
     * Log the user out of the application and delete 'all' cookies.
     *
     * @param CreateLogoutRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \RuntimeException
     */
    public function logoutClearState(CreateLogoutRequest $request)
    {
        /** @var Auth0Service $Auth0Service */
        $Auth0Service = new Auth0Service();
        $Auth0Service->logout();

        $this->destroy_session_and_wipe_cookies($request);
        return $this->sendResponse([], 'Logout successful');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $UserRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function store(CreateUserRequest $UserRequestObj)
    {
        $input   = $UserRequestObj->all();
        $UserObj = $this->UserRepositoryObj->create($input);

        return $this->sendResponse($UserObj->toArray(), 'User saved successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordForm()
    {
        return $this->sendResponse(true, 'Password Form');
    }

    /** @noinspection PhpUnusedParameterInspection */
    /**
     * @param UpdateUserRequest $UserRequestObj
     * @param integer $client_id
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function updatePassword(UpdateUserRequest $UserRequestObj, $client_id, $user_id)
    {
        $input = $UserRequestObj->all();
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj->updatePassword($user_id, $input['password']);

        return $this->sendResponse($UserObj->toArray(), 'User password saved successfully');
    }

    /**
     * @param CreateUserRequest $UserRequestObj
     * @param integer $client_id
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function generatePasswordToken(CreateUserRequest $UserRequestObj, $client_id, $user_id)
    {
        /** @var User $UserObj */
        $user_token = $this->UserRepositoryObj->generatePasswordToken($user_id);
        return $this->sendResponse($user_token, 'User Token created successfully');
    }

    /**
     * @param $result
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     *
     * See https://laravel.com/docs/5.1/cache#configuration
     */
    public function sendResponse($result, $message, $errors = [], $warnings = [], $metadata = [])
    {
        return Response::json(ResponseUtil::makeResponse($message, $result, $errors, $warnings, $metadata));
    }

    /**
     * @param ApikeyLoginRequest $ApikeyLoginRequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function login_via_apiKey(ApikeyLoginRequest $ApikeyLoginRequestObj, $client_id)
    {


        /** @var User $UserObj */
        $UserObj = ApiGuardAuth::getUser();
        Auth::login($UserObj, App::make('auth0')->rememberUser());

        if ( ! $UserObj->hasRole(Role::WAYPOINT_ROOT_ROLE))
        {
            if ($UserObj->client_id != $client_id)
            {
                throw new GeneralException('invalid client_id');
            }
        }

        $return_me = [
            'user'            => $UserObj->toArray(),
            'laravel_session' => $ApikeyLoginRequestObj->cookie('laravel_session'),
        ];

        session(['api_key_user_id' => $UserObj->id]);

        return $this->sendResponse($return_me, 'Successfully logged into client ' . $client_id)
                    ->withCookie(Cookie::forever('CLIENT_ID_COOKIE', $client_id));
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function renderSetupPasswordPage(Request $request)
    {
        try
        {
            $token = $request->get('one_time_token');

            if (empty($token))
            {
                throw new Exception('You are missing a security token, please hit the link from your invitation email again.');
            }
            /** @var User $UserObj */
            elseif ( ! $UserObj = $this->UserRepositoryObj->validatePasswordToken($token))
            {
                throw new Exception('Your invitation may have expired, or this is an invalid link.');
            }
        }
        catch (Exception $e)
        {
            $this->error   = true;
            $this->message = $e->getMessage();
        }

        $this->rules = App::make(PasswordRuleRepository::class)->get_password_rules()->toArray();
        return View::make(
            'pages.setup-password',
            [
                'rules'          => json_encode($this->rules),
                'message'        => $this->message,
                'error'          => $this->error,
                'warning'        => $this->warning,
                'user'           => isset($UserObj) && $UserObj ? $UserObj->toArray() : null,
                'one_time_token' => $token,
            ]
        );
    }

    /**
     * @param UpdateUserRequest $UserRequestObj
     * @return $this|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     * @throws GeneralException
     * @throws PolicyException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function updatePasswordWithToken(UpdateUserRequest $UserRequestObj)
    {
        $message = null;
        $error   = false;
        $input   = $UserRequestObj->all();
        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj->validatePasswordToken($input['one_time_token']);
        if (
            ! isset($input['one_time_token']) ||
            ! $input['one_time_token'] ||
            ! isset($input['password']) ||
            ! $input['password'] ||
            ! $UserObj
        )
        {
            $message = 'Invalid token';
            $error   = true;
        }
        else
        {
            $update_password_status = $this->UserRepositoryObj->updatePasswordWithToken($input['one_time_token'], $input['password']);
        }

        if ( ! $update_password_status)
        {
            $rules = App::make(PasswordRuleRepository::class)->get_password_rules()->toArray();
            return View::make(
                'pages.setup-password',
                [
                    'rules'          => json_encode($rules),
                    'message'        => $message,
                    'error'          => $error,
                    'user'           => $UserObj ? $UserObj->toArray() : null,
                    'one_time_token' => $input['one_time_token'],
                ]
            );
        }

        /**
         * Calling login() seems to refresh the session so that they cannot login,
         * which is desired at this point due to IE browser incompatability with the app.
         * To seamlessly login remove it and maybe don't attach those cookies below.
         */
        Auth::login($UserObj, App::make('auth0')->rememberUser());

        return Redirect::intended('/')
                       ->withCookie(Cookie::forever('CLIENT_ID_COOKIE', $UserObj->client_id))
                       ->withCookie(Cookie::forever('laravel_session', $UserObj->client_id));

    }

    /**
     * @param $request
     */
    private function destroy_session_and_wipe_cookies($request)
    {
        if ($request->hasSession())
        {
            $request->session()->invalidate();
        }
        /**
         * delete all cookies, even CLIENT_ID_COOKIE
         *
         * See https://github.com/laravel/framework/issues/18833
         */
        Cookie::queue(
            Cookie::forget('XSRF-TOKEN')
        );
        Cookie::queue(
            Cookie::forget('laravel_session')
        );
        Cookie::queue(
            Cookie::forget('CLIENT_ID_COOKIE')
        );
    }
}
