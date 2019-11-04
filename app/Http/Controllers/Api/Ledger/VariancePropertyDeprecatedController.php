<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Ledger\VarianceProperty;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use App\Waypoint\Repositories\Ledger\VarianceRepository;

/**
 * Class VariancePropertyController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class VariancePropertyDeprecatedController extends LedgerController
{
    /** @var  VarianceRepository */
    private $VarianceRepository;

    /** @var string */
    public $apiTitle = 'VarianceProperty';

    /** @var string */
    public $apiDisplayName = 'Budget Variance';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'              => 'Expense Name',
        'actual'            => 'Actual Amount ($/sq ft)',
        'budget'            => 'Budget Amount ($/sq ft)',
        'actualGrossAmount' => 'Actual Gross Amount',
        'budgetGrossAmount' => 'Budget Gross Amount',
    ];

    /**
     * @var array
     */
    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '"$"0.00',
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /**
     * VariancePropertyController constructor.
     * @param VarianceRepository $VarianceRepo
     */
    public function __construct(VarianceRepository $VarianceRepo)
    {
        $this->VarianceRepository = $VarianceRepo;
        parent::__construct($VarianceRepo);
    }

    /**
     * @param integer $property_id
     * @param integer $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @param bool $suppressResponse
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index(
        $property_id,
        $year,
        $period,
        $area,
        $report_template_account_group_id,
        $suppressResponse = false
    ) {
        $this->initInputForCombinedSpreadsheets(
            [
                'year'   => $year,
                'period' => $period,
                'area'   => $area,
            ],
            $this->VarianceRepository
        );
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        $this->entityName         = $this->PropertyObj->display_name;
        $this->occupancy          = $this->VarianceRepository->getOccupancyForSingleProperty($this->PropertyObj->property_id_old, $this->year);
        $this->targetPayloadSlice = $this->getDefaultTargetPayloadSlice();

        if ($this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj))
        {
            $results = $this->perform_query();
            $this->package_data($results);
        }
        else
        {
            $this->warnings[] = 'no data for this time period';
        }

        if ($suppressResponse)
        {
            return [
                'data'            => $this->payload->toArray(),
                'metadata'        => $this->getMetadata(),
                'transformations' => $this->getSpreadsheetFields(),
            ];
        }
        else
        {
            return $this->sendResponse(
                $this->payload->toArray(),
                'variance benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @param $results
     * @throws GeneralException
     */
    protected function package_data($results)
    {
        /**
         * because $results is normally (ie out of the box Lavaral) a
         * Illuminate\Suoprt|Collection rather than a
         * Illuminate\Database\Eloquent\Collection like almost everywhere else in
         * Laravel, methods in Illuminate\Supprt|Collection that are overridden in
         * Illuminate\Database\Eloquent\Collection should not be used.
         *
         * @todo fix me Mothods in App\Waypoint\Collection (which extends
         * Illuminate\Database\Eloquent\Collection which in turn
         * extends Illuminate\Suoprt|Collection). Need to find the methods
         * in Illuminate\Database\Eloquent\Collection that override a method
         * in Illuminate\Supprt|Collection. Then override or fix these overrides
         * in App\Waypoint\Collection
         *
         * OR
         * update DatabaseConnectionRepository::getLedgerDatabaseConnection() to emit
         * App\Waypoint\Collection's and test
         */
        if ($results->count() > 0)
        {
            /**
             * can't use ->unique() or ->pluck(). Aee above. For now do things the old
             * fashioned way
             */
            $code_arr = [];
            foreach ($results as $result)
            {
                $code_arr[] = $result->code;
            }
            $code_arr = array_unique($code_arr);
            foreach ($code_arr as $code)
            {
                if (
                    $code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code ||
                    in_array($code, $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes())
                )
                {
                    // filter to get actual/budget of the particular child
                    $filteredResults = $results->filter(
                        function ($item) use ($code)
                        {
                            return $item->code === $code;
                        }
                    )->sortBy('type');

                    if ($filteredResults->count() == 2 && $this->bothNotNull($filteredResults))
                    {
                        if (isset($this->ReportTemplateAccountGroupObj->getGrandChildrenDeprecatedCoaCodes(true)[$code]))
                        {
                            $grandChildrenCount       = 0;
                            $processedGrandChildCodes = [];

                            foreach (
                                array_intersect(
                                    $code_arr,
                                    $this->ReportTemplateAccountGroupObj->getGrandChildrenDeprecatedCoaCodes(true)[$code]
                                )
                                as $grandChildCoaCode)
                            {
                                $filtereGrandChildrenResults = $results->filter(
                                    function ($item) use ($grandChildCoaCode, $processedGrandChildCodes)
                                    {
                                        return $item->code === $grandChildCoaCode && ! in_array($grandChildCoaCode, $processedGrandChildCodes);
                                    }
                                )->all();
                                if (count($filtereGrandChildrenResults) == 2)
                                {
                                    $grandChildrenCount++;
                                    $processedGrandChildCodes[] = $grandChildCoaCode;
                                }
                            }
                        }
                        else
                        {
                            $childCoaCodeCount   = 0;
                            $processedChildCodes = [];
                            foreach ($this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes() as $childCoaCode)
                            {
                                $filtereChildrenResults = $results
                                    ->filter(
                                        function ($item) use ($childCoaCode, $processedChildCodes)
                                        {
                                            return $item->code === $childCoaCode && ! in_array($childCoaCode, $processedChildCodes);
                                        }
                                    )
                                    ->all();

                                if (count($filtereChildrenResults) == 2)
                                {
                                    $childCoaCodeCount++;
                                    $processedChildCodes[] = $childCoaCode;
                                }
                            }
                        }

                        $combinedResult = [
                            'LedgerController'                 => $this,
                            'id'                               => $this->generateMD5($filteredResults),
                            'name'                             => $filteredResults->first()->name,
                            'code'                             => $this->getNewCodeFromDeprecatedCode($filteredResults->first()->code),
                            'report_template_account_group_id' => $this->getReportTemplateAccountGroupIdFromDeprecatedCode($filteredResults->first()->code),
                            'native_account_type_id'           => $this->ReportTemplateAccountGroupObj->native_account_type_id,
                            'native_account_type_coefficient'  => $this->ReportTemplateAccountGroupObj->nativeAccountType->nativeAccountTypeTrailers->first()->advanced_variance_coefficient,
                            'benchmark_id'                     => $filteredResults->first()->id,
                            'property_id'                      => Property::where('property_id_old', $filteredResults->first()->property_id)->value('id'),
                            'targetYear'                       => $this->year,
                            'entityOccupancy'                  => ! $this->VarianceRepository->renaming_occupancy_table ? $this->occupancy : 0,
                            'actual'                           => $this->calculateAmount($filteredResults->first()),
                            'budget'                           => $this->calculateAmount($filteredResults->last()),
                            'actual_gross_amount'              => $filteredResults->first()->rentable_amount * $filteredResults->first()->rentable_area,
                            // occupied & adjusted gr amts = rentable gr amt
                            'budget_gross_amount'              => $filteredResults->last()->rentable_amount * $filteredResults->last()->rentable_area,
                            // occupied & adjusted gr amts = rentable gr amt
                            'childCount'                       => isset($grandChildrenCount) ? $grandChildrenCount : $childCoaCodeCount,
                            'area'                             => (float) $filteredResults->first()->area,
                            'rentable_area'                    => (float) $filteredResults->first()->rentable_area,
                        ];

                        if ($code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                        {
                            $this->targetPayloadSlice['area']              = (float) $filteredResults->first()->area;
                            $this->targetPayloadSlice['rentable_area']     = (float) $filteredResults->first()->rentable_area;
                            $this->targetPayloadSlice['actual']            = $this->calculateAmount($filteredResults->first());
                            $this->targetPayloadSlice['budget']            = $this->calculateAmount($filteredResults->last());
                            $this->targetPayloadSlice['actualGrossAmount'] = $combinedResult['actual_gross_amount'];
                            $this->targetPayloadSlice['budgetGrossAmount'] = $combinedResult['budget_gross_amount'];
                            $this->targetPayloadSlice['childCount']        = $childCoaCodeCount;

                            /**
                             * @todo please doc ReportTemplateAccountGroupObj->reportTemplateAccountGroups ??????? Do you mean children?
                             */
                            if ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren()->count() === 0)
                            {
                                $this->payload[] = new VarianceProperty($combinedResult);
                            }
                        }
                        else
                        {
                            $this->payload[] = new VarianceProperty($combinedResult);
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws GeneralException
     */
    protected function perform_query()
    {
        /**
         * because DatabaseConnectionRepository::getLedgerDatabaseConnection($this->ClientObj)
         * normally (ie out of the box Lavaral) returns a
         * Illuminate\Suoprt|Collection rather than a
         * Illuminate\Database\Eloquent\Collection like almost everywhere else in
         * Laravel, methods in Illuminate\Supprt|Collection that are overridden in
         * Illuminate\Database\Eloquent\Collection should not be used.
         *
         * @todo fix me Mothods in App\Waypoint\Collection (which extends
         * Illuminate\Database\Eloquent\Collection which in turn
         * extends Illuminate\Suoprt|Collection). Need to find the methods
         * in Illuminate\Database\Eloquent\Collection that override a method
         * in Illuminate\Supprt|Collection. Then override or fix these overrides
         * in App\Waypoint\Collection
         *
         * OR
         * update DatabaseConnectionRepository::getLedgerDatabaseConnection() to emit
         * App\Waypoint\Collection's and test
         */
        return
            DatabaseConnectionRepository::getLedgerDatabaseConnection($this->ClientObj)
                                        ->table('BENCHMARK_LEVELS')
                                        ->where(
                                            [
                                                ['BENCHMARK_LEVELS.FROM_YEAR', $this->year],
                                                ['CLIENT_BENCHMARKS.FK_PROPERTY_ID', $this->PropertyObj->property_id_old],
                                            ]
                                        )
                                        ->whereIn('BENCHMARK_LEVELS.BENCHMARK_TYPE', $this->getVarianceBenchmarkTypesPropertyOnly($this->period))
                                        ->whereIn('BENCHMARK_LEVELS.ACCOUNT_CODE', $this->getDeprecatedAccountCodes())
                                        ->join('CLIENT_BENCHMARKS', 'BENCHMARK_LEVELS.BENCHMARK_ID', '=', 'CLIENT_BENCHMARKS.FK_BENCHMARK_ID')
                                        ->select(
                                            'CLIENT_BENCHMARKS.CLIENT_BENCHMARKS_ID as id',
                                            'CLIENT_BENCHMARKS.FK_PROPERTY_ID as property_id',
                                            'CLIENT_BENCHMARKS.BENCHMARK_TYPE as type',
                                            'CLIENT_BENCHMARKS.ACCOUNT_CODE as code',
                                            'CLIENT_BENCHMARKS.ACCOUNT_NAME_UPPER as name',
                                            'CLIENT_BENCHMARKS.' . $this->getAmountFieldFromArea($this->area, true) . ' as amount',
                                            'CLIENT_BENCHMARKS.' . $this->getAmountFieldFromArea(LedgerController::RENTABLE_SELECTION, true) . ' as rentable_amount',
                                            'CLIENT_BENCHMARKS.ASOF_MONTH as month',
                                            'CLIENT_BENCHMARKS.' . $this->getPropertyAreaField() . ' as area',
                                            'CLIENT_BENCHMARKS.' . $this->getPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_area'
                                        )
                                        ->orderBy('CLIENT_BENCHMARKS.ACCOUNT_CODE')
                                        ->get();
    }

    /**
     * @return array
     */
    protected function getDeprecatedAccountCodes()
    {
        return array_merge(
            [$this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
            $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes(),
            $this->ReportTemplateAccountGroupObj->getGrandChildrenDeprecatedCoaCodes()
        );
    }

    /**
     * @param \Illuminate\Support\Collection $result
     * @return string
     */
    private function generateMD5(\Illuminate\Support\Collection $result): string
    {
        return md5($result->first()->id . $result->first()->property_id . $this->year . $this->period . $this->area . $result->first()->code);
    }

    /**
     * @param $data
     * @return float
     * The column CLIENT_BENCHMARKS.AMOUNT_OCC cannot be relied upon, so when looking for that value
     * it must be calculated using: CLIENT_BENCHMARKS.AMOUNT_RNT / occupancy rate
     */
    private function calculateAmount($data)
    {
        if ($this->area == self::OCCUPIED_SELECTION)
        {
            return $this->occupancy == 0 ? 0 : (float) $data->rentable_amount / ($this->occupancy / 100);
        }
        return (float) $data->amount;
    }

    /**
     * @param \App\Waypoint\Collection|\Illuminate\Support\Collection $results
     * @return bool
     */
    private function bothNotNull($results): bool
    {
        return ! (is_null($results->first()->amount) && is_null($results->last()->amount));
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function getDefaultTargetPayloadSlice(): array
    {
        return [
            'apiTitle'        => $this->apiTitle,
            'code'            => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'name'            => $this->ReportTemplateAccountGroupObj->display_name,
            'fromDate'        => $this->getFromDate($this->year, $this->period),
            'toDate'          => $this->getToDate($this->year, $this->period),
            'period'          => $this->period,
            'targetYear'      => $this->year,
            'units'           => $this->units,
            'entityName'      => $this->entityName,
            'entityOccupancy' => ! $this->VarianceRepository->renaming_occupancy_table ? $this->occupancy : 0,
            'area'            => 0,
        ];
    }
}

