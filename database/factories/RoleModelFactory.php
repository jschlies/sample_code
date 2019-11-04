<?php

use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Role::class,
    function ()
    {
        return [
            'name'         => Seeder::getFakeName(),
            'display_name' => Seeder::getFakerObj()->words(4, true) . mt_rand(1000, 9999999),
            'description'  => Seeder::getFakeDescription(),
        ];
    }
);

$factory->defineAs(
    Role::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(Role::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);