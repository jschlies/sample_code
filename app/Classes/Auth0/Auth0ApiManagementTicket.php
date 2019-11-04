<?php

namespace App\Waypoint\Auth0;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\TestCase;

/**
 * Class Auth0ApiManagementTicket
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementTicket extends Auth0ApiManagement
{
    /** @var  Auth0ApiManagementUser */
    protected $Auth0ApiManagementUserObj;
    /** @var  Auth0ApiManagementConnection */
    protected $Auth0ApiManagementConnectionObj;

    /**
     * User Management constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->Auth0ApiManagementUserObj       = App::make(Auth0ApiManagementUserMock::class);
        $this->Auth0ApiManagementConnectionObj = App::make(Auth0ApiManagementConnectionMock::class);
    }

    /**
     * @param string $email
     * @param $result_url
     * @param int $ttl
     * @return bool
     * @throws GeneralException
     */
    public function create_email_verification_ticket($email, $result_url, $ttl = 600, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        if ( ! $user_arr = $this->Auth0ApiManagementUserObj->get_user_with_email($email, $identity_connection))
        {
            /**
             * user already exists on Auth0. This is OK since we may
             * be dealing w/ a multi-client user
             */
            throw new GeneralException('failed get_user_with_email in create_email_verification_ticket');
        }

        if ( ! TestCase::is_syntactially_valid_url($result_url))
        {
            throw new GeneralException('invalid $result_url');
        }

        if ( ! is_int($ttl))
        {
            throw new GeneralException('invalid $ttl');
        }

        $payload  = [
            'result_url' => $result_url,
            'user_id'    => $user_arr['user_id'],
            'ttl_sec'    => $ttl,
        ];
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'tickets/email-verification')
                         ->withData($payload)
                         ->returnResponseObject()
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->post();

        if (count($response->content) > 1)
        {
            throw new GeneralException('failed get_user_with_email - multiple results');
        }
        return isset($response->content) ? $response->content : false;
    }

    /**
     * @param $email
     * @param $result_url
     * @param int $ttl
     * @param null $new_password
     * @param string $identity_connection
     * @return mixed
     * @throws GeneralException
     */
    public function create_password_change_ticket(
        $email,
        $result_url,
        $ttl = 600,
        $new_password = null,
        $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION
    ) {
        $connection = $this->Auth0ApiManagementConnectionObj->get_connections_with_name($identity_connection);
        if ( ! $user = $this->Auth0ApiManagementUserObj->get_user_with_email($email, $identity_connection))
        {
            /**
             * user already exists on Auth0. This is OK since we may
             * be dealing w/ a multi-client user
             */
            throw new GeneralException('failed get_user_with_email in create_email_verification_ticket');
        }
        if ( ! $new_password)
        {
            $new_password = Seeder::getRandomString(16);
        }
        $payload  = [
            'result_url'    => $result_url,
            'new_password'  => $new_password,
            'connection_id' => $connection->id,
            'email'         => $email,
            'ttl_sec'       => $ttl,
        ];
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'tickets/password-change')
                         ->withData($payload)
                         ->returnResponseObject()
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->post();
        return $response->content;
    }

    /**
     * @return Auth0ApiManagementUser
     */
    public function getAuth0ApiManagementUserObj()
    {
        return $this->Auth0ApiManagementUserObj;
    }

    /**
     * @param Auth0ApiManagementUser $Auth0ApiManagementUserObj
     */
    public function setAuth0ApiManagementUserObj($Auth0ApiManagementUserObj)
    {
        $this->Auth0ApiManagementUserObj = $Auth0ApiManagementUserObj;
    }
}
