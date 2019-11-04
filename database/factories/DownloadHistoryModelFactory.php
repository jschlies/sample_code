<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\DownloadHistory;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    DownloadHistory::class,
    function ()
    {
        return [
            'original_file_name' => Seeder::getFakerObj()->word,
            'download_time'      => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'download_md5'       => Seeder::getFakerObj()->md5,
            'download_type'      => Seeder::getFakerObj()->randomElement(DownloadHistory::$download_type_values),
            'data'               => Seeder::getFakerObj()->paragraph(2),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    DownloadHistory::class,
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
            $factory->raw(DownloadHistory::class),
            [
                'user_id' => $UserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);