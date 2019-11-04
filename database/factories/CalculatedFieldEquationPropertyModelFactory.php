<?php

use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CalculatedFieldEquationProperty::class,
    function ()
    {
        return [
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CalculatedFieldEquationProperty::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['calculated_field_equation_id']))
        {
            $CalculatedFieldEquationObj = CalculatedFieldEquation::find($seeder_provided_attributes_arr['calculated_field_equation_id']);
        }
        else
        {
            $CalculatedFieldEquationSeederObj = new CalculatedFieldEquationSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $CalculatedFieldEquationObj       = $CalculatedFieldEquationSeederObj->run()->first();
        }
        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        else
        {
            $PropertyObj = $ClientObj->properties->random();
        }
        return array_merge(
            $factory->raw(CalculatedFieldEquationProperty::class),
            [
                'calculated_field_equation_id' => $CalculatedFieldEquationObj->id,
                'property_id'                  => $PropertyObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);