<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListPropertySummary;

/**
 * Class AccessListPropertyRepository
 * @package App\Waypoint\Repositories
 */
class AccessListPropertySummaryRepository extends AccessListPropertyRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListPropertySummary::class;
    }

    /**
     * @return string
     */
    public function all($columns = ['*'])
    {
        return $this->with('accessListUsers')->with('accessListProperties')->all();
    }
}

