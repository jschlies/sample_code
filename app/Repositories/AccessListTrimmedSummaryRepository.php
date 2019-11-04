<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListTrimmedSummary;

/**
 * Class AccessListSummaryRepository
 * @package App\Waypoint\Repositories
 */
class AccessListTrimmedSummaryRepository extends AccessListRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListTrimmedSummary::class;
    }
}
