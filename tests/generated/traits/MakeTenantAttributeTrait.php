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
use App\Waypoint\Models\TenantAttribute;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeTenantAttributeTrait
{
    /**
     * Create fake instance of TenantAttribute and save it in database
     *
     * @param array $tenant_attributes_arr
     * @return TenantAttribute
     */
    public function makeTenantAttribute($tenant_attributes_arr = [])
    {
        $theme = $this->fakeTenantAttributeData($tenant_attributes_arr);
        return $this->TenantAttributeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of TenantAttribute
     *
     * @param array $tenant_attributes_arr
     * @return TenantAttribute
     */
    public function fakeTenantAttribute($tenant_attributes_arr = [])
    {
        return new TenantAttribute($this->fakeTenantAttributeData($tenant_attributes_arr));
    }

    /**
     * Get fake data of TenantAttribute
     *
     * @param array $tenant_attributes_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeTenantAttributeData($tenant_attributes_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($tenant_attributes_arr);
        return $factory->raw(TenantAttribute::class, $tenant_attributes_arr, $factory_name);
    }
}