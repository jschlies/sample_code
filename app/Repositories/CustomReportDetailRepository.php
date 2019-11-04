<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CustomReportDetail;

class CustomReportDetailRepository extends CustomReportRepositoryBase
{

    /**
     * Configure the Model
     *
     **/
    public function model()
    {
        return CustomReportDetail::class;
    }
}