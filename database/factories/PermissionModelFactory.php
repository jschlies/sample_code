<?php

use App\Waypoint\Models\Permission;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Permission::class,
    function ()
    {
        return [
            'name'         => Seeder::getFakeName(),
            'display_name' => Seeder::getFakerObj()->words(4, true),
            'description'  => Seeder::getFakeDescription(),
        ];
    }
);

$factory->defineAs(
    Permission::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(Permission::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);