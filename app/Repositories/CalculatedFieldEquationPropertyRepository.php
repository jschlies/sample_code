<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldEquationProperty;

/**
 * Class CalculatedFieldEquationPropertyRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldEquationPropertyRepository extends CalculatedFieldEquationRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldEquationProperty::class;
    }
}
