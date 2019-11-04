<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantAttributeDetail;

/**
 * Class TenantIndustryRepository
 * @package App\Waypoint\Repositories
 */
class TenantAttributeDetailRepository extends TenantIndustryRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantAttributeDetail::class;
    }
}
