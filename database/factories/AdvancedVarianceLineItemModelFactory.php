<?php

use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AdvancedVarianceLineItem::class,
    function ()
    {
        return [
            "flagged_via_policy"               => Seeder::getFakerObj()->randomElement([true, false]),
            "flagged_manually"                 => Seeder::getFakerObj()->randomElement([true, false]),
            "flagged_manually_date"            => Carbon::now()->format('F j, Y, g:i:u a'),
            "flagger_user_id"                  => null,
            "report_template_account_group_id" => null,
            "budgeted"                         => mt_rand(1, 10000000000000),
            "actual"                           => mt_rand(1, 10000000000000),
            "budgeted_actual_difference"       => mt_rand(1, 10000000000000),
            "budgeted_actual_percent"          => mt_rand(1, 100),

            "explanation"             => null,
            "resolved_date"           => null,
            "explanation_update_date" => null,
            "explainer_id"            => null,

            'monthly_actual'           => mt_rand(1, 10000000000000),
            'monthly_budgeted'         => mt_rand(1, 10000000000000),
            'monthly_variance'         => mt_rand(1, 10000000000000),
            'monthly_percent_variance' => mt_rand(1, 10000000000000),
            'ytd_budgeted'             => mt_rand(1, 10000000000000),
            'ytd_actual'               => mt_rand(1, 10000000000000),
            'ytd_variance'             => mt_rand(1, 10000000000000),
            'ytd_percent_variance'     => mt_rand(1, 10000000000000),
            'qtd_budgeted'             => mt_rand(1, 10000000000000),
            'qtd_actual'               => mt_rand(1, 10000000000000),
            'qtd_variance'             => mt_rand(1, 10000000000000),
            'line_item_coefficient'    => '1',
            'line_item_name'           => 'foo',
            'line_item_code'           => 'foo',

            'is_summary_tab_default_line_item' => Seeder::getFakerObj()->randomElement([true, false]),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AdvancedVarianceLineItem::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->filter(function ($PropertyObj) { return $PropertyObj->advancedVariances->count(); })->random())
        {
            $PropertySeederObj = new PropertySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        if (isset($seeder_provided_attributes_arr['advanced_variance_id']))
        {
            $AdvancedVarianceObj = AdvancedVariance::find($seeder_provided_attributes_arr['advanced_variance_id']);
        }
        elseif ( ! $AdvancedVarianceObj = $PropertyObj->advancedVariances->random())
        {
            $AdvancedVarianceSeederObj = new AdvancedVarianceSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $AdvancedVarianceObj       = $AdvancedVarianceSeederObj->run()->first();
        }
        if (isset($seeder_provided_attributes_arr['native_account_id']))
        {
            $NativeAccountObj = NativeAccount::find($seeder_provided_attributes_arr['native_account_id']);
        }
        elseif ( ! $NativeAccountObj = $ClientObj->nativeCoas->first()->nativeAccounts->random())
        {
            $NativeAccountSeederObj = new NativeAccountSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $NativeAccountObj       = $NativeAccountSeederObj->run()->first();
        }
        if (isset($seeder_provided_attributes_arr['advanced_variance_explanation_type_id']))
        {
            $AdvancedVarianceExplanationTypeObj = AdvancedVarianceExplanationType::find($seeder_provided_attributes_arr['advanced_variance_explanation_type_id']);
        }
        elseif ( ! $AdvancedVarianceExplanationTypeObj = $ClientObj->advancedVarianceExplanationTypes->random())
        {
            $AdvancedVarianceExplanationTypeSeederObj = new AdvancedVarianceExplanationTypeSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $AdvancedVarianceExplanationTypeObj       = $AdvancedVarianceExplanationTypeSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(AdvancedVarianceLineItem::class),
            [
                'advanced_variance_id'                  => $AdvancedVarianceObj->id,
                'native_account_id'                     => $NativeAccountObj->id,
                'advanced_variance_explanation_type_id' => $AdvancedVarianceExplanationTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);