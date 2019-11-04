<?php

use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldVariable;
use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CalculatedFieldVariable::class,
    function ()
    {
        return [
            'name'        => Seeder::getFakeName(),
            'description' => Seeder::getFakeDescription(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CalculatedFieldVariable::class,
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
        return array_merge(
            $factory->raw(CalculatedFieldVariable::class),
            [
                'calculated_field_equation_id' => $CalculatedFieldEquationObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);