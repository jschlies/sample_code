<?php

namespace App\Waypoint\Auth0;

/**
 * Class Auth0ClientCredential
 * @package App\Waypoint\Repositories
 */
class Auth0ClientCredential
{
    protected $access_token;
    protected $expires_in;
    protected $scope;
    protected $token_type;

    public function __construct($access_token, $expires_in, $scope, $token_type)
    {
        $this->access_token = $access_token;
        $this->expires_in   = $expires_in;
        $this->scope        = $scope;
        $this->token_type   = $token_type;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param mixed $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @return mixed
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @param mixed $expires_in
     */
    public function setExpiresIn($expires_in)
    {
        $this->expires_in = $expires_in;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * @param mixed $token_type
     */
    public function setTokenType($token_type)
    {
        $this->token_type = $token_type;
    }
}
