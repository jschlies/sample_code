<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Suite;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Suite::class,
    function ()
    {
        return [
            'name'            => Seeder::getFakeName(),
            'suite_id_code'   => Seeder::getRandomString(),
            'suite_id_number' => Seeder::getFakeName(),
            'description'     => Seeder::getFakeDescription(),
            'square_footage'  => mt_rand(10000, 1000000),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    Suite::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        else
        {
            $PropertyObj = $ClientObj->properties->random();
        }
        return array_merge(
            $factory->raw(Suite::class),
            [
                'client_id'   => $ClientObj->id,
                'property_id' => $PropertyObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);