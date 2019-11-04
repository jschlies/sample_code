<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CustomReport::class,
    function ()
    {
        return [
            'period'       => array_random(CustomReport::MONTHS),
            'year'         => Seeder::getFakerObj()->year,
            'download_url' => 'http://hermes.com',
            'file_type'    => array_random(CustomReport::FILE_TYPES),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CustomReport::class,
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

        if (isset($seeder_provided_attributes_arr['custom_report_type_id']))
        {
            $CustomReportTypeObj = CustomReportType::find($seeder_provided_attributes_arr['custom_report_type_id']);
        }
        elseif ( ! $CustomReportTypeObj = $ClientObj->customReportTypes->first())
        {
            $CustomReportTypeSeeder = new CustomReportTypeSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $CustomReportTypeObj    = $CustomReportTypeSeeder->run()->first();
        }
        return array_merge(
            $factory->raw(CustomReport::class),
            [
                'property_id'           => $PropertyObj->id,
                'custom_report_type_id' => $CustomReportTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);