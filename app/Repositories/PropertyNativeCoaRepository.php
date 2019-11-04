<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyNativeCoa;

/**
 * Class NativeCoaLedgerRepository
 * @package App\Waypoint\Repositories
 */
class PropertyNativeCoaRepository extends PropertyNativeCoaRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return PropertyNativeCoa::class;
    }
}
