<?php

use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AccessListUser::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AccessListUser::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        do
        {
            if (isset($seeder_provided_attributes_arr['user_id']))
            {
                $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
            }
            else
            {
                /** @var AccessList $AccessListObj */
                $UserObj = $ClientObj->users->filter(function (User $UserObj) { return ! $UserObj->isAdmin(); })->random();
            }

            if (isset($seeder_provided_attributes_arr['access_list_id']))
            {
                $AccessListObj = AccessList::find($seeder_provided_attributes_arr['access_list_id']);
            }
            /**
             * this should be an empty accessList
             */
            elseif ( ! $AccessListObj = $ClientObj->accessLists->filter(
                function (AccessList $AccessListObj)
                {
                    return $AccessListObj->accessListUsers->count() == 0;
                }
            )->first()
            )
            {
                $AccessListSeederObj = new AccessListSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
                /** @var AccessList $AccessListObj */
                $AccessListObj = $AccessListSeederObj->run()->first();
            }
        } while (AccessListUser::where('access_list_id', $AccessListObj->id)->where('user_id', $UserObj->id)->get()->count());

        return array_merge(
            $factory->raw(AccessListUser::class),
            [
                'client_id'      => $ClientObj->id,
                'user_id'        => $UserObj->id,
                'access_list_id' => $AccessListObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);