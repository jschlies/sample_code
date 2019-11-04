<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Lease::class,
    function ()
    {
        $LeaseStartDateObj = Seeder::getFakerObj()->dateTimeBetween($startDate = '-120 months', $endDate = '+6 months');
        do
        {
            /** @var DateTime */
            $LeaseExpirationDateObj = Seeder::getFakerObj()->dateTimeBetween($startDate = '-6 months', $endDate = '+120 months');
        } while ($LeaseStartDateObj->diff($LeaseExpirationDateObj)->days < 0);

        return [
            'lease_id_code'         => Seeder::getRandomString(),
            'lease_name'            => Seeder::getFakeCompanyName(),
            'lease_type'            => Seeder::getFakeName(),
            'lease_start_date'      => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '-5 months')->format('Y-m-d H:i:s'),
            'lease_expiration_date' => Seeder::getFakerObj()->dateTimeBetween($startDate = '+5 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'lease_term'            => mt_rand(12, 360),
            'tenancy_year'          => mt_rand(1900, 2100),
            'monthly_rent'          => mt_rand(1000, 100000),
            'annual_rent'           => mt_rand(1000, 100000),
            'security_deposit'      => mt_rand(1000000, 100000000),
            'letter_cr_amt'         => mt_rand(1000000, 100000000),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    Lease::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function ($seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($factory->getProvidedValuesArr()['property_id']))
        {
            $PropertyObj = Property::find($factory->getProvidedValuesArr()['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        if (isset($factory->getProvidedValuesArr()['suite_id']))
        {
            $SuiteObj = Property::find($factory->getProvidedValuesArr()['suite_id']);
        }
        elseif ( ! $SuiteObj = $ClientObj->properties->random())
        {
            $SuiteSeederObj = new SuiteSeeder(['property_id' => $PropertyObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $SuiteObj       = $SuiteSeederObj->run()->first();
        }

        return array_merge(
            $factory->raw(Lease::class),
            [
                'property_id' => $PropertyObj->id,
                'suite_id'    => $SuiteObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);