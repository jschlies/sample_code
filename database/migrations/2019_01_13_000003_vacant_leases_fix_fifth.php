<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeasesFix Analytic
 */
class VacantLeasesFixFifth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update(
            DB::raw(
                '
                    UPDATE lease_schedules
                        set lease_schedules.lease_id = null
                            where lease_schedules.lease_id not in (SELECT id FROM leases);
                '
            )
        );
        DB::update(
            DB::raw(
                '
                    UPDATE lease_schedules
                        set lease_schedules.suite_id = null
                            where lease_schedules.suite_id not in (SELECT id FROM suites);
                '
            )
        );
        if ( ! MigrationHelper::foreign_key_exists('lease_schedules', 'lease_schedules_lease_id_foreign'))
        {
            Schema::table(
                'lease_schedules',
                function (Blueprint $table)
                {
                    $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
                }
            );
        }
        if ( ! MigrationHelper::foreign_key_exists('lease_schedules', 'lease_schedules_suite_id_foreign'))
        {
            Schema::table(
                'lease_schedules',
                function (Blueprint $table)
                {
                    $table->foreign('suite_id')->references('id')->on('suites')->onDelete('cascade');
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
