<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PermissionRole;

/**
 * Class PermissionRoleRepository
 * @package App\Waypoint\Repositories
 */
class PermissionRoleRepository extends PermissionRoleRepositoryBase
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return PermissionRole::class;
    }
}
