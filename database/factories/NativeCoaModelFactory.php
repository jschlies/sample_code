<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NativeCoa::class,
    function ()
    {
        return [

            'name' => Seeder::getFakerObj()->words(3, true),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    NativeCoa::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(NativeCoa::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);