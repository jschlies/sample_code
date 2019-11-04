<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\TenantDetail;

/**
 * Class TenantRepository
 * @package App\Waypoint\Repositories
 */
class TenantDetailRepository extends TenantRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return TenantDetail::class;
    }
}
