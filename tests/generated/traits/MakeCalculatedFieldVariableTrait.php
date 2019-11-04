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
use App\Waypoint\Models\CalculatedFieldVariable;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCalculatedFieldVariableTrait
{
    /**
     * Create fake instance of CalculatedFieldVariable and save it in database
     *
     * @param array $calculated_field_variables_arr
     * @return CalculatedFieldVariable
     */
    public function makeCalculatedFieldVariable($calculated_field_variables_arr = [])
    {
        $theme = $this->fakeCalculatedFieldVariableData($calculated_field_variables_arr);
        return $this->CalculatedFieldVariableRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CalculatedFieldVariable
     *
     * @param array $calculated_field_variables_arr
     * @return CalculatedFieldVariable
     */
    public function fakeCalculatedFieldVariable($calculated_field_variables_arr = [])
    {
        return new CalculatedFieldVariable($this->fakeCalculatedFieldVariableData($calculated_field_variables_arr));
    }

    /**
     * Get fake data of CalculatedFieldVariable
     *
     * @param array $calculated_field_variables_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCalculatedFieldVariableData($calculated_field_variables_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($calculated_field_variables_arr);
        return $factory->raw(CalculatedFieldVariable::class, $calculated_field_variables_arr, $factory_name);
    }
}