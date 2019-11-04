<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class SupportForecastInVarianceReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'advanced_variance_line_items',
            function (Blueprint $table)
            {
                /**
                 * See HER-3127
                 */
                $table->double('forecast_budgeted', 16, 2)->nullable()->default(null)->after('qtr_ytd_month_3_percent_variance');
                $table->double('forecast_actual', 16, 2)->nullable()->default(null)->after('forecast_budgeted');
                $table->double('forecast_variance', 16, 2)->nullable()->default(null)->after('forecast_actual');
                $table->double('forecast_percent_variance', 16, 7)->nullable()->default(null)->after('forecast_variance');

                $table->double('total_forecast_budgeted', 16, 2)->nullable()->default(null)->after('total_qtr_ytd_month_3_percent_variance');
                $table->double('total_forecast_actual', 16, 2)->nullable()->default(null)->after('total_forecast_budgeted');
                $table->double('total_forecast_variance', 16, 2)->nullable()->default(null)->after('total_forecast_actual');
                $table->double('total_forecast_percent_variance', 16, 7)->nullable()->default(null)->after('total_forecast_variance');

                $table->double('qtr_forecast_month_1_budgeted', 16, 2)->nullable()->default(null)->after('total_forecast_percent_variance');
                $table->double('qtr_forecast_month_1_actual', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_1_budgeted');
                $table->double('qtr_forecast_month_1_variance', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_1_actual');
                $table->double('qtr_forecast_month_1_percent_variance', 16, 7)->nullable()->default(null)->after('qtr_forecast_month_1_variance');
                $table->double('qtr_forecast_month_2_budgeted', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_1_percent_variance');
                $table->double('qtr_forecast_month_2_actual', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_2_budgeted');
                $table->double('qtr_forecast_month_2_variance', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_2_actual');
                $table->double('qtr_forecast_month_2_percent_variance', 16, 7)->nullable()->default(null)->after('qtr_forecast_month_2_variance');
                $table->double('qtr_forecast_month_3_budgeted', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_2_percent_variance');
                $table->double('qtr_forecast_month_3_actual', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_3_budgeted');
                $table->double('qtr_forecast_month_3_variance', 16, 2)->nullable()->default(null)->after('qtr_forecast_month_3_actual');
                $table->double('qtr_forecast_month_3_percent_variance', 16, 7)->nullable()->default(null)->after('qtr_forecast_month_3_variance');

                $table->double('total_qtr_forecast_month_1_budgeted', 16, 2)->nullable()->default(null)->after('total_qtr_ytd_month_3_percent_variance');
                $table->double('total_qtr_forecast_month_1_actual', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_1_budgeted');
                $table->double('total_qtr_forecast_month_1_variance', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_1_actual');
                $table->double('total_qtr_forecast_month_1_percent_variance', 16, 7)->nullable()->default(null)->after('total_qtr_forecast_month_1_variance');
                $table->double('total_qtr_forecast_month_2_budgeted', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_1_percent_variance');
                $table->double('total_qtr_forecast_month_2_actual', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_2_budgeted');
                $table->double('total_qtr_forecast_month_2_variance', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_2_actual');
                $table->double('total_qtr_forecast_month_2_percent_variance', 16, 7)->nullable()->default(null)->after('total_qtr_forecast_month_2_variance');
                $table->double('total_qtr_forecast_month_3_budgeted', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_2_percent_variance');
                $table->double('total_qtr_forecast_month_3_actual', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_3_budgeted');
                $table->double('total_qtr_forecast_month_3_variance', 16, 2)->nullable()->default(null)->after('total_qtr_forecast_month_3_actual');
                $table->double('total_qtr_forecast_month_3_percent_variance', 16, 7)->nullable()->default(null)->after('total_qtr_forecast_month_3_variance');
            }
        );
        Schema::table(
            'advanced_variances',
            function (Blueprint $table)
            {
                $table->string('trigger_mode')->default('trigger_mode_ytd')->after('period_type');
            }
        );

        DB::update(
            DB::raw(
                "
                    UPDATE advanced_variances SET trigger_mode = 'trigger_mode_ytd'
                "
            )
        );

        $client_results = DB::select(
            DB::raw(
                "
                    SELECT clients.id, clients.config_json FROM clients 
                        JOIN properties on clients.id = properties.client_id
                        JOIN advanced_variances on properties.id = advanced_variances.property_id
                        WHERE clients.id > 1
                        GROUP BY clients.id
                "
            )
        );

        foreach ($client_results as $client_result)
        {
            $config_json = json_decode($client_result->config_json);

            $config_json->ADVANCED_VARIANCE_TRIGGER = 'trigger_mode_ytd';

            $config_string = json_encode(stdToArray($config_json));
            DB::update(
                DB::raw(
                    "UPDATE clients 
                            SET config_json = :CONFIG_JSON
                            WHERE id = :ID
                        "
                ),
                [
                    'CONFIG_JSON' => $config_string,
                    'ID'          => $client_result->id,
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
