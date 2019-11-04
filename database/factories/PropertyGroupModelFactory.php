<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    PropertyGroup::class,
    function ()
    {
        return [
            'name'          => Seeder::getFakeName(),
            'description'   => Seeder::getFakeDescription(),
        ];
    }
);

$factory->defineAs(
    PropertyGroup::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        /** @var User $UserObj */
        if (isset($seeder_provided_attributes_arr['user_id']))
        {
            $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
        }
        else
        {
            $UserObj = $ClientObj->users->filter(function (User $UserObj) { return ! $UserObj->isAdmin(); })->random();
        }
        return array_merge(
            $factory->raw(PropertyGroup::class),
            [
                'client_id' => $ClientObj->id,
                'user_id'   => $UserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);