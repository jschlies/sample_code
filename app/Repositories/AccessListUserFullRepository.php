<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListUserFull;

/**
 * Class AccessListUserRepository
 * @package App\Waypoint\Repositories
 */
class AccessListUserFullRepository extends AccessListUserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return AccessListUserFull::class;
    }
}
