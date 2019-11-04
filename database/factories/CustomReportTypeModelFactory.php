<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CustomReportType::class,
    function ()
    {
        $name = Seeder::getFakeName();

        return [
            'name'         => $name,
            'display_name' => $name . ' -- display version',
            'period_type'  => array_random(CustomReportType::$period_types),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CustomReportType::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(CustomReportType::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);



