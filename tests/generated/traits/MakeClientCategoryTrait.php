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
use App\Waypoint\Models\ClientCategory;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeClientCategoryTrait
{
    /**
     * Create fake instance of ClientCategory and save it in database
     *
     * @param array $client_categories_arr
     * @return ClientCategory
     */
    public function makeClientCategory($client_categories_arr = [])
    {
        $theme = $this->fakeClientCategoryData($client_categories_arr);
        return $this->ClientCategoryRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ClientCategory
     *
     * @param array $client_categories_arr
     * @return ClientCategory
     */
    public function fakeClientCategory($client_categories_arr = [])
    {
        return new ClientCategory($this->fakeClientCategoryData($client_categories_arr));
    }

    /**
     * Get fake data of ClientCategory
     *
     * @param array $client_categories_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeClientCategoryData($client_categories_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($client_categories_arr);
        return $factory->raw(ClientCategory::class, $client_categories_arr, $factory_name);
    }
}