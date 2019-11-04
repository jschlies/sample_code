<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyDetail;

/**
 * Class PropertySummaryRepository
 * @package App\Waypoint\Repositories
 */
class PropertyDetailRepository extends PropertyRepository
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return PropertyDetail::class;
    }
}
