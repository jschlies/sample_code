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
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNativeAccountTypeTrailerTrait
{
    /**
     * Create fake instance of NativeAccountTypeTrailer and save it in database
     *
     * @param array $native_account_type_trailers_arr
     * @return NativeAccountTypeTrailer
     */
    public function makeNativeAccountTypeTrailer($native_account_type_trailers_arr = [])
    {
        $theme = $this->fakeNativeAccountTypeTrailerData($native_account_type_trailers_arr);
        return $this->NativeAccountTypeTrailerRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NativeAccountTypeTrailer
     *
     * @param array $native_account_type_trailers_arr
     * @return NativeAccountTypeTrailer
     */
    public function fakeNativeAccountTypeTrailer($native_account_type_trailers_arr = [])
    {
        return new NativeAccountTypeTrailer($this->fakeNativeAccountTypeTrailerData($native_account_type_trailers_arr));
    }

    /**
     * Get fake data of NativeAccountTypeTrailer
     *
     * @param array $native_account_type_trailers_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNativeAccountTypeTrailerData($native_account_type_trailers_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($native_account_type_trailers_arr);
        return $factory->raw(NativeAccountTypeTrailer::class, $native_account_type_trailers_arr, $factory_name);
    }
}