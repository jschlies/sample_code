<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantIndustryDetail;

/**
 * Class TenantIndustryRepository
 * @package App\Waypoint\Repositories
 */
class TenantIndustryDetailRepository extends TenantIndustryRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantIndustryDetail::class;
    }
}
