<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use DB;
use Exception;
use Rollbar\Payload\Level;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class GenerateScheduledAdvancedVariancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:advance_variance:generate_scheduled
        {--client_ids= : Comma separated list client IDs or \'All\'}
        {--dry_run : List properties that need reports but do not generate them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates AV reports with ADVANCED_VARIANCE_SCHEDULE set to "quarterly" or "monthly" in client/property JSON. '
                             . 'Only creates reports that don\'t already exist for the property\'s TARGET_ASOF_MONTH in the staging database.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $advanced_variance_parameters_array = [];
        foreach ($this->getClientsFromArray($this->option('client_ids')) as $ClientObj)
        {
            try
            {
                $advanced_variance_parameters_array = array_merge(
                    $advanced_variance_parameters_array,
                    $this->AdvancedVarianceRepositoryObj->get_scheduled_advanced_variance_parameters($ClientObj)
                );
            }
                // Try not to let a bad config or DB table on one client
                // stop all the others from processing
            catch (GeneralException $e)
            {
                $this->logToGraylogAndEcho(Level::ALERT, $e->getMessage(), $e->getExceptionAsString());
            }
            catch (Exception $e)
            {
                $this->logToGraylogAndEcho(Level::ALERT, $e->getMessage(), $e->getTraceAsString());
            }
                // More exotic errors can crash us, but wrap in
                // GeneralException per Jim
            catch (Throwable $e)
            {
                $e = new FatalThrowableError($e);
                throw new GeneralException($e->getMessage(), 500, $e);
            }

        }

        if ($this->option('dry_run'))
        {
            echo "If --dry-run were not specified, we would generate reports with the following parameters:\n";
            foreach ($advanced_variance_parameters_array as $advanced_variance_parameters)
            {
                echo implode(", ", array_map(
                    function ($key, $value) { return "$key: $value"; },
                    array_keys($advanced_variance_parameters),
                    $advanced_variance_parameters
                ));
                echo PHP_EOL;
            }
            return;
        }

        foreach ($advanced_variance_parameters_array as $advanced_variance_parameters)
        {
            $this->logToGraylogAndEcho(
                Level::INFO, "Creating advanced variance for property " . $advanced_variance_parameters['property_id'] . ", " . $advanced_variance_parameters['as_of_year'] .
                             str_pad($advanced_variance_parameters['as_of_month'], 2, '0', STR_PAD_LEFT)
            );

            DB::beginTransaction();
            try
            {
                $this->AdvancedVarianceRepositoryObj->create($advanced_variance_parameters);
            }
                // It's expected for individual properties to fail due to
                // missing data, etc. Report the error but don't crash.
            catch (GeneralException $e)
            {
                DB::rollBack();
                $this->logToGraylogAndEcho(Level::ALERT, $e->getMessage(), $e->getExceptionAsString());
                continue;
            }
            catch (Exception $e)
            {
                DB::rollBack();
                $this->logToGraylogAndEcho(Level::ALERT, $e->getMessage(), $e->getTraceAsString());
                continue;
            }
                // More exotic errors can crash us
            catch (Throwable $e)
            {
                DB::rollBack();
                $e = new FatalThrowableError($e);
                throw new GeneralException($e->getMessage(), 500, $e);
            }
            DB::commit();
        }
    }
}
