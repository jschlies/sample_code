<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    EcmProject::class,
    function ()
    {
        return [
            'name'                            => Seeder::getFakeName(),
            'description'                     => Seeder::getFakeDescription(),
            'project_category'                => Seeder::getFakerObj()->randomElement(EcmProject::$project_category_arr),
            'project_status'                  => Seeder::getFakerObj()->randomElement(EcmProject::$project_status_arr),
            'costs'                           => mt_rand(0, 10000000000000),
            'estimated_incentive'             => mt_rand(0, 10000000000000),
            'estimated_annual_savings'        => mt_rand(0, 10000000000000),
            'estimated_annual_energy_savings' => mt_rand(0, 10000000000000),
            'energy_units'                    => Seeder::getFakerObj()->randomElement(EcmProject::$energy_units_arr),
            'project_summary'                 => Seeder::getFakerObj()->words(1000, true),
            'estimated_start_date'            => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'estimated_completion_date'       => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    EcmProject::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(EcmProject::class),
            [
                'property_id' => $PropertyObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);