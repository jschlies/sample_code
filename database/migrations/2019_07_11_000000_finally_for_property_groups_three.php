<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FinallyForPropertyGroupsThree extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'clients',
            function (Blueprint $table)
            {
                $table->mediumText('pre_calc_json')->change();
            }
        );
        Schema::table(
            'users',
            function (Blueprint $table)
            {
                $table->mediumText('pre_calc_json')->change();
            }
        );
        Schema::table(
            'properties',
            function (Blueprint $table)
            {
                $table->mediumText('pre_calc_json')->change();
            }
        );
        Schema::table(
            'property_groups',
            function (Blueprint $table)
            {
                $table->mediumText('pre_calc_json')->change();
            }
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
