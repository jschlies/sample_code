<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;

/**
 * Class AlterStyleCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterStyleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:style:alter  
                        {--client_id= : client_id} 
                        {--style_name= : style_name} 
                        {--style_value= : style_value (true, false, scalar value, comma seperated list of values. To indicate a one element list, pass the element and append a comma as in "my_value,". For an empty array, simply pass a comma.)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add/change a style of a client. Null config_name yields printout of client config' . PHP_EOL .
                             'Null config_name yields printout of client style' . PHP_EOL .
                             'All config names are converted to UPPERCASE ';

    /**
     * AlterStyleCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

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
        if ( ! $style_name = $this->option('style_name'))
        {
            echo print_r(App\Waypoint\Models\Client::find($client_id)->getStyleJSON(), true);
            return;
        }
        if ( ! $style_value = $this->option('style_value'))
        {
            echo print_r(App\Waypoint\Models\Client::find($client_id)->getStyleJSON(), true);
            return;
        }

        $this->processAlterStyleCommand($client_id, $style_name, $style_value);
        return true;
    }

    /**
     * @param integer $client_id
     * @param $style_name
     * @param $style_value
     * @throws GeneralException
     * @throws \Exception
     */
    public function processAlterStyleCommand($client_id, $style_name, $style_value)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj = Client::find($client_id))
        {
            throw new GeneralException("no client_id found", 404);
        }

        $style_value = $this->stringToTypedValue($style_value);
        $ClientObj->updateConfig($style_name, $style_value);
    }
}