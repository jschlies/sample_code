<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Command;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use DB;
use App\Waypoint\Repositories\CalculateVariousPropertyListsRepository;
use App\Waypoint\Exceptions\GeneralException;

class SyncSquareFootageCommand extends Command
{
    /** @var  PropertyRepository */
    protected $PropertyRepositoryObj;
    /** @var  string */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:sync_square_footage
                            {--client_ids= : Comma separated list client IDs or \'All\' }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the as-of-month\'s square footage to the hermes database from ledger';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->syncSquareFootageFromLedger($this->getClientsFromArray($this->option('client_ids')));
    }

    public function syncSquareFootageFromLedger($client_id_array)
    {
        /** @var Client $ClientObj */
        foreach ($client_id_array as $ClientObj)
        {
            if ( ! $ClientObj->name = Client::DUMMY_CLIENT_NAME)
            {
                continue;
            }
            if ( ! $ClientObj->client_id_old)
            {
                continue;
            }
            // pass over client if does not have associated ledger database
            if ( ! DatabaseConnectionRepository::schemasExist($ClientObj, 'waypoint_ledger_' . $ClientObj->client_id_old))
            {
                continue;
            }

            $property_collection         = Property::where('client_id', $ClientObj->id)->pluck('property_id_old');
            $LedgerDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);

            $results = $LedgerDatabaseConnectionObj
                ->table('OCCUPANCY_PERCENT')
                ->where(
                    [
                        ['FK_ACCOUNT_CLIENT_ID', $ClientObj->client_id_old],
                        ['RENTABLE_AREA', '!=', 0],
                    ]
                )
                ->whereNotNull('RENTABLE_AREA')
                ->whereIn('FK_PROPERTY_ID', $property_collection)
                ->select('RENTABLE_AREA as square_footage', 'FK_PROPERTY_ID as property_id_old')
                ->get();

            $this->PropertyRepositoryObj = App::make(PropertyRepository::class)->setSuppressEvents(true);

            if ($results->count() > 0)
            {
                foreach ($results as $result)
                {
                    /** @var Property $PropertyObj */
                    foreach (
                        $this->PropertyRepositoryObj->findWhere(
                            [
                                'property_id_old' => $result->property_id_old,
                                'client_id'       => $ClientObj->id,
                            ]
                        ) as $PropertyObj)
                    {
                        $this->PropertyRepositoryObj->update(
                            ['square_footage' => $result->square_footage],
                            $PropertyObj->id
                        );
                    }
                }
            }

            $this->runJobInline($ClientObj);
        }
    }

    private function runJobInline(Client $ClientObj)
    {
        try
        {
            $CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class)->setSuppressEvents(true);
            $CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id);
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
    }
}
