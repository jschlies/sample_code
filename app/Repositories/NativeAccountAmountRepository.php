<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Model;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\Ledger\NativeCoaLedgerRepository;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use Cache;
use Carbon\Carbon;
use Exception;
use FormulaInterpreter\Compiler as FormulaInterpreterCompiler;

/**
 * Class NativeAccountAmountRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountAmountRepository extends AccessListRepository
{
    /** @var  NativeCoaLedgerRepository|NativeCoaLedgerMockRepository */
    private static $NativeCoaLedgerRepositoryObj;

    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountAmount::class;
    }

    public function processRefreshNativeAccountValues($client_id, $property_id_arr, $from_month, $from_year, $to_month, $to_year, $allow_overwrite)
    {
        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        /** @var NativeCoaLedgerRepository $NativeCoaLedgerRepositoryObj */
        $NativeCoaLedgerRepositoryObj = App::make(NativeCoaLedgerRepository::class);
        /** @var NativeAccountAmountRepository $NativeAccountAmountRepositoryObj */
        $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);

        if ( ! $BenchmarkGenerationDateObj = LedgerController::getClientAsOfDate(Client::find($client_id)))
        {
            $BenchmarkGenerationDateObj = Carbon::now();
        }

        $NativeCoaLedgerRepositoryObj->ClientObj = Client::find($client_id);

        if ( ! $property_id_arr)
        {
            if ( ! $PropertyObjArr =
                $PropertyRepositoryObj
                    ->with('client')
                    ->with('nativeCoas.nativeAccounts')
                    ->findWhere(
                        [
                            'client_id' => $client_id,
                        ]
                    )
            )
            {
                throw new GeneralException("no properties found", 500);
            }

        }
        else
        {
            if ( ! $PropertyObjArr =
                $PropertyRepositoryObj
                    ->with('client')
                    ->with('nativeCoas.nativeAccounts')
                    ->findWhereIn('id', $property_id_arr))
            {
                throw new GeneralException("no properties found", 500);
            }
        }

        $FromDateObj = Carbon::create($from_year, $from_month, 1, 0, 0, 0)->modify('first day of this month');
        $ToDateObj   = Carbon::create($to_year, $to_month, 1, 0, 0, 0)->modify('last day of this month');
        /** @var App\Waypoint\Models\Property $PropertyObj */
        foreach ($PropertyObjArr as $PropertyObj)
        {
            /**
             * handy for debugging
             */
            //$check_for_existing = $NativeAccountAmountRepositoryObj->findWhere(
            //    [
            //        "client_id"            => $PropertyObj->client_id,
            //        "property_id"          => $PropertyObj->id,
            //    ]
            //);
            //if ($check_for_existing->count())
            //{
            //    continue;
            //}
            $LoopDateObj     = clone $FromDateObj;
            $bulk_update_arr = [];

            $native_account_codes_array = $PropertyObj->nativeCoas->first()->nativeAccounts->pluck('native_account_code')->toArray();

            while ($LoopDateObj->lessThanOrEqualTo($ToDateObj))
            {
                if ($LoopDateObj->greaterThan($BenchmarkGenerationDateObj))
                {
                    break;
                }
                if ($allow_overwrite)
                {
                    $NativeAccountAmountRepositoryObj->deleteWhere(
                        [
                            "client_id"            => $PropertyObj->client_id,
                            "property_id"          => $PropertyObj->id,
                            "month_year_timestamp" => $LoopDateObj->format('Y-m-d H:i:s'),
                        ]
                    );
                }
                $check_for_existing = $NativeAccountAmountRepositoryObj->findWhere(
                    [
                        "client_id"            => $PropertyObj->client_id,
                        "property_id"          => $PropertyObj->id,
                        "month_year_timestamp" => $LoopDateObj->format('Y-m-d H:i:s'),
                    ]
                );
                if ($check_for_existing->count())
                {
                    $LoopDateObj->addMonth();
                    echo 'NativeAccountAmount Data for property id=' . $PropertyObj->id . ' for month of ' .
                         $LoopDateObj->format('Y-m-d H:i:s') . ' is already in NativeAccountAmount table ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
                    continue;
                }

                $NativeCoaLedgerRepositoryObj->PropertyObj = $PropertyObj;

                $as_of_month = $LoopDateObj->format('M');
                $as_of_year  = $LoopDateObj->format('Y');

                $minutes    = config('cache.cache_on', false)
                    ? config('cache.cache_tags.ProcessRefreshNativeAccountValues.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
                    :
                    0;
                $key = "processRefreshNativeAccountValues_property_id=" . $PropertyObj->id . '_as_of_month=' . $as_of_month . '_as_of_year' . $as_of_year . '_quarterly_' . md5(json_encode($native_account_codes_array)).'_'.md5(__FILE__.__LINE__);
                $payloadArr =
                    Cache::tags([
                                    'ProcessRefreshNativeAccountValues' . $client_id,
                                    'Non-Session',
                                ])
                         ->remember(
                             $key,
                             $minutes,
                             function () use ($NativeCoaLedgerRepositoryObj, $native_account_codes_array, $PropertyObj, $as_of_month, $as_of_year, $LoopDateObj)
                             {
                                 try
                                 {
                                     return $NativeCoaLedgerRepositoryObj->getLedgerNativeAccounts($PropertyObj->id, $native_account_codes_array, $LoopDateObj, false, true);
                                 }
                                 catch (GeneralException $e)
                                 {
                                     throw $e;
                                 }
                                 catch (Exception $e)
                                 {
                                     throw new GeneralException('Call to NativeCoaLedgerRepository failed - property_id = ' . $PropertyObj->id, 500, $e);
                                 }
                             }
                         );

                if ($payloadArr)
                {
                    echo 'NativeAccountAmount Data for property id=' . $PropertyObj->id . '  month of ' .
                         $LoopDateObj->format('Y-m-d H:i:s') . ' exists at ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
                }
                else
                {
                    echo 'NativeAccountAmount Data for property id=' . $PropertyObj->id . '  month of ' .
                         $LoopDateObj->format('Y-m-d H:i:s') . ' does not exist at ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
                }
                foreach ($payloadArr as $payload)
                {
                    if ( ! $NativeAccountObj = $PropertyObj->nativeCoas->first()->nativeAccounts->where('native_account_code', $payload['native_code'])->first())
                    {
                        throw new GeneralException('Unknown native_code at ' . __FILE__ . ':' . __LINE__);
                    }

                    $bulk_update_arr [] =
                        [
                            "client_id"            => $PropertyObj->client_id,
                            "property_id"          => $PropertyObj->id,
                            "native_account_id"    => $NativeAccountObj->id,
                            "month"                => $LoopDateObj->format('m'),
                            "year"                 => $LoopDateObj->format('Y'),
                            "month_year_timestamp" => $LoopDateObj->format('Y-m-d H:i:s'),
                            "actual"               => $payload['monthly_actual'],
                            "budget"               => $payload['monthly_budgeted'],
                            "created_at"           => Carbon::now()->format('Y-m-d H:i:s'),
                            "updated_at"           => Carbon::now()->format('Y-m-d H:i:s'),
                        ];
                }
                echo 'Processing for property id=' . $PropertyObj->id . ' for month of ' . $LoopDateObj->format('Y-m-d H:i:s') . ' at ' . Carbon::now()
                                                                                                                                                ->format('Y-m-d H:i:s') . PHP_EOL;
                $LoopDateObj->addMonth();

                if ($bulk_update_arr)
                {
                    NativeAccountAmount::insert($bulk_update_arr);
                    $bulk_update_arr = [];
                }
            }

        }
    }

    /**
     * @return NativeCoaLedgerRepository|NativeCoaLedgerMockRepository
     */
    public static function getNativeCoaLedgerRepositoryObj()
    {
        if ( ! self::$NativeCoaLedgerRepositoryObj)
        {
            self::$NativeCoaLedgerRepositoryObj = App::make(NativeCoaLedgerRepository::class);
        }
        return self::$NativeCoaLedgerRepositoryObj;
    }

    /**
     * @param NativeCoaLedgerRepository|NativeCoaLedgerMockRepository $NativeCoaLedgerRepositoryObj
     */
    public static function setNativeCoaLedgerRepositoryObj($NativeCoaLedgerRepositoryObj)
    {
        self::$NativeCoaLedgerRepositoryObj = $NativeCoaLedgerRepositoryObj;
    }

    /**
     * HEY!!!!!!!!!
     * For this to work well, elements of $PropertyObjArr must by hydrated with
     * nativeAccountAmountsFiltered. if not, this may
     * last a LONG time
     *
     * @param Collection $PropertyObjArr
     * @param array $native_account_id_arr
     * @return Collection|\Illuminate\Support\Collection
     */
    function rankAndScorePropertiesByActualForRTAG(Collection $PropertyObjArr, $native_account_id_arr = [], Carbon $FromDateObj = null, Carbon $ToDateObj = null)
    {
        if ( ! $FromDateObj)
        {
            $FromDateObj = Carbon::create(1900, 1, 1);
        }
        if ( ! $ToDateObj)
        {
            $ToDateObj = Carbon::create(2200, 12, 31);
        }
        $return_me =
            $PropertyObjArr
                ->map(
                    function (Property $PropertyObj) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                    {
                        $score                = [];
                        $score['property_id'] = $PropertyObj->id;
                        if ($native_account_id_arr)
                        {
                            $score['total_actual'] =
                                $PropertyObj
                                    ->nativeAccountAmountsFiltered
                                    ->filter(
                                        function (NativeAccountAmount $NativeAccountAmountsFiltered) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                                        {
                                            return
                                                in_array($NativeAccountAmountsFiltered->native_account_id, $native_account_id_arr) &&
                                                $NativeAccountAmountsFiltered->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                                $NativeAccountAmountsFiltered->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                        }
                                    )->sum('actual');
                        }
                        else
                        {
                            $score['total_actual'] =
                                $PropertyObj
                                    ->nativeAccountAmountsFiltered
                                    ->filter(
                                        function (NativeAccountAmount $NativeAccountAmountsFiltered) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                                        {
                                            return
                                                in_array($NativeAccountAmountsFiltered->native_account_id, $native_account_id_arr) &&
                                                $NativeAccountAmountsFiltered->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                                $NativeAccountAmountsFiltered->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                        }
                                    )
                                    ->sum('actual');
                        }

                        return $score;
                    }
                )
                ->sortByDesc('total_actual')
                ->zip(range(1, $PropertyObjArr->count()))
                ->map(
                    function ($scoreAndRank)
                    {
                        list($score, $rank) = $scoreAndRank;
                        return array_merge($score, [
                                                     'rank' => $rank,
                                                 ]
                        );
                    }
                )
                ->groupBy('score')
                ->collapse()
                ->sortBy('rank');

        return $return_me;
    }

    /**
     * Property ranking based on calculated fields
     *
     * @param Collection $PropertyObjArr This must pre-load nativeAccountAmountsFiltered to work properly
     * @param $CalculatedFieldObj
     * @param Carbon|null $FromDateObj
     * @param Carbon|null $ToDateObj
     * @return Collection|\Illuminate\Support\Collection
     */
    function rankAndScorePropertyByActualForCalculatedFields(Collection $PropertyObjArr, $CalculatedFieldObj, Carbon $FromDateObj = null, Carbon $ToDateObj = null)
    {
        if ( ! $FromDateObj)
        {
            $FromDateObj = Carbon::create(1900, 1, 1);
        }
        if ( ! $ToDateObj)
        {
            $ToDateObj = Carbon::create(2200, 12, 31);
        }

        /** @var  $FormulaInterpreterCompiler */
        $FormulaInterpreterCompiler = new FormulaInterpreterCompiler();

        $return_me = $PropertyObjArr
            ->map(
                function (Property $PropertyObj) use ($CalculatedFieldObj, $FromDateObj, $ToDateObj, $FormulaInterpreterCompiler)
                {
                    $score                = [];
                    $score['property_id'] = $PropertyObj->id;

                    if ( ! $CalculatedFieldEquationObj = $CalculatedFieldObj->calculatedFieldEquationsForProperty($PropertyObj->id))
                    {
                        /**
                         * this grabs the client default
                         */
                        $CalculatedFieldEquationObj =
                            $CalculatedFieldObj->calculatedFieldEquations
                                ->filter(
                                    function ($CalculatedFieldEquationObj)
                                    {
                                        return $CalculatedFieldEquationObj->properties->count() == 0;
                                    }
                                )->first();
                    }

                    $FormulaInterpreterExecutable = $FormulaInterpreterCompiler->compile($CalculatedFieldEquationObj->equation_string_parsed);

                    foreach ($CalculatedFieldEquationObj->calculatedFieldVariables as $CalculatedFieldVariableObj)
                    {
                        if ($CalculatedFieldVariableObj->native_account_id)
                        {
                            $native_account_id_arr = $CalculatedFieldVariableObj->native_account_id;
                            /** @var Collection $LocalNativeAccountAmountObjArr */
                            $LocalNativeAccountAmountObjArr = $PropertyObj->nativeAccountAmountsFiltered->filter(
                                function (NativeAccountAmount $NativeAccountAmountObj) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                                {
                                    return
                                        in_array($NativeAccountAmountObj->native_account_id, $native_account_id_arr) &&
                                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                }
                            );

                            $actual_variable_element_arr['NA_' . $CalculatedFieldVariableObj->native_account_id] = $LocalNativeAccountAmountObjArr->sum('actual');

                        }
                        elseif ($CalculatedFieldVariableObj->report_template_account_group_id)
                        {
                            $native_account_id_arr         = $CalculatedFieldVariableObj->reportTemplateAccountGroup->get_native_account_id_arr();
                            $LocalNativeAcountAmountObjArr =
                                $PropertyObj
                                    ->nativeAccountAmountsFiltered
                                    ->filter(
                                        function (NativeAccountAmount $NativeAccountAmountsFilteredObj) use ($FromDateObj, $ToDateObj, $native_account_id_arr)
                                        {
                                            return in_array($NativeAccountAmountsFilteredObj->native_account_id, $native_account_id_arr) &&
                                                   $NativeAccountAmountsFilteredObj->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                                   $NativeAccountAmountsFilteredObj->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                        }
                                    );

                            $actual_variable_element_arr['RTAG_' . $CalculatedFieldVariableObj->report_template_account_group_id] =
                                $LocalNativeAcountAmountObjArr->sum('actual');
                        }
                        else
                        {
                            throw new GeneralException('Invalid equation in ' . self::class . $CalculatedFieldEquationObj->equation_string . ' ' . __FILE__ . ':' . __LINE__);
                        }
                    }

                    $score['total_actual'] = $FormulaInterpreterExecutable->run($actual_variable_element_arr);
                    return $score;
                }
            )->sortByDesc('total_actual')
            ->zip(range(1, $PropertyObjArr->count()))
            ->map(
                function ($scoreAndRank)
                {
                    list($score, $rank) = $scoreAndRank;
                    return array_merge($score, [
                                                 'rank' => $rank,
                                             ]
                    );
                }
            )
            ->groupBy('score')
            ->collapse()
            ->sortBy('rank');

        return $return_me;
    }

    /**
     * $PropertyObjArr should contain the property(s) in question and (if $rank_switch) all properties to be ranked against
     * $rank_switch true/false if rank of $property_id within $PropertyObjArr is needed
     * $property_id - the property in question
     *
     * We do this (both the procrssing for property in question and for the population we're ranking) to save memory -
     * ->with('nativeAccountAmountsFiltered') has the potential to blow things up so be careful
     *
     * @param Collection $PropertyObjArr
     * @param bool $rank_switch
     * @param integer|null $property_id
     * @return array
     */
    public function processTotalsForPropertyArrActualBudget(
        Collection $PropertyObjArr,
        array $property_id_arr,
        array $native_account_id_arr,
        Carbon $FromDateObj,
        Carbon $ToDateObj
    ) {
        if ( ! $FromDateObj)
        {
            $FromDateObj = Carbon::create(1900, 1, 1);
        }
        if ( ! $ToDateObj)
        {
            $ToDateObj = Carbon::create(2200, 12, 31);
        }

        $NativeAccountAmountObjArr =
            $PropertyObjArr
                ->filter(
                    function (Property $PropertyObj) use ($property_id_arr)
                    {
                        return in_array($PropertyObj->id, $property_id_arr);
                    }
                )
                ->map(
                    function (Property $PropertyObj) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                    {
                        return
                            $PropertyObj
                                ->nativeAccountAmountsFiltered
                                ->filter(
                                    function (NativeAccountAmount $NativeAccountAmountObj) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                                    {
                                        return
                                            in_array($NativeAccountAmountObj->native_account_id, $native_account_id_arr) &&
                                            $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                            $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                    }
                                );
                    }
                )
                ->flatten();

        $total_budget    = $NativeAccountAmountObjArr->sum('budget');
        $total_actual    = $NativeAccountAmountObjArr->sum('actual');
        $ActualToDateObj = null;
        if ($NativeAccountAmountObjArr->count())
        {
            $ActualToDateObj = $NativeAccountAmountObjArr->max('month_year_timestamp');
        }

        return [$total_budget, $total_actual, $ActualToDateObj];
    }
}
