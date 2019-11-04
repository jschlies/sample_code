<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\Property;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListPropertiesCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class ListPropertiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:properties 
                        {--client_ids= : Comma separated list client IDs or \'All\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List property information';

    /**
     * ListPropertiesCommand constructor.
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
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                $return_me[] = [
                    'id'                     => $PropertyObj->id,
                    'client_id'              => $PropertyObj->client_id,
                    'name'                   => $PropertyObj->name,
                    'display_name'           => $PropertyObj->display_name,
                    'property_code'          => $PropertyObj->property_code,
                    'description'            => $PropertyObj->description,
                    'active_status'          => $PropertyObj->active_status,
                    'property_id_old'        => $PropertyObj->property_id_old,
                    'accounting_system'      => $PropertyObj->accounting_system,
                    'street_address'         => $PropertyObj->street_address,
                    'display_address'        => $PropertyObj->display_address,
                    'city'                   => $PropertyObj->city,
                    'state'                  => $PropertyObj->state,
                    'postal_code'            => $PropertyObj->postal_code,
                    'country'                => $PropertyObj->country,
                    'country_code'           => $PropertyObj->country_code,
                    'longitude'              => $PropertyObj->longitude,
                    'latitude'               => $PropertyObj->latitude,
                    'census_tract'           => $PropertyObj->census_tract,
                    'time_zone'              => $PropertyObj->time_zone,
                    'square_footage'         => $PropertyObj->square_footage,
                    'asset_type'             => isset($PropertyObj->assetType) ? $PropertyObj->assetType->asset_type_name : null,
                    'year_built'             => $PropertyObj->year_built,
                    'management_type'        => $PropertyObj->management_type,
                    'lease_type'             => $PropertyObj->lease_type,
                    'smartystreets_metadata' => $PropertyObj->smartystreets_metadata,
                    'property_class'         => $PropertyObj->property_class,
                    'year_renovated'         => $PropertyObj->year_renovated,
                    'number_of_buildings'    => $PropertyObj->number_of_buildings,
                    'number_of_floors'       => $PropertyObj->number_of_floors,
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