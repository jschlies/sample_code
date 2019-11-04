<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EquationStringSizes Analytic
 */
class AddAnalyticsEnabled extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'report_templates',
            function (Blueprint $table)
            {
                $table->boolean('is_data_calcs_enabled')->default(true)->after('is_default_advance_variance_report_template');
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
