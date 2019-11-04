<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeCoa;
use \Faker\Factory as FakerFactory;

/**
 * Class InitializeCoaToClientTwoCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class InitializeCoaToClientTwoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:initialize_coa_to_client_two 
                        {--client_id= : Client id}
                        {--years= : Years to generate advanced variance reports}
                        {--months= : Months to generate advanced variance reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate audit activity to database';

    /**
     * ClientSeederCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         * NOTE you cannot populate $this->SuperUserObj in parent::__construct or here. Messes up migrate:refresh
         */
        $this->FakerObj = FakerFactory::create();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What!! Are you crazy!!. You can\'t run the seeder in production context', 403);
        }
        parent::handle();

        $codes_used = [];

        /** @var Client $TargetClientObj */
        $TargetClientObj = $this->ClientRepositoryObj->find($this->option('client_id'));
        $months_arr      = explode(',', $this->option('months'));
        $years_arr       = explode(',', $this->option('years'));
        /** @var Client $Client2Obj */
        $Client2Obj            = $this->ClientRepositoryObj->find(119);
        $Client2NativeAccounts = $Client2Obj->nativeCoas->map(
            function (NativeCoa $NativeCoaObj)
            {
                return $NativeCoaObj->nativeAccounts;
            }
        )->flatten();

        $processed_property_codes = [];
        foreach ($TargetClientObj->properties as $TargetPropertyObj)
        {
            {
                $processed_property_codes[] = $TargetPropertyObj->property_code;
                if ($Property2Obj = $Client2Obj->properties->filter(
                    function ($PropertyObj) use ($TargetPropertyObj)
                    {
                        return preg_match('/^' . $TargetPropertyObj->property_code . '/', $PropertyObj->property_code) ||
                               preg_match('/' . $TargetPropertyObj->property_code . '$/', $PropertyObj->property_code);
                    }
                )->first())
                {

                    $this->alert('Starting original_property_code fix  for  ' . $TargetPropertyObj->id . '  ' . $TargetPropertyObj->original_property_code);
                    $step1                      = explode(',', $Property2Obj->original_property_code);
                    $step2                      = array_unique($step1);
                    $new_original_property_code = implode(',', $step2);
                    $TargetPropertyObj          = $this->PropertyRepositoryObj->update(
                        [
                            'original_property_code' => $new_original_property_code . ',AAA' . rand(1000, 1000000) . rand(1000, 1000000),
                            'wp_property_id_old'     => $Property2Obj->wp_property_id_old,
                        ],
                        $TargetPropertyObj->id
                    );
                    $this->alert('Finished original_property_code fix  for  ' . $TargetPropertyObj->id . '  ' . $TargetPropertyObj->original_property_code);
                }

            }
            /** @var NativeCoa $NativeCoaObj */
            $candidate_codes = $Client2NativeAccounts->pluck('native_account_code')->toArray();
            foreach ($TargetClientObj->nativeCoas as $NativeCoaObj)
            {
                foreach ($NativeCoaObj->nativeAccounts as $NativeAccountObj)
                {
                    if (preg_match("/^MR/", $NativeAccountObj->native_account_code))
                    {
                        continue;
                    }
                    do
                    {
                        $random_code = array_random($candidate_codes);
                    } while (in_array($random_code, $codes_used));

                    $this->NativeAccountRepositoryObj->update(
                        [
                            'native_account_code' => $random_code,
                        ],
                        $NativeAccountObj->id
                    );
                    $codes_used[] = $random_code;
                    if (count($codes_used) >= $Client2NativeAccounts->count())
                    {
                        break 2;
                    }
                }
            }

            foreach ($TargetClientObj->properties as $PropertyObj)
            {
                foreach ($years_arr as $year)
                {
                    foreach ($months_arr as $month)
                    {
                        $this->alert('Starting AdvancedVariance for  ' . $TargetClientObj->name . ' property ' . $PropertyObj->id . ' year/month ' . $year . '/' . $month);
                        $this->AdvancedVarianceRepositoryObj->create(
                            [
                                'client_id'   => $TargetClientObj->id,
                                'property_id' => $PropertyObj->id,
                                'period_type' => 'monthly',
                                'as_of_month' => $month,
                                'as_of_year'  => $year,
                            ]
                        );
                        $this->alert('Finished AdvancedVariance for  ' . $TargetClientObj->name . ' property ' . $PropertyObj->id . ' year/month ' . $year . '/' . $month);
                    }
                }
            }
        }
        return true;
    }
}