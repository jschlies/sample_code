<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use \App\Waypoint\Models\ApiKey;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
/** @noinspection PhpUnusedParameterInspection */
$factory->define(
    ApiKey::class,
    function ()
    {
        return [
            'key'           => substr(sha1(time() . mt_rand()), 0, 40),
            'level'         => 1,
            'ignore_limits' => true,

        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    ApiKey::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['user_id']))
        {
            $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
        }
        elseif ( ! $UserObj = $ClientObj->users->filter(function ($UserObj) { return ! $UserObj->api_key; })->first())
        {
            /** @var UserSeeder $UserSeederObj */
            $UserSeederObj = new UserSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $UserObj       = $UserSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(ApiKey::class),
            [
                'user_id' => $UserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);