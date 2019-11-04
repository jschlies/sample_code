<?php

use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CalculatedField::class,
    function ()
    {
        return [
            'name'                             => Seeder::getFakeName(),
            'description'                      => Seeder::getFakeDescription(),
            'is_summary_tab_default_line_item' => Seeder::getFakerObj()->randomElement([true, false]),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CalculatedField::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['report_template_id']))
        {
            /** @var ReportTemplate $ReportTemplateObj */
            $ReportTemplateObj = ReportTemplate::find($seeder_provided_attributes_arr['report_template_id']);
        }
        elseif ( ! $ReportTemplateObj = $ClientObj->defaultAdvancedVarianceReportTemplate)
        {
            $ReportTemplateSeederObj = new ReportTemplateSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ReportTemplateObj       = $ReportTemplateSeederObj->run()->first();
        }

        return array_merge(
            $factory->raw(CalculatedField::class),
            [
                'report_template_id' => $ReportTemplateObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);