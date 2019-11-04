<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\SuiteLease;

/**
 * Class SuiteLeaseRepository
 * @package App\Waypoint\Repositories
 */
class SuiteLeaseRepository extends SuiteLeaseRepositoryBase
{
    /**
     * Configure the Repository
     **/
    public function model()
    {
        return SuiteLease::class;
    }
}
