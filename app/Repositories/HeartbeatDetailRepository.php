<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\HeartbeatDetail;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class HeartbeatDetailRepository extends HeartbeatRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return HeartbeatDetail::class;
    }
}