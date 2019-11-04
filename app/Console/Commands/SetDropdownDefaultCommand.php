<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use DB;

/**
 * Class AlterFilterCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class SetDropdownDefaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:set_filter_default_value
                        {--client_id= : client_id}
                        {--dropdown_name= : dropdown name}
                        {--default_value= : default value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Change specific filter dropdown default for a single client. Use only one value per default set. POSSIBLE OPTIONS: (
    AREA: rentable, occupied, adjusted) - (YEAR: eg. 2015) - (REPORT: actual, budget) - (PERIOD: T12, CY, YTD)";

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
            throw new GeneralException("no client_id found", 500);
        }
        if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
        {
            throw new GeneralException("no client_id found", 500);
        }
        if ( ! $dropdownName = $this->stringToTypedValue($this->option('dropdown_name')))
        {
            throw new GeneralException("dropdown_name not found", 500);
        }
        if ( ! $defaultValue = $this->stringToTypedValue($this->option('default_value')))
        {
            throw new GeneralException("filter_options not found", 500);
        }

        $config_JSON_arr = $ClientObj->getConfigJSON(true);

        $config_JSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY][$dropdownName] = strtoupper($defaultValue);
        $ClientObj->setConfigJSON($config_JSON_arr);

        $this->alert('Filter dropdowns update for client: ' . $ClientObj->id);

        DB::commit();

        return true;
    }
}
