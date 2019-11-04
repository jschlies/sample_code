<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;

class PreCalcCleanup3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $key_arr   = [];
        $key_arr[] = 'RELATEDUSERTYPES_PROPERTY_[0-9]';
        $key_arr[] = 'REPORT_TEMPLATE_FULL_ARR_CLIENT[0-9]';
        $key_arr[] = 'ADVANCEDVARIANCESUMMARIES_PROPERTY__';
        $key_arr[] = 'REPORT_TEMPLATE_FULL_ARR_REPORT_TEMPLATE_';
        $key_arr[] = 'ADVANCEDVARIANCESUMMARIES_PROPERTY_';
        $key_arr[] = 'INDEXFORPROPERTYGROUP_[0-9]';
        $key_arr[] = 'INDEXFORPROPERTYGROUP_METADATA_[0-9]';
        $key_arr[] = 'STANDARDATTRIBUTESOFPROPERTIES$';

        $where_clause = 'AND ( 
                                pre_calc_name REGEXP \'' . implode('\' 
                                or pre_calc_name REGEXP \'', $key_arr) . '\')';
        $sql          = '
                                DELETE FROM pre_calc_status 
                                                                        where
                                          is_soiled 
                                           ' . $where_clause . '
                            ';
        DB::delete(
            DB::raw($sql)
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
