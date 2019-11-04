<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    ReportTemplateAccountGroup::class,
    function ()
    {
        return [
            'parent_report_template_account_group_id' => null,
            'is_category'                             => false,
            'is_major_category'                       => false,
            'is_waypoint_specific'                    => false,
            'report_template_account_group_code'      => waypoint_generate_uuid(),
            'report_template_account_group_name'      => waypoint_generate_uuid(),
            'display_name'                            => waypoint_generate_uuid(),
            'usage_type'                              => waypoint_generate_uuid(),
            'sorting'                                 => waypoint_generate_uuid(),
            'version_num'                             => waypoint_generate_uuid(),
            'deprecated_waypoint_code'                => waypoint_generate_uuid(),
            'boma_account_header_1_code_old'          => waypoint_generate_uuid(),
            'boma_account_header_1_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_2_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_2_code_old'          => waypoint_generate_uuid(),
            'boma_account_header_3_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_3_code_old'          => waypoint_generate_uuid(),
            'boma_account_header_4_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_4_code_old'          => waypoint_generate_uuid(),
            'boma_account_header_5_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_5_code_old'          => waypoint_generate_uuid(),
            'boma_account_header_6_name_old'          => waypoint_generate_uuid(),
            'boma_account_header_6_code_old'          => waypoint_generate_uuid(),
            'is_summary_tab_default_line_item'        => Seeder::getFakerObj()->randomElement([true, false]),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    ReportTemplateAccountGroup::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['native_account_type_id']))
        {
            $NativeAccountTypeObj = NativeAccountType::find($seeder_provided_attributes_arr['native_account_type_id']);
        }
        else
        {
            $NativeAccountTypeObj = $ClientObj->nativeAccountTypes->random();
        }
        if (isset($seeder_provided_attributes_arr['report_template_id']))
        {
            $ReportTemplateObj = ReportTemplate::find($seeder_provided_attributes_arr['report_template_id']);
        }
        else
        {
            $ReportTemplateObj = $ClientObj->defaultAdvancedVarianceReportTemplate;
        }
        return array_merge(
            $factory->raw(ReportTemplateAccountGroup::class),
            [
                'client_id'              => $ClientObj->id,
                'report_template_id'     => $ReportTemplateObj->id,
                'native_account_type_id' => $NativeAccountTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);