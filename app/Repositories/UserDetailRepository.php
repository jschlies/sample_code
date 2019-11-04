<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserDetail;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserDetailRepository extends UserRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return UserDetail::class;
    }
}