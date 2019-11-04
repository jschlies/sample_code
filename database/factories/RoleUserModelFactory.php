<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\RoleUser;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    RoleUser::class,
    function ()
    {
        return [];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    RoleUser::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        $RoleSeederObj = new RoleSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
        if (isset($seeder_provided_attributes_arr['user_id']))
        {
            $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
        }
        elseif ( ! $UserObj = $ClientObj->users->filter(function (User $UserObj) { return ! $UserObj->isAdmin(); })->random())
        {
            $UserSeederObj = new UserSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $UserObj       = $UserSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(RoleUser::class),
            [
                "role_id" => $RoleSeederObj->run()->first()->id,
                "user_id" => $UserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);