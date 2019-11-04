<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\Tenant;
use App;

/**
 * Class TenantRepository
 * @package App\Waypoint\Repositories
 */
class TenantRepository extends TenantRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return Tenant::class;
    }
}
