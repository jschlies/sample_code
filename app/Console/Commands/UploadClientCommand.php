<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\ClientRepository;
use Artisan;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class UploadClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:upload_client 
                        {--client_json_file= : JSON client download file} 
                        {--client_suffix= : Append to client name to maintain uniqueness (must be unique across all existing clients!!!!!!)}  
                        {--client_id_old= : client_id_old - Default is same as copied client }
                        {--copy_all_users : copy all users - Default is copy only CLIENT_ADMINISTRATIVE_USER_ROLE users} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload client information';

    private $old_new_cross_ref = [];

    /**
     * Execute the console command.
     *
     * @throws GeneralException
     * @todo See HER-424 $client_id_arr
     *
     */
    public function handle()
    {
        parent::handle();

        /** @var ClientRepository $ClientRepositoryObj */
        $this->ClientRepositoryObj->setSuppressEvents(true);

        /**
         * we'll deal with events and the rest at the end. See PropertyCreatedEvent below
         */
        $client_suffix = null;
        $client_id_old = null;
        if ($this->hasOption('client_suffix') && $this->option('client_suffix'))
        {
            $client_suffix = ' ' . $this->option('client_suffix');
        }
        if ($this->hasOption('client_id_old') && $this->option('client_id_old'))
        {
            $client_id_old = $this->option('client_id_old');
        }

        $client_full_arr = json_decode(file_get_contents($this->option('client_json_file')), true, 1000);

        $client_full_arr['name']         .= $client_suffix;
        $client_full_arr['display_name'] .= $client_suffix;
        if ($client_id_old)
        {
            $client_full_arr['client_id_old'] = $client_id_old;
        }

        $NewClientObj = $ClientRepositoryObj->import_client($client_full_arr, $this->option('copy_all_users'));
        /**
         * @todo see HER-424 - we should be passing in a client_id here
         */

        if ($NewClientObj->client_id_old)
        {
            if (config('waypoint.waypoint_ledger_mysql_connection_available', true))
            {
                Artisan::call(
                    'waypoint:update_client_benchmark_config',
                    []
                );
            }
        }

        $this->old_new_cross_ref = [];

        return true;
    }

    public function upload_client()
    {

    }
}