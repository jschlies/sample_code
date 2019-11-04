<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\ReportTemplateDetail;

/**
 * Class ReportTemplateRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateDetailRepository extends ReportTemplateRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplateDetail::class;
    }
}
