<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\Suite;

/**
 * Class SuiteRepository
 * @package App\Waypoint\Repositories
 */
class SuiteRepository extends SuiteRepositoryBase
{
    public function create(array $attributes)
    {
        return parent::create($attributes);
    }

    /**
     * Configure the Repository
     **/
    public function model()
    {
        return Suite::class;
    }
}
