<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;

class FinallyForPropertyGroupsTwo extends Migration
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
                    UPDATE clients
                        SET clients.pre_calc_json = :EMPTY_JSON;
                '
            ),
            [
                'EMPTY_JSON' => json_encode(new stdClass()),
            ]
        );
        DB::update(
            DB::raw(
                '
                    UPDATE properties
                        SET properties.pre_calc_json = :EMPTY_JSON;
                '
            ),
            [
                'EMPTY_JSON' => json_encode(new stdClass()),
            ]
        );
        DB::update(
            DB::raw(
                '
                    UPDATE users
                        SET users.pre_calc_json = :EMPTY_JSON;
                '
            ),
            [
                'EMPTY_JSON' => json_encode(new stdClass()),
            ]
        );
        DB::update(
            DB::raw(
                '
                    UPDATE property_groups
                        SET property_groups.pre_calc_json = :EMPTY_JSON;
                '
            ),
            [
                'EMPTY_JSON' => json_encode(new stdClass()),
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        throw new GeneralException('we do not support migration reversal');
    }
}
