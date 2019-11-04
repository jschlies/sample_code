<?php

use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AdvancedVarianceExplanationType::class,
    function ()
    {
        return [
            "name"        => Seeder::getFakerObj()->words(6, true),
            "description" => Seeder::getFakerObj()->words(8, true),
            "color"       => Seeder::getFakerObj()->hexColor,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AdvancedVarianceExplanationType::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(AdvancedVarianceExplanationType::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);