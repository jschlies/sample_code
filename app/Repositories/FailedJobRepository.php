<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\FailedJob;

/**
 * Class FailedJobRepository
 * @package App\Waypoint\Repositories
 */
class FailedJobRepository extends FailedJobRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return FailedJob::class;
    }
}
