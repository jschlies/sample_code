<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    TenantAttribute::class,
    function ()
    {
        return [
            'name'                      => Seeder::getFakerObj()->colorName . ' ' . rand(),
            'description'               => Seeder::getFakeDescription(),
            'tenant_attribute_category' => Seeder::getFakeDescription(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    TenantAttribute::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(TenantAttribute::class),
            [
                'client_id' => $ClientObj->id,
            ]
        );
    }
);