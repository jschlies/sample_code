<?php

namespace App\Waypoint\Repositories;

use \App\Waypoint\Repository;
use App\Waypoint\Models\Home;

/**
 * Class HomeRepository
 * @package App\Waypoint\Repositories
 */
class HomeRepository extends Repository
{
    public function model()
    {
        return Home::class;
    }
}
