<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;

/**
 * Class AlterFilterCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterFilterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:filter:alter
                        {--client_id= : client_id}
                        {--filter_name= : filter_name (single dropdown name only)}
                        {--filter_options= : filter_options  Comma separated list of options described in help. To indicate a one element list, pass the element and append a comma as in "my_value,". For an empty array, simply pass a comma.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Change specific filter dropdown options for a single client. POSSIBLE OPTIONS: Area: rentable, occupied, adjusted";

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */

    public function handle()
    {
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            throw new GeneralException("no client_id found", 404);
        }
        if ( ! $filter_name = $this->stringToTypedValue($this->option('filter_name')))
        {
            throw new GeneralException("filter_name not found", 404);
        }
        if ( ! $filter_options = $this->stringToTypedValue($this->option('filter_options'), true))
        {
            throw new GeneralException("filter_options not found", 404);
        }

        if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
        {
            throw new GeneralException("no client_id found", 404);
        }

        $config_JSON_arr = $ClientObj->getConfigJSON(true);

        if ( ! isset($config_JSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]))
        {
            $config_JSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY] = [];
        }
        $config_JSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY][$filter_name] = array_map(
            function ($string)
            {
                return strtoupper($string);
            },
            $filter_options
        );

        $ClientObj->setConfigJSON($config_JSON_arr);
        $this->alert('Filter dropdowns update for client: ' . $ClientObj->id);

        return true;
    }
}
