<?php

namespace App\Waypoint\Tests\Mocks;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class Auth0ApiManagementUser
 * @package App\Waypoint\Repositories
 */
class Auth0ApiManagementConnectionMock
{
    /**
     * Auth0ApiManagementConnectionMock constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What, you crazy!!!!! No Auth0ApiManagementConnectionMock in production context ' . __FILE__);
        }
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
        return [];
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
        return true;
    }
}