<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    PropertyNativeCoa::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    PropertyNativeCoa::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder(['client_id' => $ClientObj->id,]);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        if (isset($seeder_provided_attributes_arr['native_coa_id']))
        {
            $NativeCoaObj = NativeCoa::find($seeder_provided_attributes_arr['native_coa_id']);
        }
        else
        {
            $NativeCoaSeederObj = new NativeCoaSeeder(['client_id' => $ClientObj->id,]);
            $NativeCoaObj       = $NativeCoaSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(PropertyNativeCoa::class),
            [
                'client_id'     => $ClientObj->id,
                'property_id'   => $PropertyObj->id,
                'native_coa_id' => $NativeCoaObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);