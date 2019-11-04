<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceSummary;

class AdvancedVarianceSummaryRepository extends AdvancedVarianceRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceSummary::class;
    }
}
