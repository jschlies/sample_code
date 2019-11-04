<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\LeaseDetail;

/**
 * Class LeaseRepository
 * @package App\Waypoint\Repositories
 */
class LeaseDetailRepository extends LeaseRepository
{

    /**
     * Configure the Model
     **/
    public function model()
    {
        return LeaseDetail::class;
    }
}
