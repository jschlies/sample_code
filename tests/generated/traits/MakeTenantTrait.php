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
use App\Waypoint\Models\Tenant;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeTenantTrait
{
    /**
     * Create fake instance of Tenant and save it in database
     *
     * @param array $tenants_arr
     * @return Tenant
     */
    public function makeTenant($tenants_arr = [])
    {
        $theme = $this->fakeTenantData($tenants_arr);
        return $this->TenantRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Tenant
     *
     * @param array $tenants_arr
     * @return Tenant
     */
    public function fakeTenant($tenants_arr = [])
    {
        return new Tenant($this->fakeTenantData($tenants_arr));
    }

    /**
     * Get fake data of Tenant
     *
     * @param array $tenants_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeTenantData($tenants_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($tenants_arr);
        return $factory->raw(Tenant::class, $tenants_arr, $factory_name);
    }
}