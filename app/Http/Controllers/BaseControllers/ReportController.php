<?php

namespace App\Waypoint\Http;

use App\Waypoint\Http\Controller as ControllerBase;
use App\Waypoint\Repository;

/**
 * Class ReportController
 * @package App\Waypoint\Http
 */
class ReportController extends ControllerBase
{
    public function __construct(Repository $Repository)
    {
        /**
         * here for future use
         */
        parent::__construct($Repository);
    }
}
