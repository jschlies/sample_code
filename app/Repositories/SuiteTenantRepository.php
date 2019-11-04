<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\SuiteTenant;

/**
 * Class SuiteTenantRepository
 * @package App\Waypoint\Repositories
 */
class SuiteTenantRepository extends SuiteTenantRepositoryBase
{

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return SuiteTenant::class;
    }
}
