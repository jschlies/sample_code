<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserSummary;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserSummaryRepository extends UserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserSummary::class;
    }
}