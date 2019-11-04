<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldVariable;

/**
 * Class CalculatedFieldVariableRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldVariableRepository extends CalculatedFieldVariableRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldVariable::class;
    }
}
