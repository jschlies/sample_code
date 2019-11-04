<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceSkinny;

/**
 * Class PropertyRepository
 */
class AdvancedVarianceSkinnyRepository extends AdvancedVarianceRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceSkinny::class;
    }
}
