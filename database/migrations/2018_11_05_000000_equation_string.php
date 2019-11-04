<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class EquationString extends Migration
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
                $table->string('equation_string_parsed')->after('equation_string')->default('unknown');
            }
        );

        DB::UPDATE(
            DB::raw(
                '
                    UPDATE calculated_field_equations
                        set equation_string_parsed = equation_string
                '
            )
        );
        $CalculatedFieldEquationsObjArr = DB::select(
            DB::raw(
                '
                    SELECT * FROM calculated_field_equations
                '
            )
        );
        foreach ($CalculatedFieldEquationsObjArr as $CalculatedFieldEquationsObj)
        {
            $CalculatedFieldEquationsObj->equation_string = preg_replace("/(NA\_\d*)/", '[$1]', $CalculatedFieldEquationsObj->equation_string);

            $CalculatedFieldEquationsObj->equation_string = preg_replace("/(RTAG\_\d*)/", '[$1]', $CalculatedFieldEquationsObj->equation_string);

            DB::update(
                DB::raw(
                    '
                    UPDATE calculated_field_equations
                        SET equation_string = :EQUATION_STRING
                        WHERE id = :ID
                '
                ),
                [
                    "EQUATION_STRING" => $CalculatedFieldEquationsObj->equation_string,
                    "ID"              => $CalculatedFieldEquationsObj->id,
                ]
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
