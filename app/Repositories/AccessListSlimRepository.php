<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListSlim;

/**
 * Class AccessListDetailRepository
 * @package App\Waypoint\Repositories
 */
class AccessListSlimRepository extends AccessListRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListSlim::class;
    }

    /**
     * @return string
     */
    public function allDetail($columns = ['*'])
    {
        return $this->with('accessListUsers')->with('accessListProperties')->all();
    }
}
