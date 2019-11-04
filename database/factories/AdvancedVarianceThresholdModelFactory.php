<?php

use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AdvancedVarianceThreshold::class,
    function ()
    {
        $native_account_overage_threshold_amount  = mt_rand(100, 1000);
        $native_account_overage_threshold_percent = mt_rand(1, 10);

        $report_template_account_group_overage_threshold_amount  = mt_rand(100, 1000);
        $report_template_account_group_overage_threshold_percent = mt_rand(1, 10);

        $calculated_field_overage_threshold_amount  = mt_rand(100, 1000);
        $calculated_field_overage_threshold_percent = mt_rand(1, 10);
        return [
            "native_account_overage_threshold_amount"           => $native_account_overage_threshold_amount,
            "native_account_overage_threshold_amount_too_good"  => $native_account_overage_threshold_amount * .75,
            "native_account_overage_threshold_percent"          => $native_account_overage_threshold_percent,
            "native_account_overage_threshold_percent_too_good" => $native_account_overage_threshold_percent * .75,
            "native_account_overage_threshold_operator"         => 'and',

            "report_template_account_group_overage_threshold_amount"           => $report_template_account_group_overage_threshold_amount,
            "report_template_account_group_overage_threshold_amount_too_good"  => $report_template_account_group_overage_threshold_amount * .75,
            "report_template_account_group_overage_threshold_percent"          => $report_template_account_group_overage_threshold_percent,
            "report_template_account_group_overage_threshold_percent_too_good" => $report_template_account_group_overage_threshold_percent * .75,
            "report_template_account_group_overage_threshold_operator"         => 'and',

            "calculated_field_overage_threshold_amount"           => $calculated_field_overage_threshold_amount,
            "calculated_field_overage_threshold_amount_too_good"  => $calculated_field_overage_threshold_amount * .75,
            "calculated_field_overage_threshold_percent"          => $calculated_field_overage_threshold_percent,
            "calculated_field_overage_threshold_percent_too_good" => $calculated_field_overage_threshold_percent * .75,
            "calculated_field_overage_threshold_operator"         => 'and',
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AdvancedVarianceThreshold::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(AdvancedVarianceThreshold::class),
            [

                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);