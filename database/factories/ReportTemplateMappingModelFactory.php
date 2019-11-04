<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    ReportTemplateMapping::class,
    function ()
    {
        return [
            'is_summary_tab_default_line_item' => Seeder::getFakerObj()->randomElement([true, false]),
        ];
    }
);

$factory->defineAs(
    ReportTemplateMapping::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        /** @var NativeAccount $NativeAccountObj */
        if (isset($seeder_provided_attributes_arr['native_account_id']))
        {
            $NativeAccountObj = NativeAccount::find($seeder_provided_attributes_arr['native_account_id']);
        }
        else
        {
            $NativeAccountSeederObj = new NativeAccountSeeder(
                [
                    'client_id'     => $ClientObj->id,
                    'native_coa_id' => $ClientObj->nativeCoas->first()->id,
                ],
                1,
                Seeder::PHPUNIT_FACTORY_NAME
            );
            $NativeAccountObj       = $NativeAccountSeederObj->run()->first();
        }
        if (isset($seeder_provided_attributes_arr['report_template_account_group_id']))
        {
            $ReportTemplateAccountGroupObj = ReportTemplateAccountGroup::find($seeder_provided_attributes_arr['report_template_account_group_id']);
        }
        else
        {
            $ReportTemplateAccountGroupObj = $ClientObj->defaultAdvancedVarianceReportTemplate->reportTemplateAccountGroups->random();
        }
        return array_merge(
            $factory->raw(ReportTemplateMapping::class),
            [
                'client_id'                        => $ClientObj->id,
                'native_account_id'                => $NativeAccountObj->id,
                'report_template_account_group_id' => $ReportTemplateAccountGroupObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);