<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListPropertyFull;

/**
 * Class AccessListPropertyRepository
 * @package App\Waypoint\Repositories
 */
class AccessListPropertyFullRepository extends AccessListPropertyRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return AccessListPropertyFull::class;
    }
}

