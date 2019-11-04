<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\RoleUser;

/**
 * Class RoleUserRepository
 * @package App\Waypoint\Repositories
 */
class RoleUserRepository extends RoleUserRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return RoleUser::class;
    }
}
