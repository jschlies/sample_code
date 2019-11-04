<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateFull;

/**
 * Class ReportTemplateFullRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateFullRepository extends ReportTemplateRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return ReportTemplateFull::class;
    }
}
