<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyGroupLeaseRollup;

/**
 * Class PropertySummaryRepository
 * @package App\Waypoint\Repositories
 */
class PropertyGroupLeaseRollupRepository extends PropertyGroupRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return PropertyGroupLeaseRollup::class;
    }
}
