<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateMappingFull;
use App;

/**
 * Class ReportTemplateMappingRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateMappingFullRepository extends ReportTemplateMappingRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplateMappingFull::class;
    }
}
