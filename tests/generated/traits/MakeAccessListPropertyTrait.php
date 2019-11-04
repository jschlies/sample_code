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
use App\Waypoint\Models\AccessListProperty;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAccessListPropertyTrait
{
    /**
     * Create fake instance of AccessListProperty and save it in database
     *
     * @param array $access_list_properties_arr
     * @return AccessListProperty
     */
    public function makeAccessListProperty($access_list_properties_arr = [])
    {
        $theme = $this->fakeAccessListPropertyData($access_list_properties_arr);
        return $this->AccessListPropertyRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AccessListProperty
     *
     * @param array $access_list_properties_arr
     * @return AccessListProperty
     */
    public function fakeAccessListProperty($access_list_properties_arr = [])
    {
        return new AccessListProperty($this->fakeAccessListPropertyData($access_list_properties_arr));
    }

    /**
     * Get fake data of AccessListProperty
     *
     * @param array $access_list_properties_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAccessListPropertyData($access_list_properties_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($access_list_properties_arr);
        return $factory->raw(AccessListProperty::class, $access_list_properties_arr, $factory_name);
    }
}