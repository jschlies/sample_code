<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeAccountTypeDetail;
use DB;

/**
 * Class NativeAccountTypeDetailRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountTypeDetailRepository extends NativeAccountTypeRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountTypeDetail::class;
    }
}
