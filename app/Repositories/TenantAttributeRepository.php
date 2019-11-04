<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantAttribute;

/**
 * Class TenantIndustryRepository
 * @package App\Waypoint\Repositories
 */
class TenantAttributeRepository extends TenantIndustryRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantAttribute::class;
    }
}
