<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertySlim;

/**
 * Class PropertySlimRepository
 * @package App\Waypoint\Repositories
 */
class PropertySlimRepository extends PropertyRepository
{
    public function model()
    {
        return PropertySlim::class;
    }
}