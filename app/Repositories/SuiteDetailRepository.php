<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\SuiteDetail;

/**
 * Class SuiteRepository
 * @package App\Waypoint\Repositories
 */
class SuiteDetailRepository extends SuiteRepositoryBase
{

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SuiteDetail::class;
    }
}
