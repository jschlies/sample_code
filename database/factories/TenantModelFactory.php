<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Tenant::class,
    function ()
    {
        return [
            'name'        => Seeder::getFakeName(),
            'description' => Seeder::getFakeDescription(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    Tenant::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($factory->getProvidedValuesArr()['tenant_industry_id']))
        {
            $TenantIndustryObj = Tenant::find($factory->getProvidedValuesArr()['tenant_industry_id']);
        }
        elseif ( ! $TenantIndustryObj = $ClientObj->tenantIndustries->random())
        {
            $TenantIndustrySeederObj = new TenantIndustrySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $TenantIndustryObj       = $TenantIndustrySeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(Tenant::class),
            [
                'client_id'          => $ClientObj->id,
                'tenant_industry_id' => $TenantIndustryObj->id,
            ]
        );
    }
);