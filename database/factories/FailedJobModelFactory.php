<?php

use App\Waypoint\Models\FailedJob;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    FailedJob::class,
    function ()
    {
        return [
            'connection' => Seeder::getFakerObj()->words(4, true),
            'queue'      => Seeder::getFakerObj()->words(4, true),
            'payload'    => Seeder::getFakerObj()->words(4, true),
            'failed_at'  => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
        ];
    }
);

$factory->defineAs(
    FailedJob::class, Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(FailedJob::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);