<?php

namespace App\Waypoint\Tests\Mocks;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;
use stdClass;

/**
 * Class Auth0ApiManagementUser
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementTicketMock
{
    /**
     * Auth0ApiManagementConnectionMock constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What, you crazy!!!!! No Auth0ApiManagementTicketMock in production context ' . __FILE__);
        }
    }

    /**
     * @param $email
     * @param $result_url
     * @param int $ttl
     * @param string $identity_connection
     * @return \stdClass
     */
    public function create_email_verification_ticket($email, $result_url, $ttl = 600, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        $result         = new stdClass();
        $result->ticket = 'http://google.com';
        return $result;
    }

    /**
     * @param $email
     * @param $result_url
     * @param int $ttl
     * @param null $new_password
     * @param string $identity_connection
     * @return stdClass
     */
    public function create_password_change_ticket(
        $email,
        $result_url,
        $ttl = 600,
        $new_password = null,
        $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION
    ) {
        $result         = new stdClass();
        $result->ticket = 'http://google.com';
        return $result;
    }
}