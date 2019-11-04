<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\TestCase;
use ClientSeeder;
use Exception;

/**
 * Class ClientSeederCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class ClientSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:client_seed 
                        {--numClients=1 : Number of Clients to create out of thin air}
                        {--client_suffix= : Number of Clients to create out of thin air}
                        {--suppress_delete_seeded_clients=0 : Number of Clients to create out of thin air}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds fake client(s) to database';

    /**
     * ClientSeederCommand constructor.
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
     */
    public function handle()
    {
        try
        {
            parent::handle();
            if ( ! $this->hasOption('do_not_delete_previous_seeded_clients'))
            {
                /** @var App\Waypoint\Models\Client $ClientToDeleteObj */
                foreach ($this->ClientRepositoryObj->findWhere([['name', 'regexp', '^.*SEEDED.*$']]) as $ClientToDeleteObj)
                {
                    foreach ($ClientToDeleteObj->propertyGroups as $PropertyGroupObj)
                    {
                        $PropertyGroupObj->delete();
                    }
                    foreach ($ClientToDeleteObj->properties as $PropertyObj)
                    {
                        $PropertyObj->delete();
                    }
                    $this->ClientRepositoryObj->delete($ClientToDeleteObj->id);
                    $this->alert('Deleting SEEDED Client ' . $ClientToDeleteObj->name);
                }
            }

            if ($this->input->getOption('client_suffix'))
            {
                TestCase::setUnitTestClientName($this->input->getOption('client_suffix'));
            }
            /** @var ClientSeeder $ClientSeederObj */
            $ClientSeederObj = new ClientSeeder(
                [
                    'name' => TestCase::getUnitTestClientName(),
                ],
                $this->input->getOption('numClients')
            );

            $ClientSeederObj->run();
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            $message = GeneralException::standard_exception_message($e, $this);
            echo $message . PHP_EOL;
            throw new GeneralException($e->getMessage() . ' ' . $message, 500, $e);
        }
    }
}