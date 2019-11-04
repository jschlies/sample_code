<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class VacantLeases
 */
class VacantLeases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
        ///**
        // * in preperation of the constraint that follows, let's make sure that $table->unique(['property_id', 'suite_id_code']);
        // * will work by consolidating suites with same property_id and suite_id_code
        // */
        //$TotalDuplicateSuiteObjArr = DB::select(
        //    DB::raw(
        //        '
        //            SELECT clients.name, suites.property_id, suites.suite_id_code, count(*) as count
        //                FROM suites
        //                JOIN properties ON properties.id = suites.property_id
        //                JOIN clients ON properties.client_id = clients.id
        //
        //                GROUP BY suites.property_id, suites.suite_id_code
        //                HAVING count > 1
        //        '
        //    )
        //);
        //foreach ($TotalDuplicateSuiteObjArr as $TotalDuplicateSuiteObj)
        //{
        //    $SuiteObjArr    =
        //        DB::select(
        //            DB::raw(
        //                '
        //                    SELECT *
        //                        FROM suites
        //                        WHERE
        //                              suites.property_id = :PROPERTY_ID AND
        //                              suites.suite_id_code = :SUITE_ID_CODE
        //                '
        //            ),
        //            [
        //                'PROPERTY_ID'   => $TotalDuplicateSuiteObj->property_id,
        //                'SUITE_ID_CODE' => $TotalDuplicateSuiteObj->suite_id_code,
        //            ]
        //        );
        //    $SurvivingSuiteObj = null;
        //    foreach ($SuiteObjArr as $SuiteObj)
        //    {
        //        if ( ! $SurvivingSuiteObj)
        //        {
        //            $SurvivingSuiteObj = $SuiteObj;
        //            continue;
        //        }
        //        DB::update(
        //            DB::raw(
        //                '
        //                    UPDATE lease_schedules
        //                        SET suite_id = :NEW_SUITE_ID
        //                        WHERE
        //                              suite_id = :OLD_SUITE_ID
        //                '
        //            ),
        //            [
        //                'NEW_SUITE_ID' => $SurvivingSuiteObj->id,
        //                'OLD_SUITE_ID' => $SuiteObj->id,
        //            ]
        //        );
        //
        //        try
        //        {
        //            DB::update(
        //                DB::raw(
        //                    '
        //                    UPDATE suite_leases
        //                        SET suite_id = :NEW_SUITE_ID
        //                        WHERE
        //                              suite_id = :OLD_SUITE_ID
        //                '
        //                ),
        //                [
        //                    'NEW_SUITE_ID' => $SurvivingSuiteObj->id,
        //                    'OLD_SUITE_ID' => $SuiteObj->id,
        //                ]
        //            );
        //        }
        //        catch (Exception $e)
        //        {
        //            /**
        //             * ah ,the connection between suite and lease already exists soooooo...
        //             */
        //            if (
        //                ! strpos($e->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') ||
        //                ! strpos($e->getMessage(), 'suite_leases_suite_id_lease_id_unique')
        //            )
        //            {
        //                throw $e;
        //            }
        //        }
        //        DB::delete(
        //            DB::raw(
        //                '
        //                    DELETE FROM suites
        //                        WHERE
        //                              id = :OLD_SUITE_ID
        //                '
        //            ),
        //            [
        //                'OLD_SUITE_ID' => $SuiteObj->id,
        //            ]
        //        );
        //
        //    }
        //
        //}
        //if ( ! MigrationHelper::index_exists('suites', 'suites_property_id_suite_id_code_index'))
        //{
        //    Schema::table(
        //        'suites',
        //        function (Blueprint $table)
        //        {
        //            $table->unique(['property_id', 'suite_id_code']);
        //        }
        //    );
        //}
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
