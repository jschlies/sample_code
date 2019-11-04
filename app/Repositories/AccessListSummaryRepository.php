<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListSummary;

/**
 * Class AccessListSummaryRepository
 * @package App\Waypoint\Repositories
 */
class AccessListSummaryRepository extends AccessListRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListSummary::class;
    }
}
