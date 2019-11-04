<?php

namespace App\Waypoint\Auth0;

use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class Auth0ApiManagement
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagement
{
    CONST AUTH0_MANAGEMENT_API_VERSION = 'v2';

    /** @var  Auth0ClientCredential */
    private $Auth0ClientCredentialObj;

    private $auth0_domain = null;
    private $management_client_id = null;
    private $management_client_secret = null;
    private $management_audience = null;
    private $management_url = null;
    private $management_ttl = null;

    use CurlServiceTrait;

    /**
     * @return null
     */
    public function getManagementUrl()
    {
        if ( ! $this->management_url)
        {
            $this->setManagementUrl('https://' . $this->getAuth0Domain() . '/api/' . self::AUTH0_MANAGEMENT_API_VERSION . '/');
        }
        return $this->management_url;
    }

    /**
     * @param null $management_url
     */
    public function setManagementUrl($management_url)
    {
        $this->management_url = $management_url;
    }

    /**
     * Management constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getAuth0Domain()
    {
        if ( ! $this->auth0_domain)
        {
            $this->auth0_domain = config('waypoint.management_auth0_domain', false);
        }
        return $this->auth0_domain;
    }

    /**
     * @param mixed $auth0_domain
     */
    public function setAuth0Domain($auth0_domain)
    {
        $this->auth0_domain = $auth0_domain;
    }

    /**
     * @return mixed
     */
    public function getManagementClientId()
    {
        if ( ! $this->management_client_id)
        {
            $this->setManagementClientId(config('waypoint.management_client_id', false));
        }
        return $this->management_client_id;
    }

    /**
     * @param mixed $management_client_id
     */
    public function setManagementClientId($management_client_id)
    {
        $this->management_client_id = $management_client_id;
    }

    /**
     * @return mixed
     */
    public function getManagementClientSecret()
    {
        if ( ! $this->management_client_secret)
        {
            $this->setManagementClientSecret(config('waypoint.management_client_secret', false));
        }
        return $this->management_client_secret;
    }

    /**
     * @param mixed $management_client_secret
     */
    public function setManagementClientSecret($management_client_secret)
    {
        $this->management_client_secret = $management_client_secret;
    }

    /**
     * @return mixed
     */
    public function getManagementAudience()
    {
        if ( ! $this->management_audience)
        {
            $this->setManagementAudience(config('waypoint.management_audience', false));
        }
        return $this->management_audience;
    }

    /**
     * @param mixed $management_audience
     */
    public function setManagementAudience($management_audience)
    {
        $this->management_audience = $management_audience;
    }

    /**
     * @return mixed
     */
    public function getManagementTtl()
    {
        if ( ! $this->management_ttl)
        {
            $this->setManagementTtl(config('waypoint.management_auth0_ttl', false));
        }
        return $this->management_ttl;
    }

    /**
     * @param $ttl
     */
    public function setManagementTtl($ttl)
    {
        $this->management_ttl = $ttl;
    }

    /**
     * @return Auth0ClientCredential
     * @throws GeneralException
     */
    public function getAuth0ClientCredentialObj()
    {
        if ( ! $this->Auth0ClientCredentialObj)
        {
            $curl    = curl_init();
            $payload = [
                "grant_type"    => "client_credentials",
                "client_id"     => $this->getManagementClientId(),
                "client_secret" => $this->getManagementClientSecret(),
                "audience"      => $this->getManagementAudience(),
            ];

            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL            => 'https://' . config('waypoint.management_auth0_domain', false) . '/oauth/token',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => "POST",
                    CURLOPT_POSTFIELDS     => json_encode($payload),
                    CURLOPT_HTTPHEADER     => [
                        "content-type: application/json",
                    ],
                ]
            );

            $response = curl_exec($curl);
            $err      = curl_error($curl);

            curl_close($curl);

            if ($err)
            {
                throw new GeneralException('Auth0ApiManagement failure ' . $response, 500);
            }

            $JSON_response = json_decode($response);
            $this->setAuth0ClientCredentialObj(
                new Auth0ClientCredential(
                    isset($JSON_response->access_token) ? $JSON_response->access_token : null,
                    isset($JSON_response->expires_in) ? $JSON_response->expires_in : null,
                    isset($JSON_response->scope) ? $JSON_response->scope : null,
                    isset($JSON_response->token_type) ? $JSON_response->token_type : null
                )
            );
        }
        return $this->Auth0ClientCredentialObj;
    }

    /**
     * @param Auth0ClientCredential $Auth0ClientCredentialObj
     */
    public function setAuth0ClientCredentialObj($Auth0ClientCredentialObj)
    {
        $this->Auth0ClientCredentialObj = $Auth0ClientCredentialObj;
    }
}
