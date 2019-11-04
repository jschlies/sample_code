<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PreCalcStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'pre_calc_status',
            function (Blueprint $table)
            {
                $table->increments('id');
                $table->integer('client_id')->unsigned()->nullable()->index();
                $table->integer('property_id')->unsigned()->nullable()->index();
                $table->integer('property_group_id')->unsigned()->nullable()->index();
                $table->integer('user_id')->unsigned()->nullable()->index();
                $table->string('pre_calc_name');
                $table->string('s3_location');
                $table->boolean('is_soiled')->default(false);
                $table->timestamp('soiled_at');

                $table->timestamps();

                $table->unique(['client_id', 'pre_calc_name']);
                $table->unique(['property_id', 'pre_calc_name']);
                $table->unique(['property_group_id', 'pre_calc_name']);
                $table->unique(['user_id', 'pre_calc_name']);
            }
        );
        Schema::table(
            'clients',
            function (Blueprint $table)
            {
                /**
                 * deadcode
                 */
                $table->dropColumn('pre_calc_json');
            }
        );
        Schema::table(
            'properties',
            function (Blueprint $table)
            {
                /**
                 * deadcode
                 */
                $table->dropColumn('pre_calc_json');
            }
        );
        Schema::table(
            'property_groups',
            function (Blueprint $table)
            {
                /**
                 * deadcode
                 */
                $table->dropColumn('pre_calc_json');
            }
        );
        Schema::table(
            'users',
            function (Blueprint $table)
            {
                /**
                 * deadcode
                 */
                $table->dropColumn('pre_calc_json');
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
        throw new GeneralException('we do not support migration reversal');
    }
}
