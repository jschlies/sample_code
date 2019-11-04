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
use App\Waypoint\Models\PropertyNativeCoa;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePropertyNativeCoaTrait
{
    /**
     * Create fake instance of PropertyNativeCoa and save it in database
     *
     * @param array $property_native_coas_arr
     * @return PropertyNativeCoa
     */
    public function makePropertyNativeCoa($property_native_coas_arr = [])
    {
        $theme = $this->fakePropertyNativeCoaData($property_native_coas_arr);
        return $this->PropertyNativeCoaRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of PropertyNativeCoa
     *
     * @param array $property_native_coas_arr
     * @return PropertyNativeCoa
     */
    public function fakePropertyNativeCoa($property_native_coas_arr = [])
    {
        return new PropertyNativeCoa($this->fakePropertyNativeCoaData($property_native_coas_arr));
    }

    /**
     * Get fake data of PropertyNativeCoa
     *
     * @param array $property_native_coas_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePropertyNativeCoaData($property_native_coas_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($property_native_coas_arr);
        return $factory->raw(PropertyNativeCoa::class, $property_native_coas_arr, $factory_name);
    }
}