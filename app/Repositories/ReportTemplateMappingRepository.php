<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateMapping;

use App;

/**
 * Class ReportTemplateMappingRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateMappingRepository extends ReportTemplateMappingRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplateMapping::class;
    }
}
