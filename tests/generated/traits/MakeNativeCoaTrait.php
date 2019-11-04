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
use App\Waypoint\Models\NativeCoa;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNativeCoaTrait
{
    /**
     * Create fake instance of NativeCoa and save it in database
     *
     * @param array $native_coas_arr
     * @return NativeCoa
     */
    public function makeNativeCoa($native_coas_arr = [])
    {
        $theme = $this->fakeNativeCoaData($native_coas_arr);
        return $this->NativeCoaRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NativeCoa
     *
     * @param array $native_coas_arr
     * @return NativeCoa
     */
    public function fakeNativeCoa($native_coas_arr = [])
    {
        return new NativeCoa($this->fakeNativeCoaData($native_coas_arr));
    }

    /**
     * Get fake data of NativeCoa
     *
     * @param array $native_coas_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNativeCoaData($native_coas_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($native_coas_arr);
        return $factory->raw(NativeCoa::class, $native_coas_arr, $factory_name);
    }
}