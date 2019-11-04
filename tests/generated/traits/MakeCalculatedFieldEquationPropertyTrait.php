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
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCalculatedFieldEquationPropertyTrait
{
    /**
     * Create fake instance of CalculatedFieldEquationProperty and save it in database
     *
     * @param array $calculated_field_equation_properties_arr
     * @return CalculatedFieldEquationProperty
     */
    public function makeCalculatedFieldEquationProperty($calculated_field_equation_properties_arr = [])
    {
        $theme = $this->fakeCalculatedFieldEquationPropertyData($calculated_field_equation_properties_arr);
        return $this->CalculatedFieldEquationPropertyRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CalculatedFieldEquationProperty
     *
     * @param array $calculated_field_equation_properties_arr
     * @return CalculatedFieldEquationProperty
     */
    public function fakeCalculatedFieldEquationProperty($calculated_field_equation_properties_arr = [])
    {
        return new CalculatedFieldEquationProperty($this->fakeCalculatedFieldEquationPropertyData($calculated_field_equation_properties_arr));
    }

    /**
     * Get fake data of CalculatedFieldEquationProperty
     *
     * @param array $calculated_field_equation_properties_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCalculatedFieldEquationPropertyData($calculated_field_equation_properties_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($calculated_field_equation_properties_arr);
        return $factory->raw(CalculatedFieldEquationProperty::class, $calculated_field_equation_properties_arr, $factory_name);
    }
}