<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 *
 */
class ListClientsGroupCalcStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:clients_group_calc_status 
                        {--client_ids= : Comma separated list client IDs or \'All\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List client information';

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
        foreach ($this->getClientsFromArray($this->option('client_ids')) as $ClientObj)
        {
            $return_me[] = [
                'id'                                 => $ClientObj->id,
                'client_id_old'                      => $ClientObj->client_id_old,
                'name'                               => $ClientObj->name,
                "property_group_calc_status"         => $ClientObj->property_group_calc_status,
                "property_group_calc_last_requested" => $ClientObj->property_group_calc_last_requested,
            ];
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