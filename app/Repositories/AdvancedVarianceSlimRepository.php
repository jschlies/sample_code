<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AdvancedVarianceSlim;

class AdvancedVarianceSlimRepository extends AdvancedVarianceRepository
{
    public function model()
    {
        return AdvancedVarianceSlim::class;
    }
}
