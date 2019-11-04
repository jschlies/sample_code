<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\Role;

class RoleRepository extends RoleRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return Role::class;
    }
}
