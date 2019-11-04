<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessListDetail;

/**
 * Class AccessListDetailRepository
 * @package App\Waypoint\Repositories
 */
class AccessListDetailRepository extends AccessListRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessListDetail::class;
    }

    /**
     * @return string
     */
    public function allDetail($columns = ['*'])
    {
        return $this->with('accessListUsers')->with('accessListProperties')->all();
    }
}
