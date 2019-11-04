<?php

use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    CalculatedFieldEquation::class,
    function ()
    {
        return [
            'name'        => Seeder::getFakeName(),
            'description' => Seeder::getFakeDescription(),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    CalculatedFieldEquation::class,
    Seeder::PHPUNIT_FACTORY_NAME,

    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['report_template_mapping_id']))
        {
            /** @var ReportTemplateMapping $ReportTemplateMappingObj */
            $ReportTemplateMappingObj = ReportTemplateMapping::find($seeder_provided_attributes_arr['report_template_mapping_id']);
        }
        elseif ( ! $ReportTemplateMappingObj =
            $ClientObj
                ->defaultAdvancedVarianceReportTemplate
                ->reportTemplateAccountGroups
                ->filter(
                    function ($ReportTemplateAccountGroupObj)
                    {
                        return $ReportTemplateAccountGroupObj->reportTemplateMappings->count();
                    }
                )
                ->random()->reportTemplateMappings->random()
        )
        {
            $ReportTemplateMappingSeederObj = new ReportTemplateMappingSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ReportTemplateMappingObj       = $ReportTemplateMappingSeederObj->run()->first();
        }
        if (isset($seeder_provided_attributes_arr['calculated_field_id']))
        {
            $CalculatedFieldObj = CalculatedField::find($seeder_provided_attributes_arr['calculated_field_id']);
        }
        else
        {
            $CalculatedFieldSeederObj = new CalculatedFieldSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $CalculatedFieldObj       = $CalculatedFieldSeederObj->run()->first();
        }
        $equation_string = Seeder::getFakerObj()->randomElement(
            [
                '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + 1000 * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . '] + ' . mt_rand(),
                '1 + 2*3',
            ]
        );
        return array_merge(
            $factory->raw(CalculatedFieldEquation::class),
            [
                'calculated_field_id' => $CalculatedFieldObj->id,
                'equation_string'     => $equation_string,
            ],
            $seeder_provided_attributes_arr
        );
    }
);