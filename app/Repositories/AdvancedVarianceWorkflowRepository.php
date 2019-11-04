<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceWorkflow;

class AdvancedVarianceWorkflowRepository extends AdvancedVarianceRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceWorkflow::class;
    }
}