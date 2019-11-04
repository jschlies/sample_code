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
use App\Waypoint\Models\CalculatedFieldEquation;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCalculatedFieldEquationTrait
{
    /**
     * Create fake instance of CalculatedFieldEquation and save it in database
     *
     * @param array $calculated_field_equations_arr
     * @return CalculatedFieldEquation
     */
    public function makeCalculatedFieldEquation($calculated_field_equations_arr = [])
    {
        $theme = $this->fakeCalculatedFieldEquationData($calculated_field_equations_arr);
        return $this->CalculatedFieldEquationRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CalculatedFieldEquation
     *
     * @param array $calculated_field_equations_arr
     * @return CalculatedFieldEquation
     */
    public function fakeCalculatedFieldEquation($calculated_field_equations_arr = [])
    {
        return new CalculatedFieldEquation($this->fakeCalculatedFieldEquationData($calculated_field_equations_arr));
    }

    /**
     * Get fake data of CalculatedFieldEquation
     *
     * @param array $calculated_field_equations_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCalculatedFieldEquationData($calculated_field_equations_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($calculated_field_equations_arr);
        return $factory->raw(CalculatedFieldEquation::class, $calculated_field_equations_arr, $factory_name);
    }
}