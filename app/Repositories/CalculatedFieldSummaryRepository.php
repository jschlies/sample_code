<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldSummary;

/**
 * Class CalculatedFieldRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldSummaryRepository extends CalculatedFieldRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldSummary::class;
    }
}
