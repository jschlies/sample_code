<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class EquationStringSizes
 */
class EquationStringSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'calculated_field_equations',
            function (Blueprint $table)
            {
                $table->string('equation_string', 255)->change();
                $table->string('equation_string_parsed', 1023)->change();
                $table->string('display_equation_string', 1023)->change();
                $table->string('name', 255)->change();
                $table->string('description', 255)->change();
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
