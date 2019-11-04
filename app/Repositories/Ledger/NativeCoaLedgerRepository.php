<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Model;
use App\Waypoint\Models\Ledger\NativeCoa;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Cache;
use Carbon\Carbon;
use DB;
use function collect_waypoint;
use Exception;
use function implode;
use function in_array;
use function is_null;
use function isZero;
use Illuminate\Container\Container as Application;

class NativeCoaLedgerRepository extends LedgerRepository
{
    const QUARTER_TO_DATE = 'qtd';
    const MONTHLY         = 'monthly';
    const YEAR_TO_DATE    = 'ytd';
    const DATE_TO_YEAR    = 'dty';

    const MONTHS_QUARTERS_LOOKUP = [
        1  => 1,
        2  => 1,
        3  => 1,
        4  => 2,
        5  => 2,
        6  => 2,
        7  => 3,
        8  => 3,
        9  => 3,
        10 => 4,
        11 => 4,
        12 => 4,
    ];

    const MONTHS_PER_QUARTER = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];

    /** @var array */
    protected $fieldSearchable = [];

    /** @var null|Property */
    public $PropertyObj = null;

    /** @var PropertyRepository|App\Waypoint\Repository|null */
    public $PropertyRepositoryObj = null;

    protected $monthly_actual = null;
    protected $monthly_budget = null;
    protected $monthly_variance = null;
    protected $monthly_percent_variance = null;

    protected $forecast_actual = null;
    protected $forecast_budget = null;
    protected $forecast_variance = null;
    protected $forecast_percent_variance = null;

    protected $quarter_to_date_actual = null;
    protected $quarter_to_date_budget = null;
    protected $quarter_to_date_variance = null;
    protected $quarter_to_date_percent_variance = null;

    protected $year_to_date_actual = null;
    protected $year_to_date_budget = null;
    protected $year_to_date_variance = null;
    protected $year_to_date_percent_variance = null;

    protected $date_to_year_budget = null;

    protected $payload = [];

    protected $result = null;
    protected $monthly_result = null;
    protected $quarter_to_date_result = null;
    protected $year_to_date_result = null;

    protected $date_to_year_result = null;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->PropertyRepositoryObj = App::make(PropertyRepository::class);
    }

    /**
     * @param int $property_id
     * @param array $native_account_codes_array
     * @param Carbon $date
     * @param bool $quarterly
     * @param bool $only_interested_in_monthly
     * @return array
     * @throws \Exception
     */
    public function getLedgerNativeAccounts(int $property_id, array $native_account_codes_array, Carbon $date, bool $quarterly, bool $only_interested_in_monthly = false): array
    {
        if (empty($property_id))
        {
            throw new GeneralException('missing property id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ( ! $this->PropertyObj =
            $this->PropertyRepositoryObj
                ->with('nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->with('nativeCoas.nativeAccounts.nativeCoa')
                ->find($property_id))
        {
            throw new GeneralException('Could not find property from property_id = ' . $property_id . ' ' . __FILE__ . ':' . __LINE__);
        }

        $monthly_results_arr         = collect_waypoint($this->performQueryToStaging($native_account_codes_array, $date, $quarterly, self::MONTHLY));
        $quarter_to_date_results_arr = new Collection();
        if ( ! $only_interested_in_monthly)
        {
            $quarter_to_date_results_arr = collect_waypoint($this->performQueryToStaging($native_account_codes_array, $date, $quarterly, self::QUARTER_TO_DATE));
        }
        $year_to_date_results_arr = new Collection();
        if ( ! $only_interested_in_monthly)
        {
            $year_to_date_results_arr = collect_waypoint($this->performQueryToStaging($native_account_codes_array, $date, $quarterly, self::YEAR_TO_DATE));
        }

        /**
         * deal with December queries
         */
        if ($date->format('M') == 'Dec' || $only_interested_in_monthly)
        {
            $date_to_year_results_arr = new Collection();
        }
        else
        {
            $date_to_year_results_arr =
                collect_waypoint($this->performQueryToStaging(
                    $native_account_codes_array,
                    $date,
                    $quarterly,
                    self::DATE_TO_YEAR)
                );
        }

        $this->payload = [];
        if ($year_to_date_results_arr->count() > 0 || $only_interested_in_monthly)
        {
            foreach ($native_account_codes_array as $native_account_code)
            {
                $this->monthly_result = $monthly_results_arr->filter(function ($item) use ($native_account_code)
                {
                    return $item->native_code == $native_account_code;
                })->first();

                $this->quarter_to_date_result = $quarter_to_date_results_arr->filter(function ($item) use ($native_account_code)
                {
                    return $item->native_code == $native_account_code;
                })->first();

                $this->year_to_date_result = $year_to_date_results_arr->filter(function ($item) use ($native_account_code)
                {
                    return $item->native_code == $native_account_code;
                })->first();

                /**
                 * Deal with December
                 */
                if ($date_to_year_results_arr->count() == 0)
                {
                    $this->date_to_year_result = new Collection();
                }
                else
                {
                    $this->date_to_year_result = $date_to_year_results_arr->filter(function ($item) use ($native_account_code)
                    {
                        return $item->native_code == $native_account_code;
                    })->first();
                }

                if (
                    $this->results_are_empty() ||
                    $this->results_are_zero() ||
                    ( ! $quarterly && $this->monthly_actual_and_budget_are_null())
                )
                {
                    $this->clearCalculatedClassVariables();
                    continue;
                }

                $this->payload[] = [
                    'native_code' => $native_account_code,

                    'monthly_actual'           => $this->getMonthlyActualValue(),
                    'monthly_budgeted'         => $this->getMonthlyBudgetValue(),
                    'monthly_variance'         => $this->getMonthlyVariance(),
                    'monthly_percent_variance' => $this->getMonthlyPercentVariance(),

                    'forecast_actual'           => ( ! $only_interested_in_monthly) ? $this->getForecastActualValue() : null,
                    'forecast_budgeted'         => ( ! $only_interested_in_monthly) ? $this->getForecastBudgetValue() : null,
                    'forecast_variance'         => ( ! $only_interested_in_monthly) ? $this->getForecastVariance() : null,
                    'forecast_percent_variance' => ( ! $only_interested_in_monthly) ? $this->getForecastPercentVariance() : null,

                    'qtd_actual'           => ( ! $only_interested_in_monthly) ? $this->getQuarterToDateActualValue() : null,
                    'qtd_budgeted'         => ( ! $only_interested_in_monthly) ? $this->getQuarterToDateBudgetValue() : null,
                    'qtd_variance'         => ( ! $only_interested_in_monthly) ? $this->getQuarterToDateVariance() : null,
                    'qtd_percent_variance' => ( ! $only_interested_in_monthly) ? $this->getQuarterToDatePercentVariance() : null,

                    'ytd_actual'           => ( ! $only_interested_in_monthly) ? $this->getYearToDateActualValue() : null,
                    'ytd_budgeted'         => ( ! $only_interested_in_monthly) ? $this->getYearToDateBudgetValue() : null,
                    'ytd_variance'         => ( ! $only_interested_in_monthly) ? $this->getYearToDateVariance() : null,
                    'ytd_percent_variance' => ( ! $only_interested_in_monthly) ? $this->getYearToDatePercentVariance() : null,
                ];

                $this->clearCalculatedClassVariables();
            }
        }
        return $this->payload;
    }

    /**
     * @return bool
     */
    protected function results_are_zero()
    {
        return
            isZero($this->getMonthlyActualValue()) &&
            isZero($this->getMonthlyBudgetValue()) &&
            isZero($this->getQuarterToDateActualValue()) &&
            isZero($this->getQuarterToDateBudgetValue()) &&
            isZero($this->getYearToDateActualValue()) &&
            isZero($this->getYearToDateBudgetValue());
    }

    /**
     * @return bool
     */
    protected function monthly_actual_and_budget_are_null()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return
            isset($this->monthly_result) &&
            is_null($this->monthly_result->actual) &&
            is_null($this->monthly_result->budget);
    }

    /**
     * @return bool
     */
    protected function results_are_empty()
    {
        return
            is_null($this->monthly_result) &&
            is_null($this->quarter_to_date_result) &&
            is_null($this->year_to_date_result);
    }

    /**
     * @return array
     * @throws GeneralException
     */
    protected function performQueryToStaging($native_account_codes_array, $date, $quarterly, $period)
    {
        $this->ClientObj             = $this->PropertyObj->client;
        $native_account_codes_string = $this->formatNativeCodesIntoString($native_account_codes_array);
        $property_code_string        = "'" . $this->PropertyObj->property_code . "'";
        $months_string               = $this->formatMonthsString($date, $quarterly, $period);

        $sql = "SELECT 
                            abst.ACCOUNT_CODE_SUB as native_code,
                            SUM(CASE WHEN ab.BUDGET_TYPE = 'actual' THEN aby.ACTUAL_AMOUNT END) actual,
                            SUM(CASE WHEN ab.BUDGET_TYPE = 'budget' THEN aby.ACTUAL_AMOUNT END) budget
                        FROM   ACTUAL_BUDGET ab
                           INNER JOIN ACTUAL_BUDGET_TYPE abt
                                   ON ab.ACTUAL_BUDGET_ID = abt.FK_ACTUAL_BUDGET_ID
                           INNER JOIN ACTUAL_BUDGET_SUB_TYPE abst
                                   ON abt.ACTUAL_BUDGET_TYPE_ID = abst.FK_ACTUAL_BUDGET_TYPE_ID
                           INNER JOIN ACTUAL_BUDGET_YEAR aby
                                   ON abst.ACTUAL_BUDGET_SUB_TYPE_ID =
                                aby.FK_ACTUAL_BUDGET_SUB_TYPE_ID
                        WHERE ab.BUDGET_TYPE IN ( 'actual', 'budget' )
                            AND ab.PROPERTY_CODE IN ( $property_code_string )
                            AND abst.ACCOUNT_CODE_SUB IN ( $native_account_codes_string )
                            AND aby.ACTUAL_MONTH_YEAR IN ( $months_string )
                        GROUP BY abst.ACCOUNT_CODE_SUB
                        ORDER BY native_code;";

        return $this->getStagingDatabaseConnection()
                    ->select(
                        DB::raw($sql                        )
                    );
    }

    protected function clearCalculatedClassVariables()
    {
        $this->monthly_actual           = null;
        $this->monthly_budget           = null;
        $this->monthly_variance         = null;
        $this->monthly_percent_variance = null;

        $this->forecast_actual           = null;
        $this->forecast_budget           = null;
        $this->forecast_variance         = null;
        $this->forecast_percent_variance = null;

        $this->quarter_to_date_actual           = null;
        $this->quarter_to_date_budget           = null;
        $this->quarter_to_date_variance         = null;
        $this->quarter_to_date_percent_variance = null;

        $this->year_to_date_actual           = null;
        $this->year_to_date_budget           = null;
        $this->year_to_date_variance         = null;
        $this->year_to_date_percent_variance = null;

        $this->date_to_year_budget = null;
    }

    /**
     * NOTE NOTE NOTE
     * the next several functions
     * - get?????????????()
     *      Only calls other get?????????????()'s and calculate?????????????()'s
     *      Does not access
     *          $this->year_to_date_result,
     *          $this->yearly_result,
     *          $this->quarter_to_date_result,
     *          $this->??????_result
     *      Does not access $this->getNativeBudgetCoefficient()
     *
     * - calculate?????????????()
     *      Can access $this->getNativeBudgetCoefficient()
     *      Can also access get?????????????()'s
     *      Can access
     *          $this->year_to_date_result,
     *          $this->yearly_result,
     *          $this->quarter_to_date_result,
     *          $this->??????_result
     *
     * @return float|int|null
     */
    protected function getMonthlyVariance()
    {
        if ( ! $this->monthly_variance)
        {
            $this->monthly_variance = $this->getMonthlyActualValue() - $this->getMonthlyBudgetValue();
        }
        return $this->monthly_variance;
    }

    /**
     * @return float|int|null
     */
    protected function getForecastVariance()
    {
        if ( ! $this->forecast_variance)
        {
            $this->forecast_variance = $this->getForecastActualValue() - $this->getForecastBudgetValue();
        }
        return $this->forecast_variance;
    }

    /**
     * @return float|int|null
     */
    protected function getQuarterToDateVariance()
    {
        if ( ! $this->quarter_to_date_variance)
        {
            $this->quarter_to_date_variance = $this->getQuarterToDateActualValue() - $this->getQuarterToDateBudgetValue();
        }
        return $this->quarter_to_date_variance;
    }

    /**
     * @return float|int|null
     */
    protected function getYearToDateVariance()
    {
        if ( ! $this->year_to_date_variance)
        {
            $this->year_to_date_variance = $this->getYearToDateActualValue() - $this->getYearToDateBudgetValue();
        }
        return $this->year_to_date_variance;
    }

    /**
     * @return float|int|null
     */
    protected function getMonthlyBudgetValue()
    {
        if ( ! $this->monthly_budget)
        {
            $this->monthly_budget = $this->calculateMonthlyBudgetValue();
        }
        return $this->monthly_budget;
    }

    /**
     * @return float|int
     */
    protected function calculateMonthlyBudgetValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if (is_null($this->monthly_result) || isZero($this->monthly_result->budget) || is_null($this->monthly_result->budget))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->monthly_result->budget * $this->getNativeBudgetCoefficient($this->monthly_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getForecastBudgetValue()
    {
        if ( ! $this->forecast_budget)
        {
            $this->forecast_budget = (float) ($this->getYearToDateBudgetValue() + $this->getDateToYearBudgetValue());
        }
        return $this->forecast_budget;
    }

    /**
     * @return float|int|null
     */
    protected function getQuarterToDateBudgetValue()
    {
        if ( ! $this->quarter_to_date_budget)
        {
            $this->quarter_to_date_budget = $this->calculateQuarterToDateBudgetValue();
        }
        return $this->quarter_to_date_budget;
    }

    /**
     * @return float|int
     */
    protected function calculateQuarterToDateBudgetValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $this->quarter_to_date_result || isZero($this->quarter_to_date_result->budget) || is_null($this->quarter_to_date_result->budget))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->quarter_to_date_result->budget * $this->getNativeBudgetCoefficient($this->quarter_to_date_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getYearToDateBudgetValue()
    {
        if ( ! $this->year_to_date_budget)
        {
            $this->year_to_date_budget = $this->calculateYearToDateBudgetValue();
        }
        return $this->year_to_date_budget;
    }

    /**
     * @return float|int
     */
    protected function calculateYearToDateBudgetValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $this->year_to_date_result || isZero($this->year_to_date_result->budget) || is_null($this->year_to_date_result->budget))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->year_to_date_result->budget * $this->getNativeBudgetCoefficient($this->year_to_date_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getDateToYearBudgetValue()
    {
        if ( ! $this->date_to_year_budget)
        {
            $this->date_to_year_budget = $this->calculateDateToYearBudgetValue();
        }
        return $this->date_to_year_budget;
    }

    /**
     * @return float|int
     */
    protected function calculateDateToYearBudgetValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! isset($this->date_to_year_result->budget))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        if (isZero($this->date_to_year_result->budget) || is_null($this->date_to_year_result->budget))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->date_to_year_result->budget * $this->getNativeBudgetCoefficient($this->date_to_year_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getMonthlyActualValue()
    {
        if ( ! $this->monthly_actual)
        {
            $this->monthly_actual = $this->calculateMonthlyActualValue();
        }
        return $this->monthly_actual;
    }

    /**
     * @return float|int
     */
    protected function calculateMonthlyActualValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if (is_null($this->monthly_result) || isZero($this->monthly_result->actual) || is_null($this->monthly_result->actual))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->monthly_result->actual * $this->getNativeActualCoefficient($this->monthly_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getForecastActualValue()
    {
        if ( ! $this->forecast_actual)
        {
            $this->forecast_actual =
                isZero($this->getYearToDateActualValue())
                    ? 0
                    : (float) ($this->getYearToDateActualValue() + $this->getDateToYearBudgetValue());
        }
        return $this->forecast_actual;
    }

    /**
     * @return float|int|null
     */
    protected function getQuarterToDateActualValue()
    {
        if ( ! $this->quarter_to_date_actual)
        {
            $this->quarter_to_date_actual = $this->calculateQuarterToDateActualValue();
        }
        return $this->quarter_to_date_actual;
    }

    /**
     * @return float|int
     */
    protected function calculateQuarterToDateActualValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $this->quarter_to_date_result || isZero($this->quarter_to_date_result->actual))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->quarter_to_date_result->actual * $this->getNativeActualCoefficient($this->quarter_to_date_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getYearToDateActualValue()
    {
        if ( ! $this->year_to_date_actual)
        {
            $this->year_to_date_actual = $this->calculateYearToDateActualValue();
        }
        return $this->year_to_date_actual;
    }

    /**
     * @return float|int
     */
    protected function calculateYearToDateActualValue()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $this->year_to_date_result || isZero($this->year_to_date_result->actual))
        {
            return 0;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        return (float) $this->year_to_date_result->actual * $this->getNativeActualCoefficient($this->year_to_date_result->native_code);
    }

    /**
     * @return float|int|null
     */
    protected function getMonthlyPercentVariance()
    {
        if ( ! $this->monthly_percent_variance)
        {
            $this->monthly_percent_variance =
                ( ! $this->getMonthlyBudgetValue() || $this->getMonthlyBudgetValue() == 0)
                    ? 0
                    : 100 * $this->getMonthlyVariance() / $this->getMonthlyBudgetValue();
        }
        return $this->monthly_percent_variance;
    }

    /**
     * @return float|int|null
     */
    protected function getForecastPercentVariance()
    {
        if ( ! $this->forecast_percent_variance)
        {
            $this->forecast_percent_variance = $this->calculateForecastPercentVariance();
        }
        return $this->forecast_percent_variance;
    }

    /**
     * @return float|int
     */
    protected function calculateForecastPercentVariance()
    {
        return ! $this->getForecastBudgetValue()
            ? 0
            : 100 * $this->getForecastVariance() / $this->getForecastBudgetValue();
    }

    /**
     * @return float|int
     */
    protected function getQuarterToDatePercentVariance()
    {
        return ! $this->getQuarterToDateBudgetValue() || $this->getQuarterToDateBudgetValue() == 0
            ? 0
            : 100 * $this->getQuarterToDateVariance() / $this->getQuartertoDateBudgetValue();
    }

    /**
     * @return float|int
     */
    protected function getYearToDatePercentVariance()
    {
        return ! $this->getYearToDateBudgetValue() || $this->getYearToDateBudgetValue() == 0
            ? 0
            : 100 * $this->getYearToDateVariance() / $this->getYearToDateBudgetValue();
    }

    /**
     * @param Carbon $date
     * @return bool
     */
    protected function isLastMonthOfQuarter(Carbon $date)
    {
        return in_array($date->month, [3, 6, 9, 12]);
    }

    /**
     * @param Carbon $original_date
     * @param bool $quarterly
     * @param string $period
     * @return string
     * @throws GeneralException
     */
    protected function formatMonthsString(Carbon $original_date, bool $quarterly, string $period)
    {
        if ($quarterly && ! $this->isLastMonthOfQuarter($original_date))
        {
            throw new GeneralException(
                $original_date->format('F') . ' is not the last month in the quarter, please only create a quarterly reports for the last month of a quarter' . ' ' . __FILE__ . ':' . __LINE__
            );
        }

        Carbon::useMonthsOverflow(false);

        switch ($period)
        {
            case self::MONTHLY:
                return $formatted_months_string = "'" . $original_date->format('M Y') . "'";

            case self::YEAR_TO_DATE:
                $date_incrementor        = $original_date->copy();
                $formatted_months_string = "'" . $date_incrementor->format('M Y') . "'";
                while ($date_incrementor->subMonth()->year == $original_date->year)
                {
                    $formatted_months_string .= ", '" . $date_incrementor->format('M Y') . "'";
                }
                return $formatted_months_string;

            case self::QUARTER_TO_DATE:
                $date_incrementor        = $this->getFirstMonthOfQuarter($original_date);
                $formatted_months_string = '';
                while ($date_incrementor->month != $original_date->month)
                {
                    $formatted_months_string .= "'" . $date_incrementor->format('M Y') . "',";
                    $date_incrementor->addMonth();
                }
                $formatted_months_string .= "'" . $date_incrementor->format('M Y') . "'";
                return $formatted_months_string;

            case self::DATE_TO_YEAR:
                $date_incrementor = $original_date->copy();
                $date_incrementor->addMonth();
                $formatted_months_string = '';
                while ($date_incrementor->year == $original_date->year)
                {
                    if ($formatted_months_string)
                    {
                        $formatted_months_string .= ',';
                    }
                    $formatted_months_string .= "'" . $date_incrementor->format('M Y') . "'";
                    $date_incrementor->addMonth();
                }
                return $formatted_months_string;

            default:
                throw new GeneralException('unrecognized period given = ' . $period . ' ' . __FILE__ . ':' . __LINE__);
        }
    }

    /**
     * @param Carbon $date
     * @return Carbon
     */
    protected function getFirstMonthOfQuarter(Carbon $date)
    {
        return Carbon::create($date->year, current(self::MONTHS_PER_QUARTER[$this->getCurrentQuarter($date)]));
    }

    /**
     * @param Carbon $date
     * @return mixed
     */
    protected function getCurrentQuarter(Carbon $date)
    {
        return self::MONTHS_QUARTERS_LOOKUP[$date->month];
    }

    /**
     * @param array $native_account_codes_array
     * @return string
     */
    protected function formatNativeCodesIntoString(array $native_account_codes_array)
    {
        return "'" . implode("','", $native_account_codes_array) . "'";
    }

    /**
     * @param $native_code
     * @return mixed
     */
    protected function getNativeActualCoefficient($native_code)
    {
        $native_coa_id = $this->PropertyObj->nativeCoas->first()->id;

        if (
            ! isset($this->NativeCoefficientArr['native_coa_' . $native_coa_id])
            ||
            ! $this->NativeCoefficientArr['native_coa_' . $native_coa_id]
        )
        {
            $this->NativeCoefficientArr['native_coa_' . $native_coa_id] = $this->getNativeCoefficientArr();
        }
        return $this->NativeCoefficientArr['native_coa_' . $native_coa_id][$native_code]['actual_coefficient'];
    }

    /** @var null|Collection */
    protected $NativeCoefficientArr = null;

    /**
     * @param $native_code
     * @return mixed
     */
    protected function getNativeCoefficientArr()
    {
        $native_coa_id = $this->PropertyObj->nativeCoas->first()->id;
        $minutes       = config('cache.cache_on', false)
            ? config('cache.cache_tags.Property.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = 'getNativeCoefficientArr_native_coa_id_' . $native_coa_id.'_'.md5(__FILE__.__LINE__);
        $return_me     =
            Cache::tags([
                            'Property_' . $this->PropertyObj->client_id,
                            'AdvancedVariance_' . $this->PropertyObj->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($native_coa_id)
                     {
                         /**
                          * note nemaespace collision on NativeCoa
                          */
                         $NativeCoaObj = App\Waypoint\Models\NativeCoa::with('nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                                                                      ->with('nativeAccounts.nativeCoa')
                                                                      ->find($native_coa_id);
                         $return_me      = $NativeCoaObj
                             ->nativeAccounts
                             ->mapWithKeys(
                                 function (App\Waypoint\Models\NativeAccount $NativeAccountObj)
                                 {
                                     return [
                                         $NativeAccountObj->native_account_code => [
                                             'actual_coefficient'   => $NativeAccountObj->getCoeffients($this->PropertyObj->id)->actual_coefficient,
                                             'budgeted_coefficient' => $NativeAccountObj->getCoeffients($this->PropertyObj->id)->budgeted_coefficient,
                                         ],
                                     ];
                                 }
                             );
                         $return_me      = $return_me->all();
                         return $return_me;

                     }
                 );
        return $return_me;
    }

    /**
     * @param $native_code
     * @return mixed
     */
    protected function getNativeBudgetCoefficient($native_code)
    {
        $native_coa_id = $this->PropertyObj->nativeCoas->first()->id;

        if (
            ! isset($this->NativeCoefficientArr['native_coa_' . $native_coa_id])
            ||
            ! $this->NativeCoefficientArr['native_coa_' . $native_coa_id]
        )
        {
            $this->NativeCoefficientArr['native_coa_' . $native_coa_id] = $this->getNativeCoefficientArr();
        }
        return $this->NativeCoefficientArr['native_coa_' . $native_coa_id][$native_code]['budgeted_coefficient'];
    }

    /**
     * @param $property_id_old_array
     * @return array
     * @throws GeneralException
     */
    public function getNativeCoaData($property_id_old_array)
    {

        if ( ! $this->ReportTemplateObj = App::make(App\Waypoint\Repositories\ReportTemplateRepository::class)->findWhere(
            [
                'client_id'                            => $this->ClientObj->id,
                'is_default_analytics_report_template' => true,
            ]
        )->first())
        {
            throw new GeneralException('We could not find a default report template, please make sure that is set' . ' ' . __FILE__ . ':' . __LINE__, 404);
        }

        if (empty($property_id_old_array))
        {
            return [];
        }

        $payload = [];

        $property_codes_string = "'" . implode("','", $this->getNativePropertyCodesFromOldIds($property_id_old_array)) . "'";

        $results = $this->getStagingDatabaseConnection()->select(
            DB::raw(
                "select 
                        outer_sub.property_code as property_code
                        , outer_sub.account_code as account_code
                        , wbcm.account_name as account_name
                        , wbcm.boma_account_code as boma_account_code
                        , outer_sub.budget_type as budget_type
                        , cast(amount_sign as signed) as sign_change
                        , yr as year
                        , cast(jan as signed) as jan                        
                        , cast(feb as signed) as feb
                        , cast(mar as signed) as mar
                        , cast(apr as signed) as apr
                        , cast(may as signed) as may
                        , cast(jun as signed) as jun
                        , cast(jul as signed) as jul
                        , cast(aug as signed) as aug
                        , cast(sep as signed) as sep
                        , cast(oct as signed) as oct
                        , cast(nov as signed) as nov
                        , cast(`dec` as signed) as `dec`
                    from
                        (
                            select 
                                account_code
                                , budget_type
                                , yr 
                                , property_code
                                , sum(case when mn = 1 then amount else 0 end) jan 
                                , sum(case when mn = 2 then amount else 0 end) feb 
                                , sum(case when mn = 3 then amount else 0 end) mar 
                                , sum(case when mn = 4 then amount else 0 end) apr 
                                , sum(case when mn = 5 then amount else 0 end) may 
                                , sum(case when mn = 6 then amount else 0 end) jun 
                                , sum(case when mn = 7 then amount else 0 end) jul 
                                , sum(case when mn = 8 then amount else 0 end) aug 
                                , sum(case when mn = 9 then amount else 0 end) sep 
                                , sum(case when mn = 10 then amount else 0 end) oct 
                                , sum(case when mn = 11 then amount else 0 end) nov 
                                , sum(case when mn = 12 then amount else 0 end) `dec`
                            from 
                                (
                                    select 
                                        abst.ACCOUNT_CODE_SUB as account_code
                                        , ab.PROPERTY_CODE as property_code 
                                        , ab.BUDGET_TYPE as budget_type
                                        , year(STR_TO_DATE(CONCAT('1 ', aby.ACTUAL_MONTH_YEAR), '%d %b %Y')) yr 
                                        , month(STR_TO_DATE(CONCAT('1 ', aby.ACTUAL_MONTH_YEAR), '%d %b %Y')) mn 
                                        , aby.ACTUAL_AMOUNT as amount
                                    from
                                        ACTUAL_BUDGET ab
                                        inner join ACTUAL_BUDGET_TYPE abt on ab.ACTUAL_BUDGET_ID = abt.FK_ACTUAL_BUDGET_ID
                                        inner join ACTUAL_BUDGET_SUB_TYPE abst on abt.ACTUAL_BUDGET_TYPE_ID = abst.FK_ACTUAL_BUDGET_TYPE_ID
                                        inner join ACTUAL_BUDGET_YEAR aby on abst.ACTUAL_BUDGET_SUB_TYPE_ID = aby.FK_ACTUAL_BUDGET_SUB_TYPE_ID
                                    
                                    where
                                        ab.BUDGET_TYPE in ('actual','budget') and 
                                        ab.PROPERTY_CODE in ($property_codes_string)
                                ) inner_sub
                            GROUP BY
                                account_code
                                , budget_type
                                , yr
                                , property_code
                        ) outer_sub
                        inner join WAYPOINT_BOMA_COA_MAPPING wbcm on wbcm.account_code = outer_sub.account_code
                    group by
                        property_code
                        , account_code
                        , account_name
                        , budget_type
                        , sign_change
                        , year
                    ;"
            ), [
                'property_codes_string' => $property_codes_string,
            ]
        );

        if ( ! empty($results))
        {
            foreach ($results as $result)
            {
                /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
                $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);

                if ( ! $ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupRepositoryObj->findWhere(
                    [
                        'report_template_id'       => $this->ReportTemplateObj->id,
                        'deprecated_waypoint_code' => $result->boma_account_code,
                    ]
                )->first()
                )
                {
                    // TODO - @Alex to more practically handle a missing account code
                    continue;
                }

                $payload_item_array = [
                    'PropId'           => $result->property_code,
                    'Account Code'     => $result->account_code,
                    'Account Name'     => $result->account_name,
                    'Waypoint Account' => $ReportTemplateAccountGroupObj->display_name,
                    'Waypoint Tree'    => $this->getLineageFormatted($ReportTemplateAccountGroupObj),
                    'Sign Change'      => $result->sign_change,
                    'Year'             => $result->year,
                    'Jan'              => (float) $result->jan,
                    'Feb'              => (float) $result->feb,
                    'Mar'              => (float) $result->mar,
                    'Apr'              => (float) $result->apr,
                    'May'              => (float) $result->may,
                    'Jun'              => (float) $result->jun,
                    'Jul'              => (float) $result->jul,
                    'Aug'              => (float) $result->aug,
                    'Sep'              => (float) $result->sep,
                    'Oct'              => (float) $result->oct,
                    'Nov'              => (float) $result->nov,
                    'Dec'              => (float) $result->dec,
                ];

                $payload[$result->budget_type][] = $payload_item_array;
            }
        }

        $payload['occupancy'] = $this->getOccupancyDataFromStaging($property_id_old_array);

        return $payload;
    }

    /**
     * @param $property_id_old_array
     * @return array
     */
    private function getOccupancyDataFromStaging($property_id_old_array)
    {
        if ( ! $property_id_old_array || empty($property_id_old_array))
        {
            throw new GeneralException('cannot get occupancy from staging as old property ids are missing' . ' ' . __FILE__ . ':' . __LINE__);
        }

        $property_id_old_string = implode(',', $property_id_old_array);

        $this->getStagingDatabaseConnection()->enableQueryLog();

        $results = $this->getStagingDatabaseConnection()->select(
            DB::raw(
                "select 
                        fk_property_id as property_id_old,
                        'Total Rentable' as Description,
                        year as `Year`,
                        sum(JAN) as Jan,
                        sum(FEB) as Feb,
                        sum(MAR) as Mar,
                        sum(APR) as Apr,
                        sum(MAY) as May,
                        sum(JUN) as Jun,
                        sum(JUL) as Jul,
                        sum(AUG) as Aug,
                        sum(SEP) as Sep,
                        sum(OCT) as Oct,
                        sum(NOV) as Nov,
                        sum(`DEC`) as `Dec`
                    from
                        (
                            select
                                fk_property_id,
                                from_year as year,
                                case when from_month = \"JAN\" then rentable_area end as JAN,
                                case when from_month = \"FEB\" then rentable_area end as FEB,
                                case when from_month = \"MAR\" then rentable_area end as MAR,
                                case when from_month = \"APR\" then rentable_area end as APR,
                                case when from_month = \"MAY\" then rentable_area end as MAY,
                                case when from_month = \"JUN\" then rentable_area end as JUN,
                                case when from_month = \"JUL\" then rentable_area end as JUL,
                                case when from_month = \"AUG\" then rentable_area end as AUG,
                                case when from_month = \"SEP\" then rentable_area end as SEP,
                                case when from_month = \"OCT\" then rentable_area end as OCT,
                                case when from_month = \"NOV\" then rentable_area end as NOV,
                                case when from_month = \"DEC\" then rentable_area end as `DEC`
                            from
                                OCCUPANCY_MONTH
                            where
                                fk_property_id in ($property_id_old_string)
                        ) rentable 
                    group by `Year`
                    
                    UNION
                    
                    select 
                        fk_property_id as property_id_old,
                        'Total Occupied' as Description,
                        year as `Year`,
                        sum(JAN) as Jan,
                        sum(FEB) as Feb,
                        sum(MAR) as Mar,
                        sum(APR) as Apr,
                        sum(MAY) as May,
                        sum(JUN) as Jun,
                        sum(JUL) as Jul,
                        sum(AUG) as Aug,
                        sum(SEP) as Sep,
                        sum(OCT) as Oct,
                        sum(NOV) as Nov,
                        sum(`DEC`) as `Dec`
                    from
                        (
                            select
                                fk_property_id,
                                from_year as year,
                                case when from_month = \"JAN\" then occupied_area end as JAN,
                                case when from_month = \"FEB\" then occupied_area end as FEB,
                                case when from_month = \"MAR\" then occupied_area end as MAR,
                                case when from_month = \"APR\" then occupied_area end as APR,
                                case when from_month = \"MAY\" then occupied_area end as MAY,
                                case when from_month = \"JUN\" then occupied_area end as JUN,
                                case when from_month = \"JUL\" then occupied_area end as JUL,
                                case when from_month = \"AUG\" then occupied_area end as AUG,
                                case when from_month = \"SEP\" then occupied_area end as SEP,
                                case when from_month = \"OCT\" then occupied_area end as OCT,
                                case when from_month = \"NOV\" then occupied_area end as NOV,
                                case when from_month = \"DEC\" then occupied_area end as `DEC`
                            from
                                OCCUPANCY_MONTH
                            where
                                fk_property_id in ($property_id_old_string)
                        ) occupied 
                    group by `Year`;"
            ),
            [
                'property_id_old_string' => $property_id_old_string,
            ]
        );

        if (count($results) > 0)
        {
            $property_code_array = $this->getNativePropertyCodesFromOldIds($property_id_old_array, true);

            $payload = [];
            foreach ($results as $result)
            {
                $result        = (array) $result;
                $result['Jan'] = (float) $result['Jan'];
                $result['Feb'] = (float) $result['Feb'];
                $result['Mar'] = (float) $result['Mar'];
                $result['Apr'] = (float) $result['Apr'];
                $result['May'] = (float) $result['May'];
                $result['Jun'] = (float) $result['Jun'];
                $result['Jul'] = (float) $result['Jul'];
                $result['Aug'] = (float) $result['Aug'];
                $result['Sep'] = (float) $result['Sep'];
                $result['Oct'] = (float) $result['Oct'];
                $result['Nov'] = (float) $result['Nov'];
                $result['Dec'] = (float) $result['Dec'];

                $result = array_merge(['PropId' => $property_code_array[$result['property_id_old']]], $result);
                unset($result['property_id_old']);

                if ( ! (empty($result['Jan']) && empty($result['Feb']) && empty($result['Mar']) && empty($result['Apr']) && empty($result['May']) && empty($result['Jun']) && empty($result['Jul']) && empty($result['Aug']) && empty($result['Sep']) && empty($result['Oct']) && empty($result['Nov']) && empty($result['Dec'])))
                {
                    $payload[] = $result;
                }
            }
            return $payload;
        }
        else
        {
            return [];
        }
    }

    /**
     * @param $ReportTemplateAccountGroupObj
     * @return string
     */
    public function getLineageFormatted($ReportTemplateAccountGroupObj)
    {
        // get family line of boma codes for the target code
        $lineage[] = $ReportTemplateAccountGroupObj->display_name;
        while ($ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent)
        {
            if (
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->deprecated_waypoint_code != LedgerController::OPERATING_EXPENSES_CEILING_CODE &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->id != ReportTemplate::BOMA_ROOT_CODE_ID
            )
            {
                $lineage[] = $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->display_name;
            }
            $ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent;
        }

        return implode(' > ', array_reverse($lineage));
    }

    /**
     * @param $property_id_old_array
     * @param bool $lookup_format book
     * @return array
     */
    private function getNativePropertyCodesFromOldIds($property_id_old_array, $lookup_format = false)
    {
        if ($lookup_format)
        {
            /**
             * @TODO - may need to update this to accommodate multiple property code mapping to one FK_PROPERTY_ID
             */
            $results = $this->getStagingDatabaseConnection()
                            ->table('WAYPOINT_ACCOUNT_CODES')
                            ->whereIn('FK_PROPERTY_ID', $property_id_old_array)
                            ->distinct()
                            ->select(
                                'FK_PROPERTY_ID',
                                'PROPERTY_CODE'
                            )
                            ->get();

            if ($results->count() > 0)
            {
                $property_id_old_and_property_code_array = [];
                foreach ($results as $result)
                {
                    $property_id_old_and_property_code_array[$result->FK_PROPERTY_ID] = $result->PROPERTY_CODE;
                }
                return $property_id_old_and_property_code_array;
            }
            else
            {
                return [];
            }
        }
        else
        {
            $result = $this->getStagingDatabaseConnection()
                           ->table('WAYPOINT_ACCOUNT_CODES')
                           ->whereIn('FK_PROPERTY_ID', $property_id_old_array)
                           ->distinct()
                           ->pluck('PROPERTY_CODE');

            return $result->toArray();
        }
    }

    /**
     * @param $enable_query_log bool
     * @return \Illuminate\Database\Connection|null
     */
    public function getStagingDatabaseConnection(bool $enable_query_log = false)
    {
        if ( ! $this->ClientObj)
        {
            throw new GeneralException('unusable client object' . ' ' . __FILE__ . ':' . __LINE__);
        }
        if ($this->StagingDatabaseConnectionObj)
        {
            if ($enable_query_log)
            {
                $this->StagingDatabaseConnectionObj->enableQueryLog();
                return $this->StagingDatabaseConnectionObj;
            }
            return $this->StagingDatabaseConnectionObj;
        }
        return $this->setStagingDatabaseConnection($enable_query_log);
    }

    /**
     * @param $enable_query_log bool
     * @return \Illuminate\Database\Connection|null
     */
    protected function setStagingDatabaseConnection(bool $enable_query_log = false)
    {
        if ( ! $this->ClientObj)
        {
            throw new GeneralException('unusable client object' . ' ' . __FILE__ . ':' . __LINE__);
        }

        try
        {
            $this->StagingDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_STAGING_FOR_CLIENT_' . $this->ClientObj->client_id_old);
            $this->StagingDatabaseConnectionObj->getPdo();
            if ($enable_query_log)
            {
                $this->StagingDatabaseConnectionObj->enableQueryLog();
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('could not find staging database - client_id_old = ' . $this->ClientObj->client_id_old . ' ' . __FILE__ . ':' . __LINE__);
        }
        return $this->StagingDatabaseConnectionObj;
    }

    /**
     * @return mixed
     **/
    public function model()
    {
        return NativeCoa::class;
    }
}
