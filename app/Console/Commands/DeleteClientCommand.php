<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class DeleteClientCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class DeleteClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:delete:client 
                        {--client_id= : Client_id, not old_client_id, of client to be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a client';

    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messses with code generator
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

        $this->loadAllRepositories(true);

        $client_id = $this->option('client_id');
        if (1 == $client_id)
        {
            throw new GeneralException('Are you crazy!!!! You cannot delete the "Dummy Client" ', 500);
        }
        /** @var App\Waypoint\Models\Client $ClientObj */
        if ( ! $ClientObj = $this->ClientRepositoryObj->findWithoutFail($client_id))
        {
            throw new ModelNotFoundException('No such client', 500);
        }
        /**
         * we think we may be getting MySql timeouts (think of the cascading) when
         * deleting whole clients, this breaks things up a little
         */

        foreach ($ClientObj->propertyGroups as $PropertyGroupObj)
        {
            foreach ($PropertyGroupObj->customReports as $CustomReportObj)
            {
                $this->CustomReportRepositoryObj->delete($CustomReportObj->id);
            }
        }
        foreach ($ClientObj->propertyGroups as $PropertyGroupObj)
        {
            $this->PropertyGroupRepositoryObj->delete($PropertyGroupObj->id);
        }
        /** @var App\Waypoint\Models\Property $PropertyObj */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            foreach ($PropertyObj->customReports as $CustomReportObj)
            {
                $this->CustomReportRepositoryObj->delete($CustomReportObj->id);
            }
        }
        /** @var App\Waypoint\Models\Property $PropertyObj */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            $this->PropertyRepositoryObj->delete($PropertyObj->id);
        }
        foreach ($ClientObj->assetTypes as $AssetTypeObj)
        {
            $this->AssetTypeRepositoryObj->delete($AssetTypeObj->id);
        }
        $this->ClientRepositoryObj->delete($client_id);

        return true;
    }
}