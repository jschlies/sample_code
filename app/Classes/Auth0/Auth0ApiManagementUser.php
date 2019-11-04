<?php

namespace App\Waypoint\Auth0;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use Log;

/**
 * Class Auth0ApiManagementUser
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementUser extends Auth0ApiManagement
{
    /** @var  User */
    protected $UserObj;

    /**
     * User Management constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
        if ($user = $this->get_user_with_email($attributes['email'], $identity_connection))
        {
            /**
             * user already exists on Auth0. This is OK since we may
             * be dealing w/ a multi-client user
             */
            return $user;
        }
        /**
         * addl params (like "'search_engine'  => 'v3'", are forbidden on a POST
         */
        $payload  = [
            'email'          => $attributes['email'],
            'username'       => Seeder::getRandomString(12),
            'password'       => $attributes['password'],
            'connection'     => $identity_connection,
            'email_verified' => true,
            'verify_email'   => false,
        ];
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'users')
                         ->withData($payload)
                         ->returnResponseObject()
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->post();
        if (
            $response->status != 201 &&
            isset($response->content) &&
            isset($response->content->message) &&
            $response->content->message !== 'The user already exists.'
        )
        {
            throw new GeneralException('Auth0 creation of ' . $attributes['email'] . ' failed ' . print_r($response, true));
        }
        return $response->content;
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
        /** @var \stdClass $response */
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'users')
                         ->withData(
                             [
                                 'q'             => 'email:' . '"' . $email . '"',
                                 'search_engine' => 'v3',
                             ]
                         )
                         ->returnResponseObject()
                         ->withHeader("Cache-Control: no-cache")
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->get();

        if ( ! isset($response->content) || ! is_array($response->content) || ! isset($response->content[0]))
        {
            return [];
        }

        if (count($response->content) > 1)
        {
            Log::warning('get_user_with_email ' . $email . ' returns multiple results from Auth0 - Open a HERMES ticket');
        }

        foreach ($response->content as $content)
        {
            foreach ($content->identities as $identy)
            {
                if ($identy->connection == $identity_connection)
                {
                    return (array) $content;
                }
            }
        }
        /**
         * this probably means that the user is in Auth0 but not with $identity_connection
         */
        return [];
    }

    /**
     * @return array
     * @throws GeneralException
     */
    public function get_all_users()
    {
        $params    = [
            'per_page' => 100,
            'page'     => 0,
        ];
        $users_arr = [];
        while (1)
        {
            /** @var \stdClass $response */
            $response = $this->getCurlServiceObj()
                             ->to($this->getManagementUrl() . 'users')
                             ->withData(
                                 [
                                     'per_page'      => $params['per_page'],
                                     'page'          => $params['page'],
                                     'search_engine' => 'v3',
                                 ]
                             )
                             ->returnResponseObject()
                             ->withHeader("Cache-Control: no-cache")
                             ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                             ->asJson()
                             ->get();
            if (isset($response->statusCode) && $response->statusCode !== 200)
            {
                throw new GeneralException('Auth0Client error. Status = ' . $response->statusCode);
            }
            if ( ! is_array($response->content))
            {
                throw new GeneralException('Auth0Client error. Status = ' . $response->statusCode);
            }
            if (count($response->content))
            {
                $users_arr = array_merge(
                    is_array($users_arr) ? $users_arr : [],
                    is_array($response->content) ? $response->content : []
                );
                $params['page']++;
                continue;
            }
            break;
        }

        return $users_arr;
    }

    /**
     * @return \stdClass|array
     * @throws GeneralException
     */
    public function update_user_password_with_email($email, $new_password, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        if ( ! $auth0_user_arr = $this->get_user_with_email($email, $identity_connection))
        {
            throw new GeneralException('cannot update unknown user ' . $email);
        }

        $attributes['connection'] = $identity_connection;
        $attributes['password']   = $new_password;

        /** @var \stdClass $response */
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'users/' . $auth0_user_arr['user_id'])
                         ->withData($attributes)
                         ->returnResponseObject()
                         ->withHeader("Cache-Control: no-cache")
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->patch();
        if ( ! isset($response->status) || $response->status !== 200)
        {
            throw new GeneralException('updating user password failed ' . __FILE__ . ':' . __LINE__ . PHP_EOL . ' ------------' . PHP_EOL . print_r($response, 1));
        }
        return $response;
    }

    /**
     * @param $email
     * @return \stdClass
     * @throws GeneralException
     */
    public function delete_user_with_email($email, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        if ( ! $Auth0_user_arr = $this->get_user_with_email($email, $identity_connection))
        {
            throw new GeneralException('cannot delete unknown user' . __FILE__ . ':' . __LINE__);
        }
        /** @var \stdClass $response */
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'users/' . $Auth0_user_arr['user_id'])
                         ->returnResponseObject()
                         ->withHeader("Cache-Control: no-cache")
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->delete();
        if ( ! isset($response->status) || $response->status !== 204)
        {
            throw new GeneralException('Deleting user failed ' . __FILE__ . ':' . __LINE__ . PHP_EOL . print_r($response, 1));
        }
        return $response;
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
        $per_page = 100;
        $page     = 0;

        $query_string = '';
        foreach ($params as $paramname => $param)
        {
            if ($query_string)
            {
                $query_string .= ',';
            }
            $query_string .= $paramname . ':' . $param;
        }
        $users_arr = [];
        while (1)
        {
            /** @var \stdClass $response */
            $response = $this->getCurlServiceObj()
                             ->to($this->getManagementUrl() . 'users')
                             ->withData(
                                 [
                                     'q'        => $query_string,
                                     'per_page' => $per_page,
                                     'page'     => $page,
                                 ]
                             )
                             ->returnResponseObject()
                             ->withHeader("Cache-Control: no-cache")
                             ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                             ->asJson()
                             ->get();

            if (is_array($response->content) && count($response->content))
            {
                $users_arr = array_merge($users_arr, $response->content);
                if (count($response->content) < $per_page)
                {
                    break;
                }
                $page++;
                continue;
            }
            break;
        }
        return $users_arr;
    }

    /**
     * @param string $email
     * @return array
     * @throws GeneralException
     */
    public function get_user_logs($email, $identity_connection = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
    {
        if ( ! $UserObj = $this->get_user_with_email($email, $identity_connection))
        {
            throw new GeneralException('Unknown user');
        }
        $per_page = 100;
        $page     = 0;

        $user_log_arr = [];
        while (1)
        {
            /** @var \stdClass $response */
            $response = $this->getCurlServiceObj()
                             ->to($this->getManagementUrl() . 'users/' . $UserObj->user_id . '/logs')
                             ->withData(
                                 [
                                     'per_page' => $per_page,
                                     'page'     => $page,
                                 ]
                             )
                             ->returnResponseObject()
                             ->withHeader("Cache-Control: no-cache")
                             ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                             ->asJson()
                             ->get();

            if (is_array($response->content) && count($response->content))
            {
                $user_log_arr = array_merge($user_log_arr, $response->content);
                if (count($response->content) < $per_page)
                {
                    break;
                }
                $page++;
                continue;
            }
            break;
        }
        return $user_log_arr;

    }
}
