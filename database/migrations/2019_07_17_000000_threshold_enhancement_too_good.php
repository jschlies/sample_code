<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ThresholdEnhancementTooGood extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('advanced_variance_thresholds', 'native_account_overage_threshold_amount_too_good'))
        {
            Schema::table(
                'advanced_variance_thresholds',
                function (Blueprint $table)
                {
                    $table->float(
                        'native_account_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('native_account_overage_threshold_amount');

                    $table->float(
                        'native_account_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('native_account_overage_threshold_percent');

                    $table->float(
                        'report_template_account_group_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('report_template_account_group_overage_threshold_amount');

                    $table->float(
                        'report_template_account_group_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('report_template_account_group_overage_threshold_percent');

                    $table->float(
                        'calculated_field_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('calculated_field_overage_threshold_amount');

                    $table->float(
                        'calculated_field_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('calculated_field_overage_threshold_percent');
                }
            );
            Schema::table(
                'advanced_variance_line_items',
                function (Blueprint $table)
                {
                    $table->float(
                        'native_account_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('native_account_overage_threshold_amount');

                    $table->float(
                        'native_account_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('native_account_overage_threshold_percent');

                    $table->float(
                        'report_template_account_group_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('report_template_account_group_overage_threshold_amount');

                    $table->float(
                        'report_template_account_group_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('report_template_account_group_overage_threshold_percent');

                    $table->float(
                        'calculated_field_overage_threshold_amount_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('calculated_field_overage_threshold_amount');

                    $table->float(
                        'calculated_field_overage_threshold_percent_too_good', 16, 2)
                          ->nullable()
                          ->default(null)
                          ->after('calculated_field_overage_threshold_percent');
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('we do not support migration reversal');
    }
}
