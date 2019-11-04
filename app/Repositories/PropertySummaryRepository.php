<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertySummary;

/**
 * Class PropertySummaryRepository
 * @package App\Waypoint\Repositories
 */
class PropertySummaryRepository extends PropertyRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return PropertySummary::class;
    }
}
