<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\RoleDetail;

/**
 * Class RoleDetailRepository
 * @package App\Waypoint\Repositories
 */
class RoleDetailRepository extends RoleRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return RoleDetail::class;
    }
}
