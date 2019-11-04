<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    ReportTemplate::class,
    function ()
    {
        return [
            'report_template_name'                        => Seeder::getFakeName(),
            'report_template_description'                 => Seeder::getFakeDescription(),
            'is_boma_report_template'                     => false,
            'is_default_analytics_report_template'        => false,
            'is_default_advance_variance_report_template' => false,
            'externally_synced'                           => true,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    ReportTemplate::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(ReportTemplate::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);