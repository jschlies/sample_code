<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AlterUserConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterUserConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:config:user  
                        {--user_id= : user_id} 
                        {--config_name= : config_name} 
                        {--config_value= : config_value  (true, false, scalar value, comma seperated list of values. To indicate a one element list, pass the element and append a comma as in "my_value,". For an empty array, simply pass a comma.)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add/change a config of a user. Null config_name yields printout of user config' . PHP_EOL .
                             'Null config_name yields printout of user config' . PHP_EOL .
                             'All config names are converted to UPPERCASE ';

    /**
     * AlterUserConfigCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->UserRepositoryObj in parent::__construct. Messes up code generator
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

        if ( ! $user_id = $this->option('user_id'))
        {
            throw new GeneralException("no user_id found", 500);
        }
        if ( ! $UserObj = $this->UserRepositoryObj->find($user_id))
        {
            throw new GeneralException("no user_id found", 500);
        }
        if ( ! $config_name = $this->option('config_name'))
        {
            echo print_r($UserObj->getConfigJSON(), true);
            return false;
        }
        if ( ! $this->option('config_value'))
        {
            echo print_r($UserObj->getConfigJSON(), true);
            return false;
        }
        $config_value = $this->stringToTypedValue($this->option('config_value'));

        $UserObj->updateConfig($config_name, $config_value);
        return true;
    }
}