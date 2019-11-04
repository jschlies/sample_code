<?php

namespace App\Waypoint\Console;

use App;
use App\Waypoint\Console\Commands\AddUsersCommand;
use App\Waypoint\Console\Commands\AlterClientConfigAdvancedVarianceCommand;
use App\Waypoint\Console\Commands\AlterClientConfigCommand;
use App\Waypoint\Console\Commands\AlterFilterCommand;
use App\Waypoint\Console\Commands\AlterNativeAccountTypeTabOrderingCommand;
use App\Waypoint\Console\Commands\AlterPropertyConfigCommand;
use App\Waypoint\Console\Commands\AlterStyleCommand;
use App\Waypoint\Console\Commands\AlterUserConfigCommand;
use App\Waypoint\Console\Commands\CheckForDormantUsersCommand;
use App\Waypoint\Console\Commands\ClientSeederCommand;
use App\Waypoint\Console\Commands\CreateAllSQSQueuesCommand;
use App\Waypoint\Console\Commands\DeactivateUsersCommand;
use App\Waypoint\Console\Commands\DeleteClientCommand;
use App\Waypoint\Console\Commands\DeployNodeCommand;
use App\Waypoint\Console\Commands\DownloadClientCommand;
use App\Waypoint\Console\Commands\EnvCheckCommand;
use App\Waypoint\Console\Commands\FlushCacheCommand;
use App\Waypoint\Console\Commands\FlushEntrustCacheCommand;
use App\Waypoint\Console\Commands\FlushNonEntrustCacheCommand;
use App\Waypoint\Console\Commands\FlushNonSessionCacheCommand;
use App\Waypoint\Console\Commands\GenerateApiKeyCommand;
use App\Waypoint\Console\Commands\GenerateAuditActivityCommand;
use App\Waypoint\Console\Commands\GenerateCommentsCommand;
use App\Waypoint\Console\Commands\GenerateGenericUsersCommand;
use App\Waypoint\Console\Commands\GenerateJavaScriptConfigCommand;
use App\Waypoint\Console\Commands\GenerateScheduledAdvancedVariancesCommand;
use App\Waypoint\Console\Commands\GenerateSystemInfoCommand;
use App\Waypoint\Console\Commands\ImageCleanupCommand;
use App\Waypoint\Console\Commands\InitializeCoaToClientTwoCommand;
use App\Waypoint\Console\Commands\Inspire;
use App\Waypoint\Console\Commands\ListAccessListPropertiesCommand;
use App\Waypoint\Console\Commands\ListAccessListsCommand;
use App\Waypoint\Console\Commands\ListAccessListUsersCommand;
use App\Waypoint\Console\Commands\ListClientsCommand;
use App\Waypoint\Console\Commands\ListClientsGroupCalcStatusCommand;
use App\Waypoint\Console\Commands\ListPropertiesCommand;
use App\Waypoint\Console\Commands\ListPropertyGroupsCommand;
use App\Waypoint\Console\Commands\ListPropertyGroupsWithClientIdOldCommand;
use App\Waypoint\Console\Commands\ListUsersCommand;
use App\Waypoint\Console\Commands\LockAdvancedVariancesCommand;
use App\Waypoint\Console\Commands\MergeCoverageCommand;
use App\Waypoint\Console\Commands\PerformanceCommand;
use App\Waypoint\Console\Commands\PooperScooperCommand;
use App\Waypoint\Console\Commands\PostmanCollectionCommand;
use App\Waypoint\Console\Commands\PropertyGroupCalcDaemonCommand;
use App\Waypoint\Console\Commands\PropertyGroupCalcHistoryCommand;
use App\Waypoint\Console\Commands\PropertyGroupCalcTriggerCommand;
use App\Waypoint\Console\Commands\RefreshAdvancedVariancesCommand;
use App\Waypoint\Console\Commands\RefreshAuditsCommand;
use App\Waypoint\Console\Commands\RefreshGeneratedListsAndGroupsCommand;
use App\Waypoint\Console\Commands\RefreshLeasesCommand;
use App\Waypoint\Console\Commands\RefreshNativeAccountAmountsCommand;
use App\Waypoint\Console\Commands\RefreshPropertyAddressCommand;
use App\Waypoint\Console\Commands\RouteReportCommand;
use App\Waypoint\Console\Commands\SetDropdownDefaultCommand;
use App\Waypoint\Console\Commands\SyncSquareFootageCommand;
use App\Waypoint\Console\Commands\TriggerAdvancedVariancesJobCommand;
use App\Waypoint\Console\Commands\UpdateClientBenchmarkConfigCommand;
use App\Waypoint\Console\Commands\UploadClientCommand;
use App\Waypoint\Console\Commands\WaypointMasterExtractPropertyCodeMappingCommand;
use App\Waypoint\Console\Commands\WPListenCommand;
use App\Waypoint\Exceptions\ExceptionHandler;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\MigrationHelper;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use DB;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        /**
         * Deployment Command
         */
        DeployNodeCommand::class,
        GenerateJavaScriptConfigCommand::class,

        /**
         * Just for fun
         */
        Inspire::class,

        /**
         * Postman
         */
        PostmanCollectionCommand::class,

        /**
         * Seeders
         */
        ClientSeederCommand::class,
        GenerateGenericUsersCommand::class,
        AddUsersCommand::class,

        /**
         * Cleanup related
         */
        DeleteClientCommand::class,
        FlushCacheCommand::class,
        FlushNonSessionCacheCommand::class,

        /**
         * Run from time to time
         */
        AlterClientConfigAdvancedVarianceCommand::class,
        AlterClientConfigCommand::class,
        AlterFilterCommand::class,
        AlterNativeAccountTypeTabOrderingCommand::class,
        AlterPropertyConfigCommand::class,
        AlterStyleCommand::class,
        AlterUserConfigCommand::class,
        CheckForDormantUsersCommand::class,
        CreateAllSQSQueuesCommand::class,
        DeactivateUsersCommand::class,
        DownloadClientCommand::class,
        EnvCheckCommand::class,
        FlushEntrustCacheCommand::class,
        FlushNonEntrustCacheCommand::class,
        GenerateApiKeyCommand::class,
        GenerateAuditActivityCommand::class,
        GenerateCommentsCommand::class,
        GenerateSystemInfoCommand::class,
        ImageCleanupCommand::class,
        InitializeCoaToClientTwoCommand::class,
        ListAccessListPropertiesCommand::class,
        ListAccessListsCommand::class,
        ListAccessListUsersCommand::class,
        ListClientsCommand::class,
        ListClientsGroupCalcStatusCommand::class,
        ListPropertiesCommand::class,
        ListPropertyGroupsCommand::class,
        ListPropertyGroupsWithClientIdOldCommand::class,
        ListUsersCommand::class,
        MergeCoverageCommand::class,
        PerformanceCommand::class,
        PooperScooperCommand::class,
        PropertyGroupCalcDaemonCommand::class,
        PropertyGroupCalcHistoryCommand::class,
        PropertyGroupCalcTriggerCommand::class,
        RefreshAdvancedVariancesCommand::class,
        LockAdvancedVariancesCommand::class,
        RefreshAuditsCommand::class,
        RefreshGeneratedListsAndGroupsCommand::class,
        RefreshLeasesCommand::class,
        RefreshNativeAccountAmountsCommand::class,
        RefreshPropertyAddressCommand::class,
        RouteReportCommand::class,
        SetDropdownDefaultCommand::class,
        SyncSquareFootageCommand::class,
        TriggerAdvancedVariancesJobCommand::class,
        UpdateClientBenchmarkConfigCommand::class,
        UploadClientCommand::class,
        WaypointMasterExtractPropertyCodeMappingCommand::class,
        WPListenCommand::class,

        /**
         * Run by the scheduler
         */
        GenerateScheduledAdvancedVariancesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $scheduled_advanced_variances_cron = config('waypoint.scheduled_advanced_variances_cron');
        if ($scheduled_advanced_variances_cron)
        {
            // TODO After we upgrade Laravel to 5.6 or later, add "->onOneServer()" to the below.
            $schedule->command(GenerateScheduledAdvancedVariancesCommand::class, ['--client_ids=All'])->cron($scheduled_advanced_variances_cron)->withoutOverlapping();
        }
        $scheduled_flush_failed_jobs_cron = config('waypoint.scheduled_advanced_variances_cron', '0 0 1 * *');
        if ($scheduled_flush_failed_jobs_cron)
        {
            // TODO After we upgrade Laravel to 5.6 or later, add "->onOneServer()" to the below.
            $schedule->command(FlushFailedCommand::class, [])->cron($scheduled_flush_failed_jobs_cron)->withoutOverlapping();
        }
    }

    /**
     * Run the console application.
     *
     * @param ArgvInput $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        $this->bootstrap();

        /**
         * since the infyom scripts generate the base repositories,we can't load
         * *repository here, even as a static
         */
        if (
            ! preg_match("/infyom/", $input->getFirstArgument()) &&
            env('APP_ENV', false) == 'local' &&
            config('waypoint.use_mock_objects', false)
        )
        {
            if (config('waypoint.use_auth0apimanagementusermock', false))
            {
                ListUsersCommand::setAuth0ManagementUsersObj(new Auth0ApiManagementUserMock());
                UserRepository::setAuth0ApiManagementUserObj(new Auth0ApiManagementUserMock());
            }
            if (config('waypoint.use_auth0apimanagementconnectionmock', false))
            {
                PasswordRuleRepository::setAuth0ApiManagementConnectionObj(new Auth0ApiManagementConnectionMock());
            }
            if (config('waypoint.use_rentrollmockrepository', false))
            {
                LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
            }
            if (config('waypoint.use_nativecoaledgermockrepository', false))
            {
                AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
            }
        }

        /**
         * Deal with Deamons
         */
        if (
            /**
             * feel free to add to this list of deamons.
             *
             * Please note we always use a try/catch for artisan commands
             * except deamons which would lock up the DB
             *
             * this is a deamon and cannot not in a try/catch aka
             * DB::beginTransaction();/DB::rollback();/DB::commit();
             */
            $input->getFirstArgument() == 'waypoint:queue:listen_all_queues' ||
            $input->getFirstArgument() == 'waypoint:daemon:property_group_calc' ||
            $input->getFirstArgument() == 'waypoint:refresh_generated_lists_and_groups' ||
            $input->getFirstArgument() == 'queue:listen' ||
            $input->getFirstArgument() == 'queue:work' ||
            $input->getFirstArgument() == 'waypoint:native_account_amounts:refresh'||
            $input->getFirstArgument() == 'waypoint:pooper_scooper' ||
            $input->getFirstArgument() == 'waypoint:advance_variance:refresh' ||
            $input->getFirstArgument() == 'waypoint:advanced_variance:lock'
        )
        {
            echo 'Starting command ' . $input->getFirstArgument() . PHP_EOL;
            if ($error_code = parent::handle($input, $output))
            {
                throw new GeneralException($input->getFirstArgument() . ' failed!!!!!!!!!!!', 500);
            }
            echo 'Finishing command ' . $input->getFirstArgument() . PHP_EOL;
            return $error_code;
        }

        /**
         * theres no point in DB::beginTransaction/DB::commit/DB::rollBack
         * for migration sincec alter stmts cannot be rolled back
         */
        elseif (
            $input->getFirstArgument() == 'migrate' ||
            $input->getFirstArgument() == 'migrate:refresh'
        )
        {
            echo 'Starting command ' . $input->getFirstArgument() . PHP_EOL;
            if ($error_code = parent::handle($input, $output))
            {
                echo '*****************************************' . PHP_EOL;
                echo 'WARNING DB was NOT rolled back. Please recover DB, address issue and re-migrate. ' . PHP_EOL;
                echo '*****************************************' . PHP_EOL;
                throw new GeneralException('Migration failed!!!!!!!!!!!', 500);
            }
            else
            {
                try
                {
                    echo 'Finishing command ' . $input->getFirstArgument() . PHP_EOL;
                    echo 'Starting PostMigrationInitialization' . PHP_EOL;
                    /** @var MigrationHelper $MigrationHelperObj */
                    $MigrationHelperObj = new MigrationHelper();
                    $MigrationHelperObj->post_migration_initialization();
                    echo 'Finishing PostMigrationInitialization' . PHP_EOL;
                    return 0;
                }
                catch (GeneralException $e)
                {
                    echo '*****************************************' . PHP_EOL;
                    echo 'WARNING DB was NOT rolled back. Please recover DB, address issue and re-migrate. ' . PHP_EOL;
                    echo '*****************************************' . PHP_EOL;
                    $this->reportException($e);
                    $this->renderException($output, $e);
                    return 1;
                }
                catch (Exception $e)
                {
                    echo '*****************************************' . PHP_EOL;
                    echo 'WARNING DB was NOT rolled back. Please recover DB, address issue and re-migrate. ' . PHP_EOL;
                    echo '*****************************************' . PHP_EOL;
                    $this->reportException($e);
                    $this->renderException($output, $e);
                    return 1;
                }
                catch (Throwable $e)
                {
                    echo '*****************************************' . PHP_EOL;
                    echo 'WARNING DB was NOT rolled back. Please recover DB, address issue and re-migrate. ' . PHP_EOL;
                    echo '*****************************************' . PHP_EOL;
                    /**
                     * $this->reportException($e) and $this->renderException($output, $e) require $e to be
                     * Exception, or in our case GeneralException.
                     */
                    $e = new GeneralException($e->getMessage());
                    $this->reportException($e);
                    $this->renderException($output, $e);
                    return 1;
                }
            }
        }

        /**
         * every other command goes here
         */
        DB::beginTransaction();
        try
        {
            echo 'Starting command ' . $input->getFirstArgument() . PHP_EOL;
            if ($error_code = parent::handle($input, $output))
            {
                throw new GeneralException($input->getFirstArgument() . ' failed!!!!!!!!!!!', 500);
            }
            echo 'Finishing command ' . $input->getFirstArgument() . PHP_EOL;
        }
        catch (GeneralException $e)
        {
            DB::rollback();
            /**
             * at this point we should be 100% rolled back and
             * exception was echo'ed in renderException and reportException - see below
             */
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        }
        catch (Exception $e)
        {
            DB::rollback();
            /**
             * at this point we should be 100% rolled back and
             * exception was echo'ed in renderException and reportException - see below
             */
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            $this->reportException($e);
            $this->renderException($output, $e);

            return 1;
        }
        catch (Throwable $e)
        {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            /**
             * $this->reportException($e) and $this->renderException($output, $e) require $e to be
             * Exception, or in our case GeneralException.
             */
            $e = new GeneralException($e->getMessage());
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        }
        DB::commit();

        return 0;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param Exception $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        parent::reportException($e);
        echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Exception $e
     * @return void
     */
    protected function renderException($output, Exception $e)
    {
        $this->app[ExceptionHandler::class]->renderForConsole($output, $e);
        echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    }
}
