<?php

use App\Waypoint\Models\AssetType;
use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AssetType::class,
    function ()
    {
        return [
            'asset_type_name'        => Seeder::getFakerObj()->words(4, true),
            'asset_type_description' => Seeder::getFakerObj()->words(5, true),
            'display_name'           => Seeder::getFakerObj()->words(4, true),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AssetType::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(AssetType::class),
            [

                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);