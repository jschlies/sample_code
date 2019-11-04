<?php

use App\Waypoint\Models\RoleDetail;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    RoleDetail::class,
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
    RoleDetail::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(RoleDetail::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);