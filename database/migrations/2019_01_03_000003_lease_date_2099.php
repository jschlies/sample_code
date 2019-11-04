<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class VacantLeasesFix Analytic
 */
class LeaseDate2099 extends Migration
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
                    UPDATE leases
                        set lease_start_date = null
                            where lease_start_date = 0;
                '
            )
        );
        DB::update(
            DB::raw(
                '
                    UPDATE leases
                        set lease_expiration_date = null
                            where lease_expiration_date = 0;
                '
            )
        );
        DB::update(
            DB::raw(
                '
                    UPDATE lease_schedules
                        set lease_start_date = null
                            where lease_start_date = 0;
                '
            )
        );
        DB::update(
            DB::raw(
                '
                    UPDATE lease_schedules
                        set lease_expiration_date = null
                            where lease_expiration_date = 0;
                '
            )
        );
        DB::update(
            DB::raw(
                '
                    UPDATE lease_schedules
                        set as_of_date = null
                            where as_of_date = 0;
                '
            )
        );
        DB::statement("ALTER TABLE `leases`
            CHANGE COLUMN `lease_start_date` `lease_start_date` DATETIME ,
            CHANGE COLUMN `lease_expiration_date` `lease_expiration_date` DATETIME NULL ;");

        DB::statement("ALTER TABLE `lease_schedules`
            CHANGE COLUMN `as_of_date` `as_of_date` DATETIME NULL,
            CHANGE COLUMN `lease_start_date` `lease_start_date` DATETIME NULL,
            CHANGE COLUMN `lease_expiration_date` `lease_expiration_date` DATETIME NULL ;");
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
