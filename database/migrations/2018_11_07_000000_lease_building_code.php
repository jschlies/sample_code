<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class LeaseBuildingCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'suites',
            function (Blueprint $table)
            {
                $table->string('original_property_code')->nullable()->after('square_footage');

                $table->dropUnique('suites_property_id_suite_id_code_unique');
            }
        );
        Schema::table(
            'suites',
            function (Blueprint $table)
            {
                $table->unique(['property_id', 'suite_id_code', 'original_property_code']);
                //$table->unique(['property_id', 'suite_id_number', 'original_property_code']);
                //$table->unique(['property_id', 'name', 'original_property_code']);
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
