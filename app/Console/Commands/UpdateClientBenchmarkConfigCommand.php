<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

/**
 * Class UpdateClientBenchmarkConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class UpdateClientBenchmarkConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:update_client_benchmark_config {--client_id= : Client ID }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update client benchmark config';

    /**
     * Execute the console command.
     * @todo push this logic into a repository
     * @todo See HER-424
     */
    public function handle()
    {
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }

        if ( ! config('waypoint.waypoint_ledger_mysql_connection_available', true))
        {
            return 0;
        }
        if (config('waypoint.continuous_integration_environment', false))
        {
            $this->alert("Skipping UpdateClientBenchmarkConfigCommand. ENV var CI =  " . config('waypoint.continuous_integration_environment', false));

            return true;
        }

        DB::beginTransaction();

        try
        {

            $ClientObjArr = [];

            if ($client_id)
            {
                if ( ! $ClientObj = $this->ClientRepositoryObj->findWithoutFail($client_id))
                {
                    throw new ModelNotFoundException('No such client');
                }

                $ClientObjArr[] = $ClientObj;
            }
            else
            {
                $ClientObjArr = $this->ClientRepositoryObj->all();
            }

            /** @var Client $ClientObj */
            foreach ($ClientObjArr as $ClientObj)
            {
                if ($ClientObj->name == Client::DUMMY_CLIENT_NAME || strpos($ClientObj->name, 'SEEDED') !== false)
                {
                    continue;
                }
                if ( ! $ClientObj->client_id_old)
                {
                    continue;
                }

                $this->alert("UpdateClientBenchmarkConfigCommand processing " . $ClientObj->name);

                $client_config_array                                    = $ClientObj->getConfigJSON(true);
                $client_config_array[Client::WAYPOINT_LEDGER_DROPDOWNS] = $this->ClientRepositoryObj->getWaypointLedgerDropDownValues($ClientObj);
                $ClientObj->setConfigJSON($client_config_array);
            }

        }
        catch (Exception $e)
        {
            DB::rollBack();
            throw new GeneralException($e->getMessage(), 404, $e);
        }

        DB::commit();

        return true;
    }
}
