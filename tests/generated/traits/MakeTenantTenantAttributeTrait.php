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
use App\Waypoint\Models\TenantTenantAttribute;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeTenantTenantAttributeTrait
{
    /**
     * Create fake instance of TenantTenantAttribute and save it in database
     *
     * @param array $tenant_tenant_attributes_arr
     * @return TenantTenantAttribute
     */
    public function makeTenantTenantAttribute($tenant_tenant_attributes_arr = [])
    {
        $theme = $this->fakeTenantTenantAttributeData($tenant_tenant_attributes_arr);
        return $this->TenantTenantAttributeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of TenantTenantAttribute
     *
     * @param array $tenant_tenant_attributes_arr
     * @return TenantTenantAttribute
     */
    public function fakeTenantTenantAttribute($tenant_tenant_attributes_arr = [])
    {
        return new TenantTenantAttribute($this->fakeTenantTenantAttributeData($tenant_tenant_attributes_arr));
    }

    /**
     * Get fake data of TenantTenantAttribute
     *
     * @param array $tenant_tenant_attributes_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeTenantTenantAttributeData($tenant_tenant_attributes_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($tenant_tenant_attributes_arr);
        return $factory->raw(TenantTenantAttribute::class, $tenant_tenant_attributes_arr, $factory_name);
    }
}