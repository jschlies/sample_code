<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyFull;

/**
 * Class PropertyFullRepository
 * @package App\Waypoint\Repositories
 */
class PropertyFullRepository extends PropertyRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return PropertyFull::class;
    }
}
