<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Models\TenantTenantAttribute;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    TenantTenantAttribute::class,
    function ()
    {
        return [
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    TenantTenantAttribute::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        do
        {
            if (isset($factory->getProvidedValuesArr()['tenant_id']))
            {
                $TenantObj = Tenant::find($factory->getProvidedValuesArr()['tenant_id']);
            }
            elseif ( ! $TenantObj = $ClientObj->tenants->random())
            {
                $TenantSeederObj = new TenantSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $TenantObj       = $TenantSeederObj->run()->first();
            }
            if (isset($factory->getProvidedValuesArr()['tenant_attribute_id']))
            {
                $TenantAttributeObj = TenantAttribute::find($factory->getProvidedValuesArr()['tenant_attribute_id']);
            }
            elseif ( ! $TenantAttributeObj = $ClientObj->tenantAttributes->random())
            {
                $TenantAttributeSeederObj = new TenantAttributeSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $TenantAttributeObj       = $TenantAttributeSeederObj->run()->first();
            }
        } while (TenantTenantAttribute::where('tenant_id', $TenantObj->id)
                                      ->where('tenant_attribute_id', $TenantAttributeObj->id)
                                      ->get()->count()
        );
        return array_merge(
            $factory->raw(TenantTenantAttribute::class),
            [
                'tenant_id'           => $TenantObj->id,
                'tenant_attribute_id' => $TenantAttributeObj->id,
            ]
        );
    }
);