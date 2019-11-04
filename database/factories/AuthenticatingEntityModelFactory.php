<?php

use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AuthenticatingEntity::class,
    function ()
    {
        return [
            'name'                => Seeder::getFakeName(),
            'description'         => Seeder::getFakeDescription(),
            'identity_connection' => Seeder::getFakeName() . 'identity_connection',
            'email_regex'         => '/' . Seeder::getFakeName() . '/',
            'is_default'          => false,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AuthenticatingEntity::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        return array_merge(
            $factory->raw(AuthenticatingEntity::class),
            []
        );
    }
);