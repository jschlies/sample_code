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
use App\Waypoint\Models\Property;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePropertyTrait
{
    /**
     * Create fake instance of Property and save it in database
     *
     * @param array $properties_arr
     * @return Property
     */
    public function makeProperty($properties_arr = [])
    {
        $theme = $this->fakePropertyData($properties_arr);
        return $this->PropertyRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Property
     *
     * @param array $properties_arr
     * @return Property
     */
    public function fakeProperty($properties_arr = [])
    {
        return new Property($this->fakePropertyData($properties_arr));
    }

    /**
     * Get fake data of Property
     *
     * @param array $properties_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePropertyData($properties_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($properties_arr);
        return $factory->raw(Property::class, $properties_arr, $factory_name);
    }
}