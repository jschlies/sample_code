<?php

namespace App\Waypoint\Auth0;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class Auth0UserMock
 * @package App\Waypoint\Repositories
 */
class Auth0UserMock
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What, you crazy!!!!! No RentRollMockRepository in production context ' . __FILE__);
        }
    }

}
