<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyGroupFull;

/**
 * Class PropertyGroupFullRepository
 * @package App\Waypoint\Repositories
 */
class PropertyGroupFullRepository extends PropertyGroupRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return PropertyGroupFull::class;
    }
}