<?php

namespace App\Waypoint\Http;

use App;
use App\Waypoint\Models\User;
use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;
use App\Waypoint\Repositories\UserRepository;

/**
 * Class ApiGuardAuth
 * @package App\Waypoint\Http
 */
class ApiGuardAuth
{
    protected static $user;

    public function __construct()
    {
    }

    /**
     * Authenticate a user via the API key.
     *
     * @param ApiKeyRepository $apiKey
     * @return bool|mixed
     */
    public static function authenticate(ApiKeyRepository $apiKey)
    {
        if ( ! self::byId($apiKey->user_id))
        {
            return false;
        }

        return self::getUser();
    }

    /**
     * Determines if we have an authenticated user
     *
     * @return bool
     */
    public static function isAuthenticated()
    {
        $user = self::getUser();

        if ( ! isset($user))
        {
            return false;
        }

        return true;
    }

    /**
     * Get the authenticated user.
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * Get the authenticated user.
     *
     * @param integer $user_id
     * @return User
     */
    public static function byId($user_id)
    {
        /** @var UserRepository $UserRepositoryObj */
        $UserRepositoryObj = App::make(UserRepository::class);
        self::$user        = $UserRepositoryObj->find($user_id);
        return self::$user;
    }

    public static function logout()
    {
        self::$user = null;
    }
}