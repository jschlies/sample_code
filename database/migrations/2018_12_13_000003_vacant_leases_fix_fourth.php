<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeasesFix Analytic
 */
class VacantLeasesFixFourth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::index_exists('suites', 'suites_property_id_suite_id_code_unique'))
        {
            Schema::table(
                'suites',
                function (Blueprint $table)
                {
                    $table->dropIndex('suites_property_id_suite_id_code_unique');
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
        throw new GeneralException('Migration down() not supported');
    }
}
