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
use App\Waypoint\Models\Lease;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeLeaseTrait
{
    /**
     * Create fake instance of Lease and save it in database
     *
     * @param array $leases_arr
     * @return Lease
     */
    public function makeLease($leases_arr = [])
    {
        $theme = $this->fakeLeaseData($leases_arr);
        return $this->LeaseRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Lease
     *
     * @param array $leases_arr
     * @return Lease
     */
    public function fakeLease($leases_arr = [])
    {
        return new Lease($this->fakeLeaseData($leases_arr));
    }

    /**
     * Get fake data of Lease
     *
     * @param array $leases_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeLeaseData($leases_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($leases_arr);
        return $factory->raw(Lease::class, $leases_arr, $factory_name);
    }
}