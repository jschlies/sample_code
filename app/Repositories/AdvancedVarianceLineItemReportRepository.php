<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AdvancedVarianceLineItemReport;

/**
 * Class AdvancedVarianceLineItemRepository
 */
class AdvancedVarianceLineItemReportRepository extends AdvancedVarianceLineItemRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceLineItemReport::class;
    }
}
