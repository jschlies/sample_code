<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    TenantIndustry::class,
    function ()
    {
        return [
            'name'                     => Seeder::getFakeName(),
            'description'              => Seeder::getFakeDescription(),
            'tenant_industry_category' => Seeder::getFakerObj()->randomElement(TenantIndustry::$tenant_industry_category_value_arr),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    TenantIndustry::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(TenantIndustry::class),
            [
                'client_id' => $ClientObj->id,
            ]
        );
    }
);