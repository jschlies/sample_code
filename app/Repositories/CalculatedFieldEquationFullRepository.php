<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldEquationFull;

/**
 * Class CalculatedFieldEquationRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldEquationFullRepository extends CalculatedFieldEquationRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldEquationFull::class;
    }
}
