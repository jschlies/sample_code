<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class ListAccessListPropertiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:access_list_properties 
                        {--client_ids= : Comma separated list client IDs or \'All\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List client access list property information';

    /**
     * ListClientsCommand constructor.
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
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        $return_me = new SpreadsheetCollection();
        $filename  = Uuid::generate()->__get('string');
        /** @var Client $ClientObj */
        foreach ($this->getClientsFromArray($this->option('client_ids')) as $ClientObj)
        {
            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->accessLists as $AccessListObj)
            {
                /** @var Property $PropertyObj */
                foreach ($AccessListObj->properties as $PropertyObj)
                {
                    $return_me[] = [
                        'client_id'          => $ClientObj->id,
                        'client_name'        => $ClientObj->name,
                        'access_list_id'     => $AccessListObj->id,
                        'access_list_name'   => $AccessListObj->name,
                        'is_all_access_list' => $AccessListObj->is_all_access_list,
                        'property_id'        => $PropertyObj->id,
                        'nama'               => $PropertyObj->name,
                    ];
                }
            }
        }

        $return_me->toCSVFile($filename);
        $fq_filename = storage_path('exports') . '/' . $filename . '.csv';
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('----- Also See ' . $fq_filename . '  ------');
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert(file_get_contents($fq_filename));
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('----- Also See ' . $fq_filename . '  ------');
        $this->alert('--------------------------------------------------------------------------------------------------------');

        return true;
    }
}