<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App;
use App\Waypoint\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use App\Waypoint\Models\User;

/**
 * Class ClientCookieController
 */
class ClientCookieController extends BaseApiController
{
    /** @var  ClientRepository */
    private $ClientRepository;

    public function __construct(ClientRepository $ClientRepository)
    {
        $this->ClientRepository = $ClientRepository;
        parent::__construct($ClientRepository);
    }

    /**
     *
     * @param $client_cookie_value
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index($client_cookie_value)
    {
        /** @var UserRepository $UserRepositoryObj */
        $UserRepositoryObj = App::make(UserRepository::class);
        if ( ! $CurrnetLoggedInUserObj = $UserRepositoryObj->getLoggedInUser())
        {
            throw new AuthorizationException();
        }

        if ( ! $UserObj = $UserRepositoryObj->findWhere(
            [
                'client_id' => $client_cookie_value,
                'email'     => $CurrnetLoggedInUserObj->email,
            ]
        )->first()
        )
        {
            if ( ! $CurrnetLoggedInUserObj->email == User::SUPERUSER_EMAIL)
            {
                throw new AuthorizationException('user not authorized in client_id');
            }
        }

        /**
         * note you can't call $UserObj->toArray() because the authorization
         * policy will fail since at this point the logged in user
         * is still in the PREV client (this route this is
         * RE_SETTING the CLIENT_ID_COOKIE).
         */
        return $this->sendResponse(
            [],
            'ClientCookie set successfully'
        )->withCookie(
            \Cookie::forever(
                'CLIENT_ID_COOKIE', $client_cookie_value, null, null, false, false
            )
        );
    }
}
