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
use App\Waypoint\Models\CalculatedField;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCalculatedFieldTrait
{
    /**
     * Create fake instance of CalculatedField and save it in database
     *
     * @param array $calculated_fields_arr
     * @return CalculatedField
     */
    public function makeCalculatedField($calculated_fields_arr = [])
    {
        $theme = $this->fakeCalculatedFieldData($calculated_fields_arr);
        return $this->CalculatedFieldRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CalculatedField
     *
     * @param array $calculated_fields_arr
     * @return CalculatedField
     */
    public function fakeCalculatedField($calculated_fields_arr = [])
    {
        return new CalculatedField($this->fakeCalculatedFieldData($calculated_fields_arr));
    }

    /**
     * Get fake data of CalculatedField
     *
     * @param array $calculated_fields_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCalculatedFieldData($calculated_fields_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($calculated_fields_arr);
        return $factory->raw(CalculatedField::class, $calculated_fields_arr, $factory_name);
    }
}