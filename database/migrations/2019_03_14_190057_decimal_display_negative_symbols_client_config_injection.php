<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Models\Client;

class DecimalDisplayNegativeSymbolsClientConfigInjection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $Clients = DB::select(
            DB::raw(
                '
                    SELECT clients.id, clients.name, clients.config_json
                    FROM clients
                    WHERE clients.name != :DUMMY_CLIENT_NAME
                '
            ),
            [
                'DUMMY_CLIENT_NAME' => Client::DUMMY_CLIENT_NAME,
            ]
        );

        foreach ($Clients as $Client)
        {
            $config_json_arr = json_decode($Client->config_json, true);
            $config_json_arr[Client::DECIMAL_DISPLAY_FLAG] = Client::DECIMAL_DISPLAY_DEFUALT_VALUE;
            $config_json_arr[Client::NEGATIVE_VALUE_SYMBOLS_FLAG] = Client::NEGATIVE_VALUE_SYMBOLS_DEFAULT_VALUE;
            $config_json = json_encode($config_json_arr);

            DB::update(
                DB::raw(
                    "
                    UPDATE clients 
                    SET config_json = :CONFIG_JSON
                    WHERE clients.id = :CLIENT_ID
                "
                ),
                [
                    'CLIENT_ID'   => $Client->id,
                    'CONFIG_JSON' => $config_json,
                ]
            );
        }

    }

    /**
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function down()
    {
        throw new \App\Waypoint\Exceptions\GeneralException('Migration down() not supported');
    }
}
