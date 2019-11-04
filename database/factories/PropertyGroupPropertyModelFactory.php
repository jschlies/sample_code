<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    PropertyGroupProperty::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    PropertyGroupProperty::class,
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
            $PropertyObj = $ClientObj->properties->random();
        }
        if (isset($seeder_provided_attributes_arr['property_group_id']))
        {
            $PropertyGroupObj = PropertyGroup::find($seeder_provided_attributes_arr['property_group_id']);
        }
        else
        {
            $PropertyGroupSeederObj = new PropertyGroupSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyGroupObj       = $PropertyGroupSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(PropertyGroupProperty::class),
            [
                'property_id'       => $PropertyObj->id,
                'property_group_id' => $PropertyGroupObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);