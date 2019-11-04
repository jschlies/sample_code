<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantTenantAttributeDetail;

/**
 * Class TenantTenantAttributeRepository
 * @package App\Waypoint\Repositories
 */
class TenantTenantAttributeDetailRepository extends TenantTenantAttributeRepositoryBase
{

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantTenantAttributeDetail::class;
    }
}
