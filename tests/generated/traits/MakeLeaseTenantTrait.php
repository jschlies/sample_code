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
use App\Waypoint\Models\LeaseTenant;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeLeaseTenantTrait
{
    /**
     * Create fake instance of LeaseTenant and save it in database
     *
     * @param array $lease_tenants_arr
     * @return LeaseTenant
     */
    public function makeLeaseTenant($lease_tenants_arr = [])
    {
        $theme = $this->fakeLeaseTenantData($lease_tenants_arr);
        return $this->LeaseTenantRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of LeaseTenant
     *
     * @param array $lease_tenants_arr
     * @return LeaseTenant
     */
    public function fakeLeaseTenant($lease_tenants_arr = [])
    {
        return new LeaseTenant($this->fakeLeaseTenantData($lease_tenants_arr));
    }

    /**
     * Get fake data of LeaseTenant
     *
     * @param array $lease_tenants_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeLeaseTenantData($lease_tenants_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($lease_tenants_arr);
        return $factory->raw(LeaseTenant::class, $lease_tenants_arr, $factory_name);
    }
}