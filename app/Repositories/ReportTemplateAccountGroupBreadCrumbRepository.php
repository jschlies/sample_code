<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateAccountGroupBreadCrumb;

/**
 * Class BomaClientMappingInboundDetailRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateAccountGroupBreadCrumbRepository extends ReportTemplateAccountGroupRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return ReportTemplateAccountGroupBreadCrumb::class;
    }
}