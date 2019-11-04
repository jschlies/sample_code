<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\YearOverYearProperty;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use App\Waypoint\Repositories\Ledger\YearOverYearRepository;

/**
 * Class YearOverYearPropertyController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class YearOverYearPropertyController extends LedgerController
{
    /** @var  YearOverYearRepository */
    private $YearOverYearRepository;

    /** @var string */
    public $apiTitle = 'YearOverYearProperty';

    /** @var string */
    public $apiDisplayName = 'Yearly Trend';

    /** @var string */
    public $unitsDisplayText = '% Change';

    protected $target_year_occupancy = null;

    protected $previous_year_occupany = null;

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'                    => 'Expense Name',
        'amount'                  => '% Change',
        'grossAmountTargetYear'   => 'Gross Amount (target year)',
        'grossAmountPreviousYear' => 'Gross Amount (previous year)',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '0.00"%"',
            'C' => '"$"#,##0.00',
            'D' => '"$"#,##0.00',
        ],
    ];

    /**
     * YearOverYearPropertyController constructor.
     * @param YearOverYearRepository $YearOverYearRepo
     */
    public function __construct(YearOverYearRepository $YearOverYearRepo)
    {
        parent::__construct($YearOverYearRepo);
        $this->YearOverYearRepository                      = $YearOverYearRepo;
        $this->YearOverYearRepository->LedgerControllerObj = $this;
        $this->targetYear                                  = $this->year;
        $this->previousYear                                = $this->targetYear - self::YEAR_OFFSET;
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @param bool $suppressResponse
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws LedgerException
     */
    public function index(
        $client_id,
        $property_id,
        $report,
        $year,
        $period,
        $area,
        $report_template_account_group_id,
        $suppressResponse = false
    ) {

        $this->initInputForCombinedSpreadsheets(
            [
                'year'         => $year,
                'targetYear'   => $year,
                'previousYear' => $year - 1,
                'period'       => $period,
                'area'         => $area,
                'report'       => $report,
            ],
            $this->YearOverYearRepository
        );
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();
        $this->entityName             = $this->PropertyObj->display_name;
        $this->target_year_occupancy  = $this->YearOverYearRepository->getOccupancyForSingleProperty($this->PropertyObj->property_id_old, $this->targetYear);
        $this->previous_year_occupany = $this->YearOverYearRepository->getOccupancyForSingleProperty($this->PropertyObj->property_id_old, $this->previousYear);
        $this->targetPayloadSlice     = $this->getDefaultTargetPayloadSlice();

        if ( ! $this->isCurrentYearEqualToAsOfYearAndCalendarYearRequested($this->period, $this->targetYear, $this->ClientObj))
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
            // return json payload
            return $this->sendResponse(
                $this->payload->toArray(),
                'year over year trends benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    protected function package_data(Collection $results)
    {
        if ($this->isCompleteData($results))
        {
            foreach (collect($results->pluck('code'))->unique() as $code)
            {
                // process yearly data
                $searchCollection = $results->filter(function ($result) use ($code)
                {
                    return $result->code == $code;
                });
                $searchCollection = $searchCollection->sortByDesc('year');

                // if there is a pair of results and if not then do not provide a yoy comparison for that line item
                if ($searchCollection->count() == 2)
                {
                    $percentageChange = $this->calculatePercentageChange(
                        $searchCollection->last(),
                        $searchCollection->first()
                    );

                    $yearOverYearPropertyCollectionItem = [
                        'LedgerController'                 => $this,
                        'id'                               => random_int(1, 10000),
                        'name'                             => $searchCollection->first()->name,
                        'code'                             => $this->getNewCodeFromDeprecatedCode($searchCollection->first()->code),
                        'amount'                           => $percentageChange,
                        'targetYear'                       => $searchCollection->first()->year,
                        'previousYear'                     => $searchCollection->last()->year,
                        'targetYearAmount'                 => $this->calculateAmount($searchCollection->first(), $this->target_year_occupancy),
                        'previousYearAmount'               => $this->calculateAmount($searchCollection->last(), $this->previous_year_occupany),
                        'targetYearOccupancy'              => ! $this->YearOverYearRepository->renaming_occupancy_table ? $this->target_year_occupancy : 0,
                        'previousYearOccupancy'            => ! $this->YearOverYearRepository->renaming_occupancy_table ? $this->previous_year_occupany : 0,
                        'gross_amount_previous_year'       => $searchCollection->last()->rentable_amount * $searchCollection->last()->rentable_area,
                        // occupied & adjusted gr amts = rentable gr amt
                        'gross_amount_target_year'         => $searchCollection->first()->rentable_amount * $searchCollection->first()->rentable_area,
                        // occupied & adjusted gr amts = rentable gr amt'squareFootagePreviousYear'  => $searchCollection->last()->rentable_area,
                        'squareFootageTargetYear'          => (double) $searchCollection->first()->rentable_area,
                        'squareFootagePreviousYear'        => (double) $searchCollection->last()->rentable_area,
                        'report_template_account_group_id' => $this->getReportTemplateAccountGroupIdFromDeprecatedCode($searchCollection->first()->code),
                        'native_account_type_coefficient'  => $this->ReportTemplateAccountGroupObj->nativeAccountType->nativeAccountTypeTrailers->first()->advanced_variance_coefficient,
                    ];

                    if ($this->isTarget($searchCollection->first()))
                    {
                        $payloadModifications = [
                            'amount'                           => $percentageChange,
                            'targetYearAmount'                 => $this->calculateAmount($searchCollection->first(), $this->target_year_occupancy),
                            'previousYearAmount'               => $this->calculateAmount($searchCollection->last(), $this->previous_year_occupany),
                            'targetYearOccupancy'              => ! $this->YearOverYearRepository->renaming_occupancy_table ? $this->target_year_occupancy : 0,
                            'previousYearOccupancy'            => ! $this->YearOverYearRepository->renaming_occupancy_table ? $this->previous_year_occupany : 0,
                            'grossAmountPreviousYear'          => $searchCollection->last()->rentable_amount * $searchCollection->last()->rentable_area,
                            // occupied & adjusted gr amts = rentable gr amt
                            'grossAmountTargetYear'            => $searchCollection->first()->rentable_amount * $searchCollection->first()->rentable_area,
                            // occupied & adjusted gr amts = rentable gr amt
                            'squareFootageTargetYear'          => (double) $searchCollection->first()->rentable_area,
                            'squareFootagePreviousYear'        => (double) $searchCollection->last()->rentable_area,
                            'report_template_account_group_id' => $this->ReportTemplateAccountGroupObj->id,
                        ];

                        $this->targetPayloadSlice = array_merge($this->targetPayloadSlice, $payloadModifications);

                        // TODO (Alex) - make sure non-coupled y1/y2 results are correctly trimmed
                        if (
                            $searchCollection->first() != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                  true)->deprecated_waypoint_code &&
                            $this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() == 0
                        )
                        {
                            $this->payload->push(new YearOverYearProperty($yearOverYearPropertyCollectionItem));
                        }
                    }
                    elseif ($searchCollection->first()->code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                        true)->deprecated_waypoint_code)
                    {
                        $this->payload->push(new YearOverYearProperty($yearOverYearPropertyCollectionItem));
                    }
                }
            }
        }
    }

    /**
     * @return Collection
     * @throws GeneralException
     */
    protected function perform_query()
    {
        $this->DatabaseConnection = DatabaseConnectionRepository::getLedgerDatabaseConnection($this->ClientObj, true);
        return collect_waypoint(
            $this->DatabaseConnection
                ->table('CLIENT_BENCHMARKS')
                ->where(
                    [
                        ['BENCHMARK_TYPE', $this->getBenchmarkType($this->report, $this->period)],
                        ['FK_PROPERTY_ID', $this->PropertyObj->property_id_old],
                    ]
                )
                ->whereIn(
                    'ACCOUNT_CODE',
                    array_merge(
                        [
                            $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                    true)->deprecated_waypoint_code,
                            $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                        ],
                        $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes()
                    )
                )
                ->whereIn('FROM_YEAR', [$this->targetYear, $this->previousYear])
                ->select(
                    'CLIENT_BENCHMARKS_ID as benchmark_id',
                    'FK_PROPERTY_ID as property_id',
                    'BENCHMARK_TYPE as type',
                    'ACCOUNT_CODE as code',
                    'ACCOUNT_NAME_UPPER as name',
                    $this->getAmountFieldFromArea($this->area, true) . ' as amount',
                    $this->getAmountFieldFromArea(LedgerController::RENTABLE_SELECTION, true) . ' as rentable_amount',
                    $this->getPropertyAreaField(self::RENTABLE_SELECTION) . ' as rentable_area',
                    $this->getRankField($this->area) . ' as rank',
                    'FROM_YEAR as year',
                    'YEARMONTHS as months'
                )
                ->get()
        );
    }

    /**
     * @param $result
     * @return bool
     */
    private function isTarget($result): bool
    {
        return $result->code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
    }

    /**
     * @param $results
     * @return bool
     *      Enforce data conditions on root code (40_000_h2):
     *      1. months have to match
     *      2. rank cannot be zero
     */
    private function isCompleteData($results)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $root_coa_code_result = $results->filter(function ($item)
        {
            return $item->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                          true)->deprecated_waypoint_code;
        });

        /** @noinspection PhpUndefinedMethodInspection */
        if ($root_coa_code_result->count() != 2)
        {
            return false;
        }

        // if either year's rank is zero
        /** @noinspection PhpUndefinedMethodInspection */
        if ($root_coa_code_result->first()->rank == 0 && $root_coa_code_result->last()->rank == 0)
        {
            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $root_year_one_months_arr = collect(explode(',', $root_coa_code_result->first()->months))->map(function ($item)
        {
            return substr($item, 4, 2);
        })->toArray();
        /** @noinspection PhpUndefinedMethodInspection */
        $root_year_two_months_arr = collect(explode(',', $root_coa_code_result->last()->months))->map(function ($item)
        {
            return substr($item, 4, 2);
        })->toArray();

        if ($root_year_one_months_arr !== $root_year_two_months_arr)
        {
            return false;
        }

        return true;
    }

    /**
     * @param $data
     * @param $occupancy
     * @return float|int
     *
     * The column CLIENT_BENCHMARKS.AMOUNT_OCC cannot be relied upon, so when looking for that value
     * it must be calculated using: CLIENT_BENCHMARKS.AMOUNT_RNT / occupancy rate
     */
    private function calculateAmount($data, $occupancy)
    {
        if ($this->area == self::OCCUPIED_SELECTION)
        {
            return $occupancy == 0 ? 0 : (float) $data->rentable_amount / ($occupancy / 100);
        }
        return (float) $data->amount;
    }

    /**
     * @param $previousYearData
     * @param $targetYearData
     * @return float|int
     *
     * Percentage Change is adapted to accommodate CLIENT_BENCHMARKS.AMOUNT_OCC's unreliabily
     */
    private function calculatePercentageChange($previousYearData, $targetYearData)
    {
        if ($previousYearData->amount == 0)
        {
            return 0;
        }
        if ($this->area == self::OCCUPIED_SELECTION)
        {
            return (($this->calculateAmount($targetYearData, $this->target_year_occupancy) - $this->calculateAmount(
                            $previousYearData,
                            $this->previous_year_occupany
                        )) / $this->calculateAmount(
                        $previousYearData,
                        $this->previous_year_occupany
                    )) * 100;
        }
        return (($targetYearData->amount - $previousYearData->amount) / $previousYearData->amount) * 100;
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function getDefaultTargetPayloadSlice(): array
    {
        return [
            'apiTitle'     => $this->apiTitle,
            'code'         => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'name'         => $this->ReportTemplateAccountGroupObj->display_name,
            'fromDate'     => $this->getFromDate($this->previousYear, $this->period),
            'toDate'       => $this->getToDate($this->targetYear, $this->period),
            'period'       => $this->period,
            'targetYear'   => $this->targetYear,
            'previousYear' => $this->previousYear,
            'entityName'   => $this->entityName,
            'units'        => $this->units,
        ];
    }
}
