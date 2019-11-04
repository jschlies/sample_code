<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantTenantAttribute;

/**
 * Class TenantTenantAttributeRepository
 * @package App\Waypoint\Repositories
 */
class TenantTenantAttributeRepository extends TenantTenantAttributeRepositoryBase
{

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantTenantAttribute::class;
    }
}
