<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateAccountGroupFull;

/**
 * Class ReportTemplateAccountGroupRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateAccountGroupFullRepository extends ReportTemplateAccountGroupRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplateAccountGroupFull::class;
    }
}
