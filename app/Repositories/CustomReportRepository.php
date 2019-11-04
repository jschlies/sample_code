<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CustomReport;

class CustomReportRepository extends CustomReportRepositoryBase
{

    /**
     * Configure the Model
     *
     **/
    public function model()
    {
        return CustomReport::class;
    }
}