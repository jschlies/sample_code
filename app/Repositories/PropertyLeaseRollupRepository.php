<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyLeaseRollup;

/**
 * Class PropertySummaryRepository
 * @package App\Waypoint\Repositories
 */
class PropertyLeaseRollupRepository extends PropertyRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return PropertyLeaseRollup::class;
    }
}
