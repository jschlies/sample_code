<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseTenant;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    LeaseTenant::class,
    function ()
    {
        return [
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    LeaseTenant::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        /**
         * nasty constraint issue
         */
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

            if (isset($factory->getProvidedValuesArr()['lease_id']))
            {
                $LeaseObj = Lease::find($factory->getProvidedValuesArr()['lease_id']);
            }
            else
            {
                $LeaseSeederObj = new LeaseSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $LeaseObj       = $LeaseSeederObj->run()->first();
            }
        } while (LeaseTenant::where(
            [
                ['lease_id', $LeaseObj->id],
                ['tenant_id', $TenantObj->id],
            ]
        )->get()->first());

        return array_merge(
            $factory->raw(LeaseTenant::class),
            [
                'lease_id'  => $LeaseObj->id,
                'tenant_id' => $TenantObj->id,
            ]
        );
    }
);