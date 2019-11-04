<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceFull;

/**
 * Class PropertyRepository
 */
class AdvancedVarianceFullRepository extends AdvancedVarianceRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceFull::class;
    }
}
