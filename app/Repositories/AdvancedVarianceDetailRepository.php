<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceDetail;

class AdvancedVarianceDetailRepository extends AdvancedVarianceRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceDetail::class;
    }
}
