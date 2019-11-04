<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterClientConfigAdvancedVarianceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:configAdvancedVariance:client  
                        {--client_id= : client_id}  ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add/change a AdvancedVariance config of a client';

    /**
     * AlterClientConfigCommand constructor.
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
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            throw new GeneralException("no client_id found", 404);
        }
        if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
        {
            throw new GeneralException("no client_id found", 404);
        }

        $this->ClientRepositoryObj->initAdvancedVariance($client_id);

        return true;
    }
}