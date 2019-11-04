<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NativeAccountType::class,
    function ()
    {
        Seeder::getFakerObj()->addProvider(new Faker\Provider\en_US\Company(Seeder::getFakerObj()));
        return [
            'native_account_type_name'        => Seeder::getFakeName(),
            'native_account_type_description' => Seeder::getFakeDescription(),
            'display_name'                    => Seeder::getFakeName(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    NativeAccountType::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(NativeAccountType::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);