<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NotificationLog;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NotificationLog::class,
    function ()
    {
        return [
            'notification_time' => '2018-10-05 17:42:55',
            'notification_uuid' => waypoint_generate_uuid(),
            'data_json'         => json_encode(['a' => 123]),
            'user_json'         => json_encode(['a' => 123]),
            'channel'           => Seeder::getFakerObj()->word,
            'queue'             => Seeder::getFakerObj()->word,
            'response'          => json_encode(['a' => 123]),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    NotificationLog::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['user_id']))
        {
            $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
        }
        else
        {
            $UserObj = $ClientObj->users->random();
        }
        return array_merge(
            $factory->raw(NotificationLog::class),
            [
                'user_id' => $UserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);