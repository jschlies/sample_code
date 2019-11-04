<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserSlim;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserSlimRepository extends UserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserSlim::class;
    }
}