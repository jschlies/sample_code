<?php

namespace App\Waypoint\Tests\Mocks;

use App;
use Carbon\Carbon;
use Exception;

class NativeCoaLedgerMockRepository
{
    /** @var array */
    protected $fieldSearchable = [];

    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new Exception('What, you crazy!!!!! No NativeCoaLedgerMockRepository in production context ' . __FILE__);
        }
    }

    /**
     * @param int $property_id
     * @param array $native_account_codes_array
     * @param Carbon $date
     * @param bool $quarterly
     * @return array
     */
    public function getLedgerNativeAccounts(int $property_id, array $native_account_codes_array, Carbon $date, bool $quarterly): array
    {
        /**
         * generate some fake data
         */
        $ledger_native_coa_line_item_arr = [];
        $at_lease_one                    = false;
        foreach ($native_account_codes_array as $advanced_variance_default_native_account_code)
        {
            if ($at_lease_one)
            {
                $monthly_budgeted = mt_rand(10000, 1000000);

                /**
                 * about a tenth of the time actual = budgeted
                 */
                if (mt_rand(0, 10) < 1)
                {
                    $monthly_actual = $monthly_budgeted;
                }
                /**
                 * about a tenth of the time actual is slightly over budgeted
                 */
                elseif (mt_rand(0, 10) < 1)
                {
                    $monthly_actual = $monthly_budgeted - 1;
                }
                /**
                 * about a tenth of the time actual is slightly under budgeted
                 */
                elseif (mt_rand(0, 10) < 1)
                {
                    $monthly_actual = $monthly_budgeted + 1;
                }
                /**
                 * about a tenth of the time actual is a over (mt_rand(5, 15))% over budgeted
                 */
                elseif (mt_rand(0, 10) < 1)
                {
                    $monthly_actual = $monthly_budgeted - ($monthly_budgeted * mt_rand(5, 15) * .01);
                }
                /**
                 * about a tenth of the time actual is a little mt_rand(5,15) under budgeted
                 */
                elseif (mt_rand(0, 10) < 1)
                {
                    $monthly_actual = $monthly_budgeted + ($monthly_budgeted * mt_rand(5, 15) * .01);
                }
                /**
                 * about two/tenth of the time actual is a lot (mt_rand(20, 40)) over budgeted
                 */
                elseif (mt_rand(0, 10) < 2)
                {
                    $monthly_actual = $monthly_budgeted - ($monthly_budgeted * mt_rand(20, 40) * .01);
                }
                /**
                 * about two/tenth of the time actual is a lot (mt_rand(20, 40)) over budgeted
                 */
                else
                {
                    $monthly_actual = $monthly_budgeted - ($monthly_budgeted * mt_rand(20, 40) * .01);
                }
            }
            else
            {
                /**
                 * guarantee that at least ONE flags
                 */
                $monthly_budgeted = 100;
                $monthly_actual   = 100000000;
                $at_lease_one     = true;
            }

            $monthly_variance         = $monthly_actual - $monthly_budgeted;
            $monthly_percent_variance = ! empty($monthly_budgeted) ? (($monthly_actual - $monthly_budgeted) / $monthly_budgeted) * 100 : 0;

            $ledger_native_coa_line_item_arr[] = [
                'native_code' => $advanced_variance_default_native_account_code,

                'monthly_budgeted'         => $monthly_budgeted,
                'monthly_actual'           => $monthly_actual,
                'monthly_variance'         => $monthly_variance,
                'monthly_percent_variance' => $monthly_percent_variance,

                'ytd_budgeted'         => $monthly_budgeted * 6,
                'ytd_actual'           => $monthly_actual * 6,
                'ytd_variance'         => $monthly_variance * 6,
                'ytd_percent_variance' => $monthly_percent_variance * 6,

                'qtd_budgeted'         => $monthly_budgeted * 3,
                'qtd_actual'           => $monthly_actual * 3,
                'qtd_variance'         => $monthly_variance * 3,
                'qtd_percent_variance' => $monthly_percent_variance * 3,

                'forecast_budgeted'         => $monthly_budgeted * 6,
                'forecast_actual'           => $monthly_actual * 3,
                'forecast_variance'         => $monthly_variance * 3,
                'forecast_percent_variance' => $monthly_percent_variance * 3,
            ];
        }
        return $ledger_native_coa_line_item_arr;
    }
}
