<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AdvancedVarianceLineItemWorkflow;

class AdvancedVarianceLineItemWorkflowRepository extends AdvancedVarianceLineItemRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceLineItemWorkflow::class;
    }
}
