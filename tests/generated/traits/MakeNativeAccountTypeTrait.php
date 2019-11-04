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
use App\Waypoint\Models\NativeAccountType;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNativeAccountTypeTrait
{
    /**
     * Create fake instance of NativeAccountType and save it in database
     *
     * @param array $native_account_types_arr
     * @return NativeAccountType
     */
    public function makeNativeAccountType($native_account_types_arr = [])
    {
        $theme = $this->fakeNativeAccountTypeData($native_account_types_arr);
        return $this->NativeAccountTypeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NativeAccountType
     *
     * @param array $native_account_types_arr
     * @return NativeAccountType
     */
    public function fakeNativeAccountType($native_account_types_arr = [])
    {
        return new NativeAccountType($this->fakeNativeAccountTypeData($native_account_types_arr));
    }

    /**
     * Get fake data of NativeAccountType
     *
     * @param array $native_account_types_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNativeAccountTypeData($native_account_types_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($native_account_types_arr);
        return $factory->raw(NativeAccountType::class, $native_account_types_arr, $factory_name);
    }
}