<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ConsolidateMigrations
 */
class ThresholdConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $ClientObjArr = DB::select(
            DB::raw(
                '
                    SELECT * FROM clients
                        WHERE id > 1
                '
            ),
            [
                'NAME' => 'HCP',
            ]
        );
        foreach ($ClientObjArr as $ClientObj)
        {
            $ThresholdObjArr = DB::select(
                DB::raw(
                    '
                    SELECT * FROM advanced_variance_thresholds
                        WHERE 
                              client_id = :CLIENT_ID AND
                              property_id IS NULL AND 
                              native_account_id IS NULL AND 
                              native_account_type_id IS NULL AND
                              report_template_account_group_id IS NULL and 
                              calculated_field_id IS null
                '
                ),
                [
                    'CLIENT_ID' => $ClientObj->id,
                ]
            );
            $client_conf_obj = json_decode($ClientObj->config_json);
            if (count($ThresholdObjArr) == 0)
            {
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_AMOUNT   = 1000;
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_PERCENT  = 10;
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_OPERATOR = 'and';
            }
            else
            {
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_AMOUNT   = $ThresholdObjArr[0]->calculated_field_overage_threshold_amount;
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_PERCENT  = $ThresholdObjArr[0]->calculated_field_overage_threshold_percent;
                $client_conf_obj->ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_OPERATOR = $ThresholdObjArr[0]->calculated_field_overage_threshold_operator;
            }

            DB::update(
                DB::raw(
                    '
                    UPDATE clients SET config_json  = :CONFIG_JSON
                        WHERE id = :CLIENT_ID
                '
                ),
                [
                    'CONFIG_JSON' => json_encode($client_conf_obj),
                    'CLIENT_ID'   => $ClientObj->id,

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
