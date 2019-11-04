<?php

namespace App\Waypoint\Auth0;

use App\Waypoint\Exceptions\GeneralException;

/**
 * Class Auth0ApiManagementConnection
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementConnection extends Auth0ApiManagement
{
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
     * @return array
     * @throws GeneralException
     */
    public function get_connections()
    {
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'connections')
                         ->returnResponseObject()
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->get();

        if (isset($response->content) && is_array($response->content))
        {
            return $response->content;
        }
        throw new GeneralException('Auth0 failed get_connections', 500);
    }

    /**
     * see https://github.com/ixudra/curl
     *
     * @param array $attributes
     * @return bool
     * @throws GeneralException
     */
    public function get_connections_with_name($connection_name)
    {
        $response = $this->getCurlServiceObj()
                         ->to($this->getManagementUrl() . 'connections')
                         ->withData(['name' => $connection_name])
                         ->returnResponseObject()
                         ->withHeader("Authorization: Bearer " . $this->getAuth0ClientCredentialObj()->getAccessToken())
                         ->asJson()
                         ->get();
        if (isset($response->content) && is_array($response->content))
        {
            return isset($response->content[0]) ? $response->content[0] : false;
        }
        throw new GeneralException('Auth0 failed get_connections_with_name', 500);
    }
}
