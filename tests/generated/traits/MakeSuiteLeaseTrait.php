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
use App\Waypoint\Models\SuiteLease;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeSuiteLeaseTrait
{
    /**
     * Create fake instance of SuiteLease and save it in database
     *
     * @param array $suite_leases_arr
     * @return SuiteLease
     */
    public function makeSuiteLease($suite_leases_arr = [])
    {
        $theme = $this->fakeSuiteLeaseData($suite_leases_arr);
        return $this->SuiteLeaseRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of SuiteLease
     *
     * @param array $suite_leases_arr
     * @return SuiteLease
     */
    public function fakeSuiteLease($suite_leases_arr = [])
    {
        return new SuiteLease($this->fakeSuiteLeaseData($suite_leases_arr));
    }

    /**
     * Get fake data of SuiteLease
     *
     * @param array $suite_leases_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeSuiteLeaseData($suite_leases_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($suite_leases_arr);
        return $factory->raw(SuiteLease::class, $suite_leases_arr, $factory_name);
    }
}