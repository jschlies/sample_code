<?php

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AccessListProperty::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AccessListProperty::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        /**
         * nasty constraint issue
         */
        $cnt = 0;
        do
        {
            if (isset($seeder_provided_attributes_arr['property_id']))
            {
                $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
            }
            elseif ( ! $PropertyObj = $ClientObj->properties->random())
            {
                $PropertySeederObj = new PropertySeeder(['client_id' => $seeder_provided_attributes_arr['client_id']], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $PropertyObj       = $PropertySeederObj->run()->first();
            }

            if (isset($seeder_provided_attributes_arr['access_list_id']))
            {
                $AccessListObj = AccessList::find($seeder_provided_attributes_arr['access_list_id']);
            }
            elseif ( ! $AccessListObj = $ClientObj->accessLists->random())
            {
                $AccessListSeederObj = new AccessListSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
                /** @var AccessList $AccessListObj */
                $AccessListObj = $AccessListSeederObj->run()->first();
            }

            if (++$cnt > 10)
            {
                throw new GeneralException('Infinite loop at ' . __FILE__ . ':' . __LINE__ . PHP_EOL);
            }
        } while (AccessListProperty::where('property_id', $PropertyObj->id)->where('access_list_id', $AccessListObj->id)->get()->count());

        return array_merge(
            $factory->raw(AccessListProperty::class),
            [
                'property_id'    => $PropertyObj->id,
                'access_list_id' => $AccessListObj->id,
            ],
            $seeder_provided_attributes_arr
        );

    }
);