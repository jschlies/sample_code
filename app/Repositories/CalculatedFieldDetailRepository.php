<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CalculatedFieldDetail;

/**
 * Class CalculatedFieldRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldDetailRepository extends CalculatedFieldRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldDetail::class;
    }
}
