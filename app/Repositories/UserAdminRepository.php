<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserAdmin;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserAdminRepository extends UserRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return UserAdmin::class;
    }
}