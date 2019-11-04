<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeAccountDetail;

/**
 * Class NativeAccountRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountDetailRepository extends NativeAccountRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountDetail::class;
    }
}
