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
use App\Waypoint\Models\FailedJob;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeFailedJobTrait
{
    /**
     * Create fake instance of FailedJob and save it in database
     *
     * @param array $failed_jobs_arr
     * @return FailedJob
     */
    public function makeFailedJob($failed_jobs_arr = [])
    {
        $theme = $this->fakeFailedJobData($failed_jobs_arr);
        return $this->FailedJobRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of FailedJob
     *
     * @param array $failed_jobs_arr
     * @return FailedJob
     */
    public function fakeFailedJob($failed_jobs_arr = [])
    {
        return new FailedJob($this->fakeFailedJobData($failed_jobs_arr));
    }

    /**
     * Get fake data of FailedJob
     *
     * @param array $failed_jobs_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeFailedJobData($failed_jobs_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($failed_jobs_arr);
        return $factory->raw(FailedJob::class, $failed_jobs_arr, $factory_name);
    }
}