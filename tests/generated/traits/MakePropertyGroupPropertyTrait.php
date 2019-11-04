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
use App\Waypoint\Models\PropertyGroupProperty;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePropertyGroupPropertyTrait
{
    /**
     * Create fake instance of PropertyGroupProperty and save it in database
     *
     * @param array $property_group_properties_arr
     * @return PropertyGroupProperty
     */
    public function makePropertyGroupProperty($property_group_properties_arr = [])
    {
        $theme = $this->fakePropertyGroupPropertyData($property_group_properties_arr);
        return $this->PropertyGroupPropertyRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of PropertyGroupProperty
     *
     * @param array $property_group_properties_arr
     * @return PropertyGroupProperty
     */
    public function fakePropertyGroupProperty($property_group_properties_arr = [])
    {
        return new PropertyGroupProperty($this->fakePropertyGroupPropertyData($property_group_properties_arr));
    }

    /**
     * Get fake data of PropertyGroupProperty
     *
     * @param array $property_group_properties_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePropertyGroupPropertyData($property_group_properties_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($property_group_properties_arr);
        return $factory->raw(PropertyGroupProperty::class, $property_group_properties_arr, $factory_name);
    }
}