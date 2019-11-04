<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantIndustry;

/**
 * Class TenantIndustryRepository
 * @package App\Waypoint\Repositories
 */
class TenantIndustryRepository extends TenantIndustryRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantIndustry::class;
    }
}
