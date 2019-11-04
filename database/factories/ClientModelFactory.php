<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Client::class,
    function ()
    {
        return [
            'name'                                         => Seeder::getFakeClientName(),
            'client_id_old'                                => 2,
            'display_name'                                 => Seeder::getFakerObj()->words(4, true),
            'description'                                  => Seeder::getFakeDescription(),
            'client_code'                                  => Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890-'),
            'active_status'                                => Seeder::getFakerObj()->randomElement([Client::ACTIVE, Client::INACTIVE]),
            'active_status_date'                           => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')
                                                                    ->format('Y-m-d H:i:s'),
            'property_group_calc_last_requested'           => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '-20 months')
                                                                    ->format('Y-m-d H:i:s'),
            'property_group_calc_status'                   => Client::PROPERTY_GROUP_CALC_STATUS_IDLE,
            'property_group_force_recalc'                  => false,
            'property_group_force_first_time_calc'         => false,
            'property_group_force_calc_property_group_ids' => null,
            "dormant_user_ttl"                             => rand(10000, 1000000),

            'config_json' => '{"FEATURE_OPPORTUNITIES":false,"NOTIFICATIONS":false}',
            'style_json'  => json_encode(new ArrayObject()),
            'image_json'  => json_encode(new ArrayObject()),
        ];
    }
);

$factory->defineAs(
    Client::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        $client_name = isset($seeder_provided_attributes_arr['name']) ? $seeder_provided_attributes_arr['name'] : Seeder::getFakeClientName();
        /**
         * nasty constraint issue
         */
        while (Client::where('name', $client_name)
                     ->get()->count()
        )
        {
            $client_name = isset($seeder_provided_attributes_arr['name']) ? $seeder_provided_attributes_arr['name'] : Seeder::getFakeClientName();
        }

        return array_merge(
            $factory->raw(Client::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);