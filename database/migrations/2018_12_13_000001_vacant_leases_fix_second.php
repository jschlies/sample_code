<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeasesFix Analytic
 */
class VacantLeasesFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::index_exists('suites', 'suites_property_id_suite_id_code_index'))
        {
            Schema::table(
                'suites',
                function (Blueprint $table)
                {
                    $table->dropUnique('suites_property_id_suite_id_code_index');
                }
            );
        }

        if ( ! MigrationHelper::index_exists('suites', 'suites_property_id_suite_id_code_original_property_code_unique'))
        {
            Schema::table(
                'suites',
                function (Blueprint $table)
                {
                    $table->unique(['property_id', 'suite_id_code', 'original_property_code']);
                }
            );
        }

        if (MigrationHelper::foreign_key_exists('lease_schedules', 'lease_schedules_lease_id_foreign'))
        {
            Schema::table(
                'lease_schedules',
                function (Blueprint $table)
                {
                    $table->dropForeign('lease_schedules_lease_id_foreign');
                }
            );
        }

        if (MigrationHelper::foreign_key_exists('lease_schedules', 'lease_schedules_suite_id_foreign'))
        {
            Schema::table(
                'lease_schedules',
                function (Blueprint $table)
                {
                    $table->dropForeign('lease_schedules_suite_id_foreign');
                }
            );
        }

        if (MigrationHelper::foreign_key_exists('suites', 'suites_property_id_suite_id_code_unique'))
        {
            Schema::table(
                'suites',
                function (Blueprint $table)
                {
                    $table->dropForeign('suites_property_id_suite_id_code_unique');
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
