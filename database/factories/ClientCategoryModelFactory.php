<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use \App\Waypoint\Models\ClientCategory;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    ClientCategory::class,
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
    ClientCategory::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(ClientCategory::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);