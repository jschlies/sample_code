<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeasesFix Analytic
 */
class DefaultLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'advanced_variance_line_items',
            function (Blueprint $table)
            {
                $table->boolean('is_summary_tab_default_line_item')->default(false)->after('is_summary');
            }
        );
        Schema::table(
            'calculated_fields',
            function (Blueprint $table)
            {
                $table->boolean('is_summary_tab_default_line_item')->default(false)->after('is_summary');
            }
        );
        Schema::table(
            'report_template_account_groups',
            function (Blueprint $table)
            {
                $table->boolean('is_summary_tab_default_line_item')->default(false)->after('is_summary');
            }
        );
        Schema::table(
            'report_template_mappings',
            function (Blueprint $table)
            {
                $table->boolean('is_summary_tab_default_line_item')->default(false)->after('is_summary');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('Migration down() not supported');
    }
}
