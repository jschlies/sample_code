<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListFull;

/**
 * Class AccessListFullRepository
 * @package App\Waypoint\Repositories
 */
class AccessListFullRepository extends AccessListRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListFull::class;
    }
}
