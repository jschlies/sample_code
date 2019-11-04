<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeasesFixSecond
 */
class VacantLeasesFixSecond extends Migration
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
                    $table->foreign('lease_id')->references('id')->on('leases');
                }
            );
        }

        if (MigrationHelper::foreign_key_exists('lease_schedules', 'lease_schedules_suite_id_foreign'))
        {
            Schema::table(
                'lease_schedules',
                function (Blueprint $table)
                {
                    $table->foreign('suite_id')->references('id')->on('suites');
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
