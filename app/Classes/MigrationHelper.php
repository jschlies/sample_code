<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Console\Commands\CreateAllSQSQueuesCommand;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use Artisan;
use ClientSeeder;

/**
 * Class MigrationHelper
 * @package App\Waypoint
 *
 * @codeCoverageIgnore
 */
class MigrationHelper
{
    use MigrationHelperPotpourriTrait;

    /** @var  ClientRepository */
    private $ClientRepositoryObj;

    /**
     * MigrationHelper constructor.
     */
    public function __construct()
    {
        $this->ClientRepositoryObj = App::make(ClientRepository::class);
    }

    /**
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public function post_migration_initialization()
    {
        if (config('queue.driver', 'sync') == 'sqs')
        {
            /***************************************************************************/
            echo('Starting CreateAllSQSQueuesCommand' . PHP_EOL);
            $CreateAllSQSQueuesCommandObj = new CreateAllSQSQueuesCommand();
            $CreateAllSQSQueuesCommandObj->create_all_sqs_queues();
        }

        /* ------------------------------------------------------
         |  CODE TO ONLY RUN LOCALLY
         ------------------------------------------------------*/
        if (env('APP_ENV') == 'local')
        {
            $PropertyRepositoryObj      = App::make(PropertyRepository::class);
            $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
            foreach ($this->ClientRepositoryObj->findWhere([['name', 'regexp', '^.*SEEDED.*$']]) as $ClientToDeleteObj)
            {
                /**
                 * this has to do w/ db constraints and the fact that a PropertyGroup is
                 * a property of buth Client and Property
                 */
                echo('Deleting old client_seed ' . $ClientToDeleteObj->name . PHP_EOL);
                $PropertyRepositoryObj->setSuppressEvents(true);
                $PropertyGroupRepositoryObj->setSuppressEvents(true);
                foreach ($ClientToDeleteObj->properties as $PropertyObj)
                {
                    $PropertyRepositoryObj->delete($PropertyObj->id);
                }

                foreach ($ClientToDeleteObj->propertyGroups as $PropertyGroupObj)
                {
                    $PropertyGroupRepositoryObj->delete($PropertyGroupObj->id);
                }
                $this->ClientRepositoryObj->delete($ClientToDeleteObj->id);
            }
            echo('Starting client_seed' . PHP_EOL);

            /** @var ClientSeeder $ClientSeederObj */
            $ClientSeederObj = new ClientSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ClientSeederObj->run();

            /***************************************************************************/
            echo('Starting postman_collection' . PHP_EOL);
            Artisan::call(
                'waypoint:postman_collection',
                []
            );

            /***************************************************************************/
            echo('Starting generate_javaScript_config' . PHP_EOL);
            Artisan::call(
                'waypoint:generate_javaScript_config',
                []
            );
        }

        $exception_message = '';
        if ( ! is_file(storage_path() . '/logs/README'))
        {
            $exception_message .= storage_path() . '/logs/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/app/temp_attachments/README'))
        {
            $exception_message .= storage_path() . '/app/temp_attachments/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/app/temp_images/README'))
        {
            $exception_message .= storage_path() . '/app/temp_images/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/framework/cache/README'))
        {
            $exception_message .= storage_path() . '/framework/cache/README does not exist' . PHP_EOL;
        }
        if (strtolower(App::environment()) == 'local')
        {
            if ( ! is_file(storage_path() . '/framework/sessions/README'))
            {
                $exception_message .= storage_path() . '/framework/sessions/README does not exist' . PHP_EOL;
            }
        }
        if ( ! is_file(storage_path() . '/framework/views/README'))
        {
            $exception_message .= storage_path() . '/framework/views/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/cache/image/README'))
        {
            $exception_message .= storage_path() . '/cache/image/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/exports/mocks/README'))
        {
            $exception_message .= storage_path() . '/exports/mocks/README does not exist' . PHP_EOL;
        }
        if ( ! is_file(storage_path() . '/exports/README'))
        {
            $exception_message .= storage_path() . '/exports/README does not exist' . PHP_EOL;
        }
        if ($exception_message)
        {
            echo
                'Post Migration ran with success (and can be considered finished) but the following files do not exist.' . PHP_EOL .
                'Please create them with 777 premission and file an urgent ticket w/ Dev' . PHP_EOL .
                $exception_message;
        }
    }
}
