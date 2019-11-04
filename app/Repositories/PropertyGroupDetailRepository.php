<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyGroupDetail;

/**
 * Class PropertyGroupDetailRepository
 * @package App\Waypoint\Repositories
 */
class PropertyGroupDetailRepository extends PropertyGroupRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return PropertyGroupDetail::class;
    }
}