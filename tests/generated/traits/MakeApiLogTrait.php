<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\ApiLog;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeApiLogTrait
{
    /**
     * Create fake instance of ApiLog and save it in database
     *
     * @param array $api_logs_arr
     * @return ApiLog
     */
    public function makeApiLog($api_logs_arr = [])
    {
        $theme = $this->fakeApiLogData($api_logs_arr);
        return $this->ApiLogRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ApiLog
     *
     * @param array $api_logs_arr
     * @return ApiLog
     */
    public function fakeApiLog($api_logs_arr = [])
    {
        return new ApiLog($this->fakeApiLogData($api_logs_arr));
    }

    /**
     * Get fake data of ApiLog
     *
     * @param array $api_logs_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeApiLogData($api_logs_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($api_logs_arr);
        return $factory->raw(ApiLog::class, $api_logs_arr, $factory_name);
    }
}