<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Models\Property;
use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use DB;
use Rollbar\Payload\Level;
use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshLeasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:leases:refresh  
                        {--client_id= : client_id} 
                        {--property_id= : property_id} 
                        {--wipe_out_all_tenants_and_leases : wipe_out_all_tenants_and_leases default is false - This will also wipe out ALL leases and tenants of the client, even those in other properties. Use with care. You have been warned}
                        {--wipe_out_all_leases : wipe_out_all_leases default is false}
                        {--wipe_out_all_suites_and_leases : wipe_out_all_suites_and_leases default is false}
                        {--do_not_refresh : do_not_refresh default is false} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh leases per client and (optionally) per property. If wipe_out_all_leases is true ALL leases for the client/property(s) will be deleted';

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
        $this->alert('Starting command ' . $this->getName());
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }
        if ( ! $property_id = $this->option('property_id'))
        {
            $property_id = null;
        }
        $wipe_out_all_leases = false;
        if ($this->option('wipe_out_all_leases'))
        {
            $wipe_out_all_leases = true;
        }
        $wipe_out_all_tenants_and_leases = false;
        if ($this->option('wipe_out_all_tenants_and_leases'))
        {
            $wipe_out_all_tenants_and_leases = true;
        }
        $wipe_out_all_suites_and_leases = false;
        if ($this->option('wipe_out_all_suites_and_leases'))
        {
            $wipe_out_all_suites_and_leases = true;
        }

        $do_not_refresh = false;
        if ($this->option('do_not_refresh'))
        {
            $do_not_refresh = true;
        }

        if (
            $wipe_out_all_suites_and_leases && $wipe_out_all_leases ||
            $wipe_out_all_suites_and_leases && $wipe_out_all_tenants_and_leases ||
            $wipe_out_all_leases && $wipe_out_all_tenants_and_leases
        )
        {
            throw new GeneralException("Make up your mind - wipe_out_all_leases, wipe_out_all_tenants_and_leases or wipe_out_all_suites_and_leases", 500);
        }

        if ( ! $client_id and $property_id)
        {
            throw new GeneralException("no client_id / property_id found", 500);
        }

        $this->processRefreshLeasesCommand($client_id, $property_id, $wipe_out_all_leases, $wipe_out_all_suites_and_leases, $wipe_out_all_tenants_and_leases, $do_not_refresh);
        return true;
    }

    /**
     * @param null|integer $client_id
     * @param null|integer $property_id
     * @param bool $wipe_out_all_leases
     * @param bool $wipe_out_all_suites_and_leases
     * @param bool $wipe_out_all_tenants_and_leases
     * @param bool $do_not_refresh
     * @throws Exception
     */
    public function processRefreshLeasesCommand(
        $client_id = null,
        $property_id = null,
        $wipe_out_all_leases = false,
        $wipe_out_all_suites_and_leases = false,
        $wipe_out_all_tenants_and_leases = false,
        $do_not_refresh = false
    ) {

        /**
         * for testing
         */
        if (
            env('APP_ENV', false) == 'local' &&
            config('waypoint.use_mock_objects', false)
        )
        {
            if (config('waypoint.use_rentrollmockrepository', false))
            {
                LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
            }
        }

        if ($client_id && ! $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->find($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }
            $PropertyObjArr = $ClientObj->properties;
        }
        elseif ($client_id && $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->find($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }

            $PropertyObjArr = collect_waypoint([$this->PropertyRepositoryObj->find($property_id)]);
        }
        else
        {
            throw new GeneralException("no client_id found", 500);
        }

        /**
         * remember that tenants exist at the Client level
         */
        if ($wipe_out_all_tenants_and_leases)
        {
            $this->alert('Wiping out tenants (and ALL their leases) for client_id = ' . $ClientObj->id);
            /**
             * we want to roll back this particular command
             */
            DB::beginTransaction();
            try
            {
                foreach ($ClientObj->tenants as $TenantObj)
                {
                    foreach ($TenantObj->leases as $LeaseObj)
                    {
                        $this->LeaseRepositoryObj->delete($LeaseObj->id);
                    }
                    $this->TenantRepositoryObj->delete($TenantObj->id);

                }
                foreach ($ClientObj->properties as $PropertyObj)
                {
                    foreach ($PropertyObj->leases as $LeaseObj)
                    {
                        $this->LeaseRepositoryObj->delete($LeaseObj->id);
                    }
                    /**
                     * for cancade delete reasons, we need to wipe all lease schedules because some ,may hace a null pointer to suites. This is
                     * important since we use lease_schedules to determin last processed date
                     */
                    $this->zap_null_lease_schedules($PropertyObj->id);
                }
                $this->alert(str_repeat('-', 30));
            }
            catch (GeneralException $e)
            {
                $this->alert('Unable to delete tenants.  $ClientObj->id = ' . $ClientObj->id . ' because ' . $e->getMessage());
                $this->alert('Processing on client_id = ' . $client_id . ' has been rolled back');
                DB::rollBack();
                throw $e;
            }
            catch (Exception $e)
            {
                $this->alert('Unable to delete tenants.  $ClientObj->id = ' . $ClientObj->id . ' because ' . $e->getMessage());
                $this->alert('Unable to delete tenants.  $ClientObj->id = ' . $ClientObj->id . ' has been rolled back');
                DB::rollBack();
                throw new GeneralException($e->getMessage(), 404, $e);
            }
            catch (Throwable $e)
            {
                $this->alert('Unable to delete tenants.  $ClientObj->id = ' . $ClientObj->id . ' because ' . $e->getMessage());
                $this->alert('Unable to delete tenants.  $ClientObj->id = ' . $ClientObj->id . ' has been rolled back');
                DB::rollBack();
                $e = new FatalThrowableError($e);
                throw new GeneralException($e->getMessage(), 404, $e);
            }
            DB::commit();
        }

        /** @var App\Waypoint\Collection $PropertyObjArr */
        /** @var Property $PropertyObj */
        foreach ($PropertyObjArr as $PropertyObj)
        {
            $this->alert('Processing lease_refresh for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);

            if ($wipe_out_all_suites_and_leases)
            {
                /**
                 * we want to roll back this particular command
                 */
                DB::beginTransaction();
                try
                {
                    $this->alert('Wiping out suites and leases for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
                    foreach ($PropertyObj->suites as $SuiteObj)
                    {
                        $this->SuiteDetailRepositoryObj->delete($SuiteObj->id);
                    }
                    /**
                     * for cancade delete reasons, we need to wipe all lease schedules because some ,may hace a null pointer to suites. This is
                     * important since we use lease_schedules to determin last processed date
                     */
                    $this->zap_null_lease_schedules($PropertyObj->id);
                }
                catch (GeneralException $e)
                {
                    $this->alert('Unable to suites and leases leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    DB::rollBack();
                    throw $e;
                }
                catch (Exception $e)
                {
                    $this->alert('Unable to suites and leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    DB::rollBack();
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                catch (Throwable $e)
                {
                    $this->alert('Unable to delete suites and leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    $e = new FatalThrowableError($e);
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                DB::commit();

                $this->alert('Finished wiping out suites and leases for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
                $this->alert(str_repeat('-', 30));
            }
            elseif ($wipe_out_all_leases)
            {
                /**
                 * we want to roll back this particular command
                 */
                DB::beginTransaction();
                try
                {
                    $this->alert('Wiping out leases for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
                    foreach ($PropertyObj->leases as $LeaseObj)
                    {
                        $this->LeaseRepositoryObj->delete($LeaseObj->id);
                    }
                    /**
                     * for cancade delete reasons, we need to wipe all lease schedules because some ,may hace a null pointer to suites. This is
                     * important since we use lease_schedules to determin last processed date
                     */
                    $this->zap_null_lease_schedules($PropertyObj->id);
                    $this->alert('Finished wiping out leases for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
                    $this->alert(str_repeat('-', 30));
                }
                catch (GeneralException $e)
                {
                    $this->alert('Unable to delete leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    DB::rollBack();
                    throw $e;
                }
                catch (Exception $e)
                {
                    $this->alert('Unable to delete leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    DB::rollBack();
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                catch (Throwable $e)
                {
                    $this->alert('Unable to delete leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    $e = new FatalThrowableError($e);
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                DB::commit();
            }

            if ( ! $do_not_refresh)
            {
                /**
                 * we want to roll back this particular command
                 */
                DB::beginTransaction();
                try
                {
                    $this->logToGraylogAndEcho(
                        Level::ALERT,
                        'Processing uploading lease for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
                    $this->LeaseRepositoryObj->upload_leases_for_property($PropertyObj->id);
                    $this->logToGraylogAndEcho(
                        Level::ALERT,
                        'Finished uploading lease for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id
                    );
                    $this->alert(str_repeat('-', 30));
                }
                catch (GeneralException $e)
                {
                    $this->alert('Unable upload leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' refresh has been rolled back');

                    DB::rollBack();
                    throw $e;
                }
                catch (Exception $e)
                {
                    $this->alert('Unable upload leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' refresh has been rolled back');
                    DB::rollBack();
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                catch (Throwable $e)
                {
                    $this->alert('Unable upload leases.  $PropertyObj->id = ' . $PropertyObj->id . ' because ' . $e->getMessage());
                    $this->alert('Processing on client_id = ' . $client_id . ' property_id = ' . $PropertyObj->id . ' has been rolled back');
                    DB::rollBack();
                    $e = new FatalThrowableError($e);
                    throw new GeneralException($e->getMessage(), 404, $e);
                }
                DB::commit();
            }

            $this->alert('Finished processing suites, leases and tenants for client_id = ' . $PropertyObj->client_id . ' property_id = ' . $PropertyObj->id);
        }
    }

    /**
     * @param integer $property_id
     */
    public function zap_null_lease_schedules($property_id)
    {
        DB::delete(
            DB::raw(
                '
                    DELETE FROM lease_schedules
                        WHERE 
                            lease_schedules.property_id =  :PROPERTY_ID
                '
            ),
            [
                'PROPERTY_ID' => $property_id,
            ]
        );
    }
}
