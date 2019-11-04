<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldFull;

/**
 * Class CalculatedFieldRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldFullRepository extends CalculatedFieldRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldFull::class;
    }
}
