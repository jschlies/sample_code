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
use App\Waypoint\Models\NativeAccount;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNativeAccountTrait
{
    /**
     * Create fake instance of NativeAccount and save it in database
     *
     * @param array $native_accounts_arr
     * @return NativeAccount
     */
    public function makeNativeAccount($native_accounts_arr = [])
    {
        $theme = $this->fakeNativeAccountData($native_accounts_arr);
        return $this->NativeAccountRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NativeAccount
     *
     * @param array $native_accounts_arr
     * @return NativeAccount
     */
    public function fakeNativeAccount($native_accounts_arr = [])
    {
        return new NativeAccount($this->fakeNativeAccountData($native_accounts_arr));
    }

    /**
     * Get fake data of NativeAccount
     *
     * @param array $native_accounts_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNativeAccountData($native_accounts_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($native_accounts_arr);
        return $factory->raw(NativeAccount::class, $native_accounts_arr, $factory_name);
    }
}