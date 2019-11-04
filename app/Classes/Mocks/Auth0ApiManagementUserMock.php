<?php

namespace App\Waypoint\Tests\Mocks;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Models\User;
use Exception;
use stdClass;

/**
 * Class Auth0ApiManagementUser
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementUserMock
{
    /** @var  User */
    protected $UserObj;

    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new Exception('What, you crazy!!!!! No Auth0ApiManagementUserMock in production context ' . __FILE__);
        }
    }

    /**
     * see https://github.com/ixudra/curl
     *
     * @param array $attributes
     * @return bool|array
     * @throws GeneralException
     */
    public function create_user(array $attributes, $identity_connection = null)
    {
        return [];
    }

    /**
     * See https://auth0.com/docs/api/management/v2/user-search
     *
     * @param string $email
     * @return array
     * @throws GeneralException
     */
    public function get_user_with_email($email, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION): array
    {
        return [true];
    }

    /**
     * @return array
     * @throws GeneralException
     */
    public function get_all_users()
    {
        return [true];
    }

    /**
     * @return stdClass
     * @throws GeneralException
     */
    public function update_user_with_email($email, $attributes)
    {
        return new stdClass();
    }

    /**
     * @return stdClass|array
     * @throws GeneralException
     */
    public function update_user_password_with_email($email, $new_password, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        return new stdClass();
    }

    /**
     * @return stdClass
     * @throws GeneralException
     */
    public function delete_user_with_email($email, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        return new stdClass();
    }

    /**
     * See https://auth0.com/docs/api/management/v2/query-string-syntax
     * See http://www.lucenetutorial.com/lucene-query-syntax.html
     *
     * @param $params
     * @return array
     * @throws GeneralException
     */
    public function search_users($params)
    {
        return [];
    }

    /**
     * @param string $email
     * @return array
     * @throws GeneralException
     */
    public function get_user_logs($email, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        return [];
    }
}
