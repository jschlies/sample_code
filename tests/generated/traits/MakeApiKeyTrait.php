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
use App\Waypoint\Models\ApiKey;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeApiKeyTrait
{
    /**
     * Create fake instance of ApiKey and save it in database
     *
     * @param array $api_keys_arr
     * @return ApiKey
     */
    public function makeApiKey($api_keys_arr = [])
    {
        $theme = $this->fakeApiKeyData($api_keys_arr);
        return $this->ApiKeyRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ApiKey
     *
     * @param array $api_keys_arr
     * @return ApiKey
     */
    public function fakeApiKey($api_keys_arr = [])
    {
        return new ApiKey($this->fakeApiKeyData($api_keys_arr));
    }

    /**
     * Get fake data of ApiKey
     *
     * @param array $api_keys_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeApiKeyData($api_keys_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($api_keys_arr);
        return $factory->raw(ApiKey::class, $api_keys_arr, $factory_name);
    }
}