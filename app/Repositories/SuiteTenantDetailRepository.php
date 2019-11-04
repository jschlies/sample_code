<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\SuiteTenantDetail;

/**
 * Class SuiteTenantRepository
 * @package App\Waypoint\Repositories
 */
class SuiteTenantDetailRepository extends SuiteTenantRepository
{

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return SuiteTenantDetail::class;
    }
}
