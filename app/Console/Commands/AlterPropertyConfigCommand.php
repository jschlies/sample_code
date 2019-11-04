<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AlterPropertyConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterPropertyConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:config:property  
                        {--property_id= : property_id} 
                        {--config_name= : config_name} 
                        {--config_value= : config_value  true, false, any scalar value or array of scalars. To indicate a one element list, pass the element and append a comma as in "my_value,". For an empty array, simply pass a comma. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add/change a config of a property. ' . PHP_EOL .
                             'Null config_name yields printout of property config' . PHP_EOL .
                             'All config names are converted to UPPERCASE ';

    /**
     * AlterPropertyConfigCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->PropertyRepositoryObj in parent::__construct. Messes up code generator
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

        if ( ! $property_id = $this->option('property_id'))
        {
            throw new GeneralException("no property_id found", 400);
        }
        if ( ! $PropertyObj = $this->PropertyRepositoryObj->find($property_id))
        {
            throw new GeneralException("no property_id found", 400);
        }
        if ( ! $config_name = $this->option('config_name'))
        {
            $this->alert(print_r($PropertyObj->getConfigJSON(), true));
            return;
        }
        if ( ! $this->option('config_value'))
        {
            $this->alert(print_r($PropertyObj->getConfigJSON(), true));
            return;
        }
        $config_value = $this->stringToTypedValue($this->option('config_value'));

        $PropertyObj->updateConfig($config_name, $config_value);

        return true;
    }
}