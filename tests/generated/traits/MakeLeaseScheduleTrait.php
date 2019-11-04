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
use App\Waypoint\Models\LeaseSchedule;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeLeaseScheduleTrait
{
    /**
     * Create fake instance of LeaseSchedule and save it in database
     *
     * @param array $lease_schedules_arr
     * @return LeaseSchedule
     */
    public function makeLeaseSchedule($lease_schedules_arr = [])
    {
        $theme = $this->fakeLeaseScheduleData($lease_schedules_arr);
        return $this->LeaseScheduleRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of LeaseSchedule
     *
     * @param array $lease_schedules_arr
     * @return LeaseSchedule
     */
    public function fakeLeaseSchedule($lease_schedules_arr = [])
    {
        return new LeaseSchedule($this->fakeLeaseScheduleData($lease_schedules_arr));
    }

    /**
     * Get fake data of LeaseSchedule
     *
     * @param array $lease_schedules_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeLeaseScheduleData($lease_schedules_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($lease_schedules_arr);
        return $factory->raw(LeaseSchedule::class, $lease_schedules_arr, $factory_name);
    }
}