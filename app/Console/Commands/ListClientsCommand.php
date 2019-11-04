<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class ListClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:clients';

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

        /** @var Client $ClientObj */
        foreach ($this->ClientRepositoryObj->all() as $ClientObj)
        {
            $num_active   = 0;
            $num_inactive = 0;
            $num_locked   = 0;
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                if ($PropertyObj->active_status == Property::ACTIVE_STATUS_ACTIVE)
                {
                    $num_active++;
                }
                elseif ($PropertyObj->active_status == Property::ACTIVE_STATUS_INACTIVE)
                {
                    $num_inactive++;
                }
                elseif ($PropertyObj->active_status == Property::ACTIVE_STATUS_LOCKED)
                {
                    $num_locked++;
                }
            }
            $return_me[] = [
                'id'                      => $ClientObj->id,
                'client_id_old'           => $ClientObj->client_id_old,
                'name'                    => $ClientObj->name,
                'num_properties'          => $ClientObj->properties ? $ClientObj->properties->count() : 0,
                'num_property_groups'     => $ClientObj->propertyGroups ? $ClientObj->propertyGroups->count() : 0,
                'num_access_lists'        => $ClientObj->accesssLists ? $ClientObj->accesssLists->count() : 0,
                'num_properties_active'   => $num_active,
                'num_properties_inactive' => $num_inactive,
                'num_properties_locked'   => $num_locked,
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