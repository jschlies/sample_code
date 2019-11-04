<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;

class PreCalcCleanup5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $key_arr   = [];
        $key_arr[] = 'INDEXFORPROPERTYGROUP_[0-9]';
        $key_arr[] = 'REPORT_TEMPLATE_FULL_ARR_CLIENT_[0-9]';
        $key_arr[] = 'RELATED_USERS_USER_[0-9]';
        $key_arr[] = 'ASSETTYPESOFPROPERTIES_USER_[0-9]';
        $key_arr[] = 'STANDARDATTRIBUTESOFPROPERTIES_USER_[0-9]';
        $key_arr[] = 'CUSTOMATTRIBUTESOFPROPERTIES_USER_[0-9]';
        $key_arr[] = 'USER_DETAIL_USER_[0-9]';

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
