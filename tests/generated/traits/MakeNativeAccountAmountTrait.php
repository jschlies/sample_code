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
use App\Waypoint\Models\NativeAccountAmount;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNativeAccountAmountTrait
{
    /**
     * Create fake instance of NativeAccountAmount and save it in database
     *
     * @param array $native_account_amounts_arr
     * @return NativeAccountAmount
     */
    public function makeNativeAccountAmount($native_account_amounts_arr = [])
    {
        $theme = $this->fakeNativeAccountAmountData($native_account_amounts_arr);
        return $this->NativeAccountAmountRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NativeAccountAmount
     *
     * @param array $native_account_amounts_arr
     * @return NativeAccountAmount
     */
    public function fakeNativeAccountAmount($native_account_amounts_arr = [])
    {
        return new NativeAccountAmount($this->fakeNativeAccountAmountData($native_account_amounts_arr));
    }

    /**
     * Get fake data of NativeAccountAmount
     *
     * @param array $native_account_amounts_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNativeAccountAmountData($native_account_amounts_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($native_account_amounts_arr);
        return $factory->raw(NativeAccountAmount::class, $native_account_amounts_arr, $factory_name);
    }
}