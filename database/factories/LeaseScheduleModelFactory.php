<?php

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseSchedule;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    LeaseSchedule::class,
    function (array $seeder_provided_attributes_arr)
    {
        return [
            'rent_roll_id' => mt_rand(1000000, 100000000),

            'suite_id_code' => Seeder::getRandomString(),
            'lease_id_code' => Seeder::getRandomString(),
            'lease_name'    => Seeder::getRandomString(),

            'property_name'          => Seeder::getFakerObj()->name . ' ' . Seeder::getFakerObj()->address,
            'property_code'          => Seeder::getFakerObj()->name . ' ' . Seeder::getRandomString(),
            'as_of_date'             => Seeder::getFakerObj()->dateTimeBetween($startDate = '-1 months', $endDate = '+1 months')->format('Y-m-d H:i:s'),
            'original_property_code' => Seeder::getFakerObj()->name . ' ' . Seeder::getRandomString(),
            'rent_unit_id'           => mt_rand(1000, 1000000),

            'lease_type'            => Seeder::getRandomString(),
            'square_footage'        => mt_rand(1000000, 100000000),
            'lease_start_date'      => Seeder::getFakerObj()->dateTimeBetween($startDate = '-60 months', $endDate = '-30 months')->format('Y-m-d H:i:s'),
            'lease_expiration_date' => Seeder::getFakerObj()->dateTimeBetween($startDate = '+30 months', $endDate = '+60 months')->format('Y-m-d H:i:s'),
            'lease_term'            => mt_rand(12, 360),
            'tenancy_year'          => mt_rand(1900, 2100),
            'monthly_rent'          => mt_rand(1000, 1000000),
            'monthly_rent_area'     => mt_rand(1000000, 100000000),
            'annual_rent'           => mt_rand(1000, 1000000),
            'annual_rent_area'      => mt_rand(1000000, 100000000),
            'annual_rec_area'       => mt_rand(1000, 1000000),
            'annual_misc_area'      => mt_rand(1000000, 100000000),
            'security_deposit'      => mt_rand(1000000, 100000000),
            'letter_cr_amt'         => Seeder::getFakeName(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    LeaseSchedule::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function () use ($factory)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj = TestCase::getUnitTestClient())
        {
            throw new GeneralException('Cannot find SEED client');
        }
        if (isset($factory->getProvidedValuesArr()['lease_id']))
        {
            $LeaseObj = Lease::find($factory->getProvidedValuesArr()['lease_id']);
        }
        else
        {
            $LeaseSeederObj = new LeaseSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $LeaseObj       = $LeaseSeederObj->run()->first();
        }
        if (isset($factory->getProvidedValuesArr()['property_id']))
        {
            $PropertyObj = Property::find($factory->getProvidedValuesArr()['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(LeaseSchedule::class),
            [
                'lease_id'      => $LeaseObj->id,
                'property_id'   => $PropertyObj->id,
                'property_code' => $PropertyObj->property_code,
            ]
        );
    }
);