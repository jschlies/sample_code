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
use App\Waypoint\Models\SuiteTenant;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeSuiteTenantTrait
{
    /**
     * Create fake instance of SuiteTenant and save it in database
     *
     * @param array $suite_tenants_arr
     * @return SuiteTenant
     */
    public function makeSuiteTenant($suite_tenants_arr = [])
    {
        $theme = $this->fakeSuiteTenantData($suite_tenants_arr);
        return $this->SuiteTenantRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of SuiteTenant
     *
     * @param array $suite_tenants_arr
     * @return SuiteTenant
     */
    public function fakeSuiteTenant($suite_tenants_arr = [])
    {
        return new SuiteTenant($this->fakeSuiteTenantData($suite_tenants_arr));
    }

    /**
     * Get fake data of SuiteTenant
     *
     * @param array $suite_tenants_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeSuiteTenantData($suite_tenants_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($suite_tenants_arr);
        return $factory->raw(SuiteTenant::class, $suite_tenants_arr, $factory_name);
    }
}