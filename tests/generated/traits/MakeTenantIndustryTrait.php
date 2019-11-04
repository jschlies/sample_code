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
use App\Waypoint\Models\TenantIndustry;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeTenantIndustryTrait
{
    /**
     * Create fake instance of TenantIndustry and save it in database
     *
     * @param array $tenant_industries_arr
     * @return TenantIndustry
     */
    public function makeTenantIndustry($tenant_industries_arr = [])
    {
        $theme = $this->fakeTenantIndustryData($tenant_industries_arr);
        return $this->TenantIndustryRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of TenantIndustry
     *
     * @param array $tenant_industries_arr
     * @return TenantIndustry
     */
    public function fakeTenantIndustry($tenant_industries_arr = [])
    {
        return new TenantIndustry($this->fakeTenantIndustryData($tenant_industries_arr));
    }

    /**
     * Get fake data of TenantIndustry
     *
     * @param array $tenant_industries_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeTenantIndustryData($tenant_industries_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($tenant_industries_arr);
        return $factory->raw(TenantIndustry::class, $tenant_industries_arr, $factory_name);
    }
}