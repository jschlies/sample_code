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
use App\Waypoint\Models\PropertyGroup;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePropertyGroupTrait
{
    /**
     * Create fake instance of PropertyGroup and save it in database
     *
     * @param array $property_groups_arr
     * @return PropertyGroup
     */
    public function makePropertyGroup($property_groups_arr = [])
    {
        $theme = $this->fakePropertyGroupData($property_groups_arr);
        return $this->PropertyGroupRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of PropertyGroup
     *
     * @param array $property_groups_arr
     * @return PropertyGroup
     */
    public function fakePropertyGroup($property_groups_arr = [])
    {
        return new PropertyGroup($this->fakePropertyGroupData($property_groups_arr));
    }

    /**
     * Get fake data of PropertyGroup
     *
     * @param array $property_groups_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePropertyGroupData($property_groups_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($property_groups_arr);
        return $factory->raw(PropertyGroup::class, $property_groups_arr, $factory_name);
    }
}