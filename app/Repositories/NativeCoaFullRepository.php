<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeCoaFull;
use App;

/**
 * Class NativeCoaRepository
 * @package App\Waypoint\Repositories
 */
class NativeCoaFullRepository extends NativeCoaRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return NativeCoaFull::class;
    }
}
