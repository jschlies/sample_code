<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\AccessList;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListAccessListsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class ListAccessListsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:access_lists 
                        {--client_ids= : Comma separated list client IDs or \'All\' }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List property group information';

    /**
     * ListAccessListsCommand constructor.
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
        foreach ($this->getClientsFromArray($this->option('client_ids')) as $ClientObj)
        {
            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->accessLists as $AccessListObj)
            {
                $return_me[] = [
                    "id"                 => $AccessListObj->id,
                    "name"               => $AccessListObj->name,
                    "client_id"          => $AccessListObj->client_id,
                    "description"        => $AccessListObj->description,
                    "is_all_access_list" => $AccessListObj->is_all_access_list,
                    "num_properties"     => $AccessListObj->properties ? $AccessListObj->properties->count() : 0,
                    "num_users"          => $AccessListObj->users ? $AccessListObj->users->count() : 0,
                ];
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