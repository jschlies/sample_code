<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\LeaseTenant;

/**
 * Class LeaseTenantRepository
 * @package App\Waypoint\Repositories
 */
class LeaseTenantRepository extends LeaseTenantRepositoryBase
{

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return LeaseTenant::class;
    }
}
