<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Suite;
use App\Waypoint\Models\SuiteLease;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    SuiteLease::class,
    function ()
    {
        return [
            'description' => Seeder::getFakeName(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    SuiteLease::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {

        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        else
        {
            $PropertyObj = $ClientObj->properties->random();
        }
        if (isset($seeder_provided_attributes_arr['suite_id']))
        {
            $SuiteObj = Suite::find($seeder_provided_attributes_arr['suite_id']);
        }
        else
        {
            $SuiteObj = $PropertyObj->suites->random();
        }
        if (isset($seeder_provided_attributes_arr['lease_id']))
        {
            $LeaseObj = Lease::find($seeder_provided_attributes_arr['lease_id']);
        }
        else
        {
            $LeaseSeederObj = new LeaseSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $LeaseObj       = $LeaseSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(SuiteLease::class),
            [
                'client_id' => $ClientObj->id,
                'suite_id'  => $SuiteObj->id,
                'lease_id'  => $LeaseObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);