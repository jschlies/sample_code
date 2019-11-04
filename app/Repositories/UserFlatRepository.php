<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserFlat;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserFlatRepository extends UserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserFlat::class;
    }
}