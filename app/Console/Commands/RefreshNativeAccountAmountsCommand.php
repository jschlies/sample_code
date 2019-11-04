<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
//use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use Carbon\Carbon;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshNativeAccountAmountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:native_account_amounts:refresh  
                        {--client_id= : client_id} 
                        {--property_ids= : property_ids, comma delimited} 
                        {--from_year= : from_year - default = 2010} 
                        {--from_month= : from_month - default = 1, if from_month is provided, from_year must be provided}
                        {--to_year= : to_year - default=current year} 
                        {--to_month= : to_month - default = 12, if to_month is provided, to_year must be provided} 
                        {--allow_overwrite= : allow_overwrite, if nativeAccountAmount exists, delete with old nativeAccountAmount recs} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =
        'Refresh native account amounts per client and (optionally) per property. If property is given the month and year (used together only) can be used to specify a report. THIS HAS NO ROLLBACK CAPIBILTY';

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
        /**
         * please leave this here for use in testing
         */
        //NativeAccountAmountRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
        parent::handle();

        $this->loadAllRepositories(true);

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }
        if ( ! $this->option('property_ids') || ! $property_id_arr = explode(',', $this->option('property_ids')))
        {
            $property_id_arr = [];
        }

        if ($this->option('from_month') && ! $this->option('from_year'))
        {
            throw new GeneralException("invalid property_id, month, year combo", 404);
        }
        if ( ! $from_month = (int) $this->option('from_month'))
        {
            $from_month = 1;
        }
        if ( ! $from_year = (int) $this->option('from_year'))
        {
            $from_year = 2010;
        }

        if ($this->option('to_month') && ! $this->option('to_year'))
        {
            throw new GeneralException("invalid property_id, month, year combo", 404);
        }
        if ( ! $to_month = (int) $this->option('to_month'))
        {
            $to_month = 12;
        }
        if ( ! $to_year = (int) $this->option('to_year'))
        {
            $to_year = Carbon::now()->format('Y');
        }
        if ( ! $allow_overwrite = (bool) $this->option('allow_overwrite'))
        {
            $allow_overwrite = false;
        }

        $this->NativeAccountAmountRepositoryObj->processRefreshNativeAccountValues(
            $client_id,
            $property_id_arr,
            $from_month,
            $from_year,
            $to_month,
            $to_year,
            $allow_overwrite
        );

        return true;
    }
}
