<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\SpreadsheetCollection;
use Webpatser\Uuid\Uuid;

/**
 * Class ListPropertyGroupsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class ListPropertyGroupsWithClientIdOldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:property_groups_with_client_id_old 
                        {--client_id_old= : Client OLD ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List property group information';

    /**
     * ListPropertyGroupsCommand constructor.
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
        foreach ($this->ClientRepositoryObj->findWhere(['client_id_old' => $this->option('client_id_old')]) as $ClientObj)
        {
            /** @var PropertyGroup $PropertyGroupObj */
            foreach ($ClientObj->getPropertyGroups() as $PropertyGroupObj)
            {
                $return_me[] = [
                    "client"                        => $PropertyGroupObj->user->client->name,
                    "client_id"                     => $ClientObj->id,
                    "client_id_old"                 => $ClientObj->client_id_old,
                    "name"                          => $PropertyGroupObj->name,
                    "id"                            => $PropertyGroupObj->id,
                    "is_all_property_group"         => $PropertyGroupObj->is_all_property_group,
                    "property_id_md5"               => $PropertyGroupObj->property_id_md5,
                    "total_square_footage"          => $PropertyGroupObj->total_square_footage,
                    "is_public"                     => $PropertyGroupObj->is_public,
                    "user_id"                       => $PropertyGroupObj->user_id,
                    "parent_property_group_id"      => $PropertyGroupObj->parent_property_group_id,
                    "property_group_property_count" => $PropertyGroupObj->properties->count() ? $PropertyGroupObj->properties->count() : 0,
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