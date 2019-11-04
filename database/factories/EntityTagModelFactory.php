<?php

use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    EntityTag::class,
    function ()
    {
        return [
            'entity_model' => Seeder::getFakerObj()->randomElement(EntityTag::$favorite_values),
            'name'         => Favorite::class,
            'description'  => Seeder::getFakeDescription(),
        ];
    }
);

$factory->defineAs(
    EntityTag::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(EntityTag::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);