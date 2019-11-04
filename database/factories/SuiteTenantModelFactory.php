<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Suite;
use App\Waypoint\Models\SuiteTenant;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    SuiteTenant::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    SuiteTenant::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($factory->getProvidedValuesArr()['property_id']))
        {
            $PropertyObj = Property::find($factory->getProvidedValuesArr()['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->filter(
            function (Property $PropertyObj)
            {
                return $PropertyObj->suites->count();
            })->random()
        )
        {

            $PropertySeederObj = new PropertySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        if (isset($factory->getProvidedValuesArr()['suite_id']))
        {
            $SuiteObj = Suite::find($factory->getProvidedValuesArr()['suite_id']);
        }
        /** randomly grab a property with suites, the randomly grab a syuite
         *  from that property
         */
        elseif ( ! $SuiteObj = $PropertyObj->suites->random())
        {
            $SuiteSeederObj = new SuiteSeeder(['property_id' => $PropertyObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $SuiteObj       = $SuiteSeederObj->run()->first();
        }

        do
        {
            if (isset($factory->getProvidedValuesArr()['tenant_id']))
            {
                $TenantObj = Tenant::find($factory->getProvidedValuesArr()['tenant_id']);
            }
            elseif ( ! $TenantObj = $ClientObj->tenants->random())
            {
                $TenantSeederObj = new TenantSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $TenantObj       = $TenantSeederObj->run()->first();
            }

        } while (SuiteTenant::where('suite_id', $SuiteObj->id)->where('tenant_id', $TenantObj->id)->get()->count());

        return array_merge(
            $factory->raw(SuiteTenant::class),
            [
                'suite_id'  => $SuiteObj->id,
                'tenant_id' => $TenantObj->id,
            ]
        );
    }
);