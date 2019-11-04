<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\YearOverYearPropertyGroupRanking;
use App\Waypoint\Models\Ledger\YearOverYearPropertyRanking;
use App\Waypoint\Models\Ledger\YearOverYearRanking;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;

/**
 * Class YearOverYearRankingRepository
 */
class YearOverYearRankingRepository extends LedgerRepository
{
    /** @var array */
    protected $fieldSearchable = [];

    /** @var null|integer */
    private $rank = null;

    /** @var null|integer */
    private $tallyOfIncompleteDataProperties = null;

    public $targetYear = null;

    public $previousYear = null;

    protected $payloadItemId = 0;

    /** Configure the Repository **/
    public function model()
    {
        return YearOverYearRanking::class;
    }

    const COLOR_OCCUPIED_TARGET_YEAR          = 'COLOR_OCC2';
    const COLOR_OCCUPIED_PREVIOUS_YEAR        = 'COLOR_OCC1';
    const COLOR_RENTABLE_TARGET_YEAR          = 'COLOR_RNT2';
    const COLOR_RENTABLE_PREVIOUS_YEAR        = 'COLOR_RNT1';
    const COLOR_ADJUSTED_TARGET_YEAR          = 'COLOR_ADJ2';
    const COLOR_ADJUSTED_PREVIOUS_YEAR        = 'COLOR_ADJ1';
    const RANK_OCCUPIED_FIELD                 = 'GROUP_OCC_YOY_RANK';
    const RANK_RENTABLE_FIELD                 = 'GROUP_RNT_YOY_RANK';
    const RANK_ADJUSTED_FIELD                 = 'GROUP_ADJ_YOY_RANK';
    const PERCENTAGE_CHANGE_OCCUPIED          = 'GROUP_OCC_YOY';
    const PERCENTAGE_CHANGE_RENTABLE          = 'GROUP_RNT_YOY';
    const PERCENTAGE_CHANGE_ADJUSTED          = 'GROUP_ADJ_YOY';
    const AMOUNT_FIELD_RENTABLE_TARGET_YEAR   = 'CalcAmountRNTPerSqFt2';
    const AMOUNT_FIELD_OCCUPIED_TARGET_YEAR   = 'CalcAmountOCCPerSqFt2';
    const AMOUNT_FIELD_ADJUSTED_TARGET_YEAR   = 'CalcAmountADJPerSqFt2';
    const AMOUNT_FIELD_RENTABLE_PREVIOUS_YEAR = 'CalcAmountRNTPerSqFt1';
    const AMOUNT_FIELD_OCCUPIED_PREVIOUS_YEAR = 'CalcAmountOCCPerSqFt1';
    const AMOUNT_FIELD_ADJUSTED_PREVIOUS_YEAR = 'CalcAmountADJPerSqFt1';
    const AREA_RENTABLE_TARGET_YEAR_FIELD     = 'INDIVIDUAL_RENTABLE_AREA_FROM_PYTHON2';
    const AREA_RENTABLE_PREVIOUS_YEAR_FIELD   = 'INDIVIDUAL_RENTABLE_AREA_FROM_PYTHON1';
    const AREA_OCCUPIED_TARGET_YEAR_FIELD     = 'INDIVIDUAL_OCCUPIED_AREA_FROM_PYTHON2';
    const AREA_OCCUPIED_PREVIOUS_YEAR_FIELD   = 'INDIVIDUAL_OCCUPIED_AREA_FROM_PYTHON1';
    const AREA_ADJUSTED_TARGET_YEAR_FIELD     = 'INDIVIDUAL_ADJUSTED_AREA_FROM_PYTHON2';
    const AREA_ADJUSTED_PREVIOUS_YEAR_FIELD   = 'INDIVIDUAL_ADJUSTED_AREA_FROM_PYTHON1';

    /**
     * @return Collection|array
     * @throws GeneralException
     */
    public function getPropertyData()
    {
        if ( ! $this->LedgerControllerObj)
        {
            throw new GeneralException('missing ledger controller object');
        }
        if ( ! $this->ReportTemplateAccountGroupObj)
        {
            throw new GeneralException('missing ReportTemplateAccountGroup coa list item object');
        }

        if ( ! $UserAllPropertyGroupObj = $this->LedgerControllerObj->getUserObject()->allPropertyGroup)
        {
            throw new GeneralException('property group missing', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        /** PropertyGroup $UserAllPropertyGroupObj */
        $property_id_old_arr = $UserAllPropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $payload             = new Collection();
        $incompletes         = [];
        $account_code_list   = [$this->ReportTemplateAccountGroupObj->deprecated_waypoint_code];

        if ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren)
        {
            foreach ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren as $child)
            {
                $account_code_list[] = $child->deprecated_waypoint_code;
            }
        }

        $target_year_occupancy_by_property_arr   = $this->getOccupancyForEachProperty($property_id_old_arr, $this->targetYear);
        $previous_year_occupancy_by_property_arr = $this->getOccupancyForEachProperty($property_id_old_arr, $this->previousYear);

        $this->LedgerControllerObj->targetPayloadSlice = $this->getDefaultTargetPayloadSlice();

        if ( ! $this->LedgerControllerObj->isCurrentYearEqualToAsOfYearAndCalendarYearRequested($this->period, $this->targetYear, $this->ClientObj))
        {
            $results = $this->getLedgerDatabaseConnection()
                            ->table('BENCHMARK_LEVELS')
                            ->where('BENCHMARK_LEVELS.BENCHMARK_TYPE', $this->LedgerControllerObj->getBenchmarkType($this->report, $this->period))
                            ->whereIn('FK_PROPERTY_ID', $property_id_old_arr)
                            ->whereIn(
                                'BENCHMARK_LEVELS.ACCOUNT_CODE', [
                                $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                        true)->deprecated_waypoint_code,
                                $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                            ])
                            ->whereIn(
                                'BENCHMARK_LEVELS.FROM_YEAR', [
                                $this->targetYear,
                                $this->previousYear,

                            ])
                            ->join('CLIENT_BENCHMARKS', 'BENCHMARK_LEVELS.BENCHMARK_ID', '=', 'CLIENT_BENCHMARKS.FK_BENCHMARK_ID')
                            ->select(
                                'CLIENT_BENCHMARKS.CLIENT_BENCHMARKS_ID as benchmark_id',
                                'CLIENT_BENCHMARKS.FK_PROPERTY_ID as property_id',
                                'CLIENT_BENCHMARKS.BENCHMARK_TYPE as type',
                                'CLIENT_BENCHMARKS.ACCOUNT_CODE as code',
                                'CLIENT_BENCHMARKS.ACCOUNT_NAME_UPPER as name',
                                'CLIENT_BENCHMARKS.' . $this->getColorFieldForLedger() . ' as color',
                                'CLIENT_BENCHMARKS.' . $this->getAmountField(true) . ' as amount',
                                'CLIENT_BENCHMARKS.' . LedgerController::AMOUNT_RENTABLE_DOUBLE_FIELD . ' as rentable_amount',
                                'CLIENT_BENCHMARKS.FROM_YEAR as year',
                                'CLIENT_BENCHMARKS.YEARMONTHS as months',
                                'CLIENT_BENCHMARKS.' . LedgerController::RENTABLE_SELECTION . '_AREA as rentable_area'
                            )
                            ->get();

            if ($results->count() > 0)
            {
                $targetYearSquareFootageLookup   = $this->getOccupancyForEachProperty($property_id_old_arr, $this->LedgerControllerObj->targetYear, true);
                $previousYearSquareFootageLookup = $this->getOccupancyForEachProperty($property_id_old_arr, $this->LedgerControllerObj->previousYear, true);

                $resultsWithNonZeroArea = $results->filter(
                    function ($result) use ($targetYearSquareFootageLookup, $previousYearSquareFootageLookup)
                    {
                        if ($this->renaming_occupancy_table)
                        {
                            return true;
                        }
                        if ( ! isset($targetYearSquareFootageLookup[$result->property_id]) || ! isset($previousYearSquareFootageLookup[$result->property_id]))
                        {
                            return false;
                        }
                        return
                            ! is_null(
                                $targetYearSquareFootageLookup[$result->property_id][$this->area . '_AREA']
                            ) && (float) $targetYearSquareFootageLookup[$result->property_id][$this->area . '_AREA'] != 0 &&
                            ! is_null(
                                $previousYearSquareFootageLookup[$result->property_id][$this->area . '_AREA']
                            ) && (float) $previousYearSquareFootageLookup[$result->property_id][$this->area . '_AREA'] != 0;
                    }
                );

                $resultsWithNoZeroColorHeader = $resultsWithNonZeroArea->filter(
                    function ($result)
                    {
                        return ! ($result->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                           true)->deprecated_waypoint_code && $result->color == 0);
                    }
                );

                $resultsWithMatchingMonthsInHeaders = $resultsWithNoZeroColorHeader->filter(
                    function ($result) use ($resultsWithNoZeroColorHeader)
                    {
                        if ($result->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                     true)->deprecated_waypoint_code)
                        {
                            $complementary_year = $resultsWithNoZeroColorHeader->where('property_id', $result->property_id)->where(
                                'code', '=', $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                     true)->deprecated_waypoint_code
                            )->where('year', '!=', $result->year)->first();
                            return ! empty ($complementary_year) && $this->matchingMonths($complementary_year->months, $result->months);
                        }
                        return true;
                    }
                );

                $resultsWithNoMissingHeaders = $resultsWithMatchingMonthsInHeaders->filter(
                    function ($result) use ($resultsWithMatchingMonthsInHeaders)
                    {
                        // if lower order line item result
                        if ($result->code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                        {
                            // get header if exists
                            $header = $resultsWithMatchingMonthsInHeaders->filter(
                                function ($item) use ($result)
                                {
                                    return $item->property_id == $result->property_id && $item->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                                                true)->deprecated_waypoint_code;
                                }
                            );
                            return $header->count() != 0;
                        }
                        return true;
                    }
                );

                $resultsWithNoHeaders = $resultsWithNoMissingHeaders->filter(
                    function ($result)
                    {
                        if ($this->ReportTemplateAccountGroupObj->deprecated_waypoint_code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                                      true)->deprecated_waypoint_code)
                        {
                            return true;
                        }
                        else
                        {
                            return $result->code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                            true)->deprecated_waypoint_code;
                        }
                    }
                );

                $completeResults                       = $resultsWithNoHeaders;
                $incompletes                           = array_diff($property_id_old_arr, $completeResults->pluck('property_id')->toArray());
                $this->tallyOfIncompleteDataProperties = count($incompletes);

                foreach ($completeResults->pluck('property_id')->unique() as $property_id_old)
                {
                    /** @var Collection $searchCollection */
                    $searchCollection = $completeResults->filter(
                        function ($result) use ($property_id_old)
                        {
                            return $result->property_id == $property_id_old;
                        }
                    );

                    $searchCollection = $searchCollection->sortByDesc('year');

                    // if there is a pair of results
                    if ($searchCollection->count() == 2)
                    {
                        $target_year_occupancy   = isset($target_year_occupancy_by_property_arr[$property_id_old]) && ! $this->renaming_occupancy_table ? $target_year_occupancy_by_property_arr[$property_id_old] : 0;
                        $previous_year_occupancy = isset($previous_year_occupancy_by_property_arr[$property_id_old]) && ! $this->renaming_occupancy_table ? $previous_year_occupancy_by_property_arr[$property_id_old] : 0;
                        $percentageChange        = $this->getPercentageChange(
                            $this->calculateAmount($searchCollection->first(), $target_year_occupancy),
                            $this->calculateAmount($searchCollection->last(), $previous_year_occupancy)
                        );
                        $payloadItem             = [
                            'LedgerController'                 => $this->LedgerControllerObj,
                            'id'                               => $this->getPayloadItemId(),
                            'client_id'                        => $this->ClientObj->id,
                            'property_id'                      => $searchCollection->first()->property_id,
                            'code'                             => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                            'lineItemName'                     => $this->ReportTemplateAccountGroupObj->display_name,
                            'amount'                           => $percentageChange,
                            'targetYearAmount'                 => $this->calculateAmount($searchCollection->first(), $target_year_occupancy),
                            'previousYearAmount'               => $this->calculateAmount($searchCollection->last(), $previous_year_occupancy),
                            'targetYear'                       => $searchCollection->first()->year,
                            'previousYear'                     => $searchCollection->last()->year,
                            'incompleteData'                   => false,
                            'targetYearOccupancy'              => $target_year_occupancy,
                            'previousYearOccupancy'            => $previous_year_occupancy,
                            'gross_amount_previous_year'       => $searchCollection->last()->rentable_amount * $searchCollection->last()->rentable_area,
                            'gross_amount_target_year'         => $searchCollection->first()->rentable_amount * $searchCollection->first()->rentable_area,
                            'squareFootageTargetYear'          => (float) $searchCollection->first()->rentable_area,
                            'squareFootagePreviousYear'        => (float) $searchCollection->last()->rentable_area,
                            'report_template_account_group_id' => $this->ReportTemplateAccountGroupObj->id,
                        ];

                        $payload->push(new YearOverYearPropertyRanking($payloadItem));
                    }
                }

                // create ranking
                $payloadSorted = $payload->sortBy('amount');
                $rankCounter   = 1;

                foreach ($payloadSorted as $key => $item)
                {
                    $item->rank = $item->incompleteData ? 0 : $rankCounter++;
                }
            }

            if ($payload->count() > 0)
            {
                foreach ($incompletes as $incomplete_property_id_old)
                {
                    $property  = [
                        'LedgerController' => $this->LedgerControllerObj,
                        'id'               => $this->getPayloadItemId(),
                        'client_id'        => $this->ClientObj->id,
                        'property_id'      => $incomplete_property_id_old,
                        'code'             => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'year'             => $this->targetYear,
                        'period'           => $this->period,
                        'area'             => $this->area,
                        'amount'           => null,
                        'rank'             => 0,
                        'incompleteData'   => true,
                    ];
                    $payload[] = new YearOverYearPropertyRanking($property);
                }
            }
        }
        else
        {
            $this->LedgerControllerObj->warnings[] = 'no data for this time period';
        }

        return $payload;
    }

    /**
     * @param LedgerController $LedgerObj
     * @param Client $ClientObj
     * @param PropertyGroup $PropertyGroupObj
     * @param ReportTemplateAccountGroup $BomaReportTemplateAccountGroupObj
     * @param integer $property_id_old_arr
     * @return \App\Waypoint\Collection|array
     */
    public function getGroupDataVersionOne($LedgerObj, Client $ClientObj, PropertyGroup $PropertyGroupObj, ReportTemplateAccountGroup $BomaReportTemplateAccountGroupObj)
    {
        $property_id_old_arr    = $PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $yearOverYearCollection = new Collection();
        $benchmarkType          = $LedgerObj->getBenchmarkType($LedgerObj->report, $LedgerObj->period);
        list($amountField, $rankField) = $LedgerObj->getAmountAndRankFields($LedgerObj->area, true);
        $targetYearPerPropertyOccupancy   = $LedgerObj->getOccupancyForEachProperty($ClientObj, $property_id_old_arr, $LedgerObj->targetYear);
        $previousYearPerPropertyOccupancy = $LedgerObj->getOccupancyForEachProperty($ClientObj, $property_id_old_arr, $LedgerObj->previousYear);
        $dataAvailabilityResults          = $LedgerObj->getDataAvailabilityResultFromRentableArea(
            $ClientObj, $property_id_old_arr, $LedgerObj->period, $LedgerObj->targetYear, $LedgerObj->get_client_asof_date($ClientObj->id)
        );
        if ( ! $LedgerObj->originalYoyGroupCalcTable = $LedgerObj->getCorrectTableBasedOnAvailabilityStatus($ClientObj, $LedgerObj->originalYoyGroupCalcTable))
        {
            throw new GeneralException('group calc in progress and data is not available');
        }

        $results = $LedgerObj->DatabaseConnection
            ->table($LedgerObj->originalYoyGroupCalcTable)
            ->where(
                [
                    'BENCHMARK_TYPE' => $benchmarkType,
                    'ACCOUNT_CODE'   => $BomaReportTemplateAccountGroupObj->deprecated_waypoint_code,
                    'REF_GROUP_ID'   => $PropertyGroupObj->id,
                ]
            )
            ->whereIn('FROM_YEAR', [$LedgerObj->targetYear, $LedgerObj->previousYear])
            ->whereIn('FK_PROPERTY_ID', $property_id_old_arr)
            ->select(
                'FK_PROPERTY_ID as property_id',
                'BENCHMARK_TYPE as type',
                'ACCOUNT_CODE as code',
                "$rankField as rank",
                "$amountField as amount",
                "FROM_YEAR as year",
                $this->getAreaFieldYoyV1() . ' as area',
                $this->getAreaFieldYoyV1(LedgerController::RENTABLE_SELECTION) . ' as rentable_area'
            )
            ->groupBy('YoY_ID', 'FROM_YEAR')
            ->get();

        if ($results->count() > 0)
        {
            $rankCounter = 1;
            foreach (collect($results->pluck('property_id'))->unique() as $property_id)
            {
                $searchCollection = $results->filter(
                    function ($result) use ($property_id)
                    {
                        return $result->property_id == $property_id;
                    }
                );

                $searchCollection = $searchCollection->sortByDesc('year');

                // if there is a pair of results
                if ($searchCollection->count() == 2 && ! empty((double) $searchCollection->last()->amount))
                {
                    // remove id from array for each valid result, creating a list of incompletes for later
                    if (($key = array_search($property_id, $property_id_old_arr)) !== false)
                    {
                        unset($property_id_old_arr[$key]);
                    }

                    $percentageChange           = (((double) $searchCollection->first()->amount - (double) $searchCollection->last()->amount) / (double) $searchCollection->last()->amount) * 100;
                    $yearOverYearCollectionItem = [
                        'LedgerController'           => $LedgerObj,
                        'id'                         => md5($searchCollection->first()->property_id . $searchCollection->first()->year),
                        'client_id'                  => $ClientObj->id,
                        'property_id'                => $searchCollection->first()->property_id,
                        'code'                       => $BomaReportTemplateAccountGroupObj->report_template_account_group_code,
                        'amount'                     => $dataAvailabilityResults[$property_id] ? $percentageChange : 0,
                        'targetYearAmount'           => $searchCollection->first()->amount,
                        'previousYearAmount'         => $searchCollection->last()->amount,
                        'targetYear'                 => $searchCollection->first()->year,
                        'previousYear'               => $searchCollection->last()->year,
                        'incompleteData'             => $searchCollection->first()->rank == 0 || $searchCollection->last()->rank == 0 || ! $dataAvailabilityResults[$property_id],
                        'previousYearOccupancy'      => isset($previousYearPerPropertyOccupancy[$property_id]) && ! $this->renaming_occupancy_table ? $previousYearPerPropertyOccupancy[$property_id] : 0,
                        'targetYearOccupancy'        => isset($targetYearPerPropertyOccupancy[$property_id]) && ! $this->renaming_occupancy_table ? $targetYearPerPropertyOccupancy[$property_id] : 0,
                        'rank'                       => $rankCounter++,
                        'gross_amount_target_year'   => $searchCollection->first()->amount * $searchCollection->first()->area,
                        'gross_amount_previous_year' => $searchCollection->last()->amount * $searchCollection->last()->area,
                        'squareFootageTargetYear'    => $searchCollection->first()->rentable_area,
                        'squareFootagePreviousYear'  => $searchCollection->last()->rentable_area,
                    ];

                    $yearOverYearCollection->push(new YearOverYearPropertyGroupRanking($yearOverYearCollectionItem));
                }
            }

            // create ranking
            $yearOverYearCollectionSorted          = $yearOverYearCollection->sortBy('amount');
            $this->tallyOfIncompleteDataProperties = $yearOverYearCollection->filter(
                function ($result)
                {
                    return $result->incompleteData;
                }
            )->count();

            $rankCount = 1;
            foreach ($yearOverYearCollectionSorted as $item)
            {
                $item->rank = ($item->incompleteData) ? 0 : $rankCount++;
            }
        }

        // create incomplete entries
        if ($yearOverYearCollection->count() > 0)
        {
            foreach ($property_id_old_arr as $property_id)
            {
                $this->tallyOfIncompleteDataProperties += 1;
                $incomplete_property                   = [
                    'LedgerController' => $LedgerObj,
                    'id'               => md5($property_id . $LedgerObj->targetYear . $LedgerObj->period . $LedgerObj->area . $BomaReportTemplateAccountGroupObj->report_template_account_group_code),
                    'client_id'        => $ClientObj->id,
                    'property_id'      => $property_id,
                    'code'             => $BomaReportTemplateAccountGroupObj->report_template_account_group_code,
                    'targetYear'       => $LedgerObj->targetYear,
                    'previousYear'     => $LedgerObj->previousYear,
                    'period'           => $LedgerObj->period,
                    'area'             => $LedgerObj->area,
                    'amount'           => null,
                    'rank'             => 0,
                ];
                $yearOverYearCollection[]              = new YearOverYearPropertyGroupRanking($incomplete_property);
            }
        }
        return $yearOverYearCollection;
    }

    /**
     * @param LedgerController $LedgerObj
     * @param Client $ClientObj
     * @param PropertyGroup $PropertyGroupObj
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @return Collection|array
     * @throws GeneralException
     */
    public function getGroupDataVersionTwo($LedgerObj, Client $ClientObj, PropertyGroup $PropertyGroupObj, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
    {
        $property_id_old_arr = $PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $payloadCollection   = new Collection();

        // database query
        $results = $LedgerObj->DatabaseConnection
            ->table($LedgerObj->newYoyGroupCalcTable)
            ->where(
                [
                    'BENCHMARK_TYPE' => $LedgerObj->getBenchmarkType($LedgerObj->report, $LedgerObj->period),
                    'FROM_YEAR2'     => $LedgerObj->targetYear,
                    'REF_GROUP_ID'   => $PropertyGroupObj->id,
                ]
            )
            ->whereIn('ACCOUNT_CODE', [
                $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id, true)->deprecated_waypoint_code,
            ])
            ->whereIn('FK_PROPERTY_ID', $property_id_old_arr)
            ->select(
                'PROPERTY_NAME',
                $this->getColorField($LedgerObj->area, 'target') . ' as target_year_color',
                $this->getColorField($LedgerObj->area, 'previous') . ' as previous_year_color',
                $this->getRankField($LedgerObj->area) . ' as rank',
                $this->getPercentageChangeField($LedgerObj->area) . ' as percentage_change',
                'BENCHMARK_TYPE as benchmark_type',
                'FK_PROPERTY_ID as property_id',
                'ACCOUNT_CODE as code',
                'FROM_YEAR2 as target_year',
                'FROM_YEAR1 as previous_year',
                $this->getAmountFieldForPreviousYear($LedgerObj->area) . ' as previous_year_group_avg_amount',
                $this->getAmountFieldForTargetYear($LedgerObj->area) . ' as target_year_group_avg_amount',
                $this->getAreaField($LedgerObj->area, 'target') . ' as target_year_relevant_area',
                $this->getAreaField($LedgerObj->area, 'previous') . ' as previous_year_relevant_area',
                $this->getAreaField(LedgerController::RENTABLE_SELECTION, 'target') . ' as target_year_rentable_area',
                $this->getAreaField(LedgerController::RENTABLE_SELECTION, 'previous') . ' as previous_year_rentable_area'
            )
            ->orderBy($this->getPercentageChangeField($LedgerObj->area), 'asc')
            ->get();

        if ($results->count() > 0)
        {
            $this->ClientObj                 = $ClientObj;
            $targetYearSquareFootageLookup   = $this->getOccupancyForEachProperty($property_id_old_arr, $LedgerObj->targetYear, true);
            $previousYearSquareFootageLookup = $this->getOccupancyForEachProperty($property_id_old_arr, $LedgerObj->previousYear, true);

            $resultsWithNonZeroArea = $results->filter(
                function ($result) use ($targetYearSquareFootageLookup, $previousYearSquareFootageLookup)
                {
                    if ($this->renaming_occupancy_table)
                    {
                        return true;
                    }
                    if ( ! isset($targetYearSquareFootageLookup[$result->property_id]) || ! isset($previousYearSquareFootageLookup[$result->property_id]))
                    {
                        return false;
                    }

                    return
                        ! is_null(
                            $targetYearSquareFootageLookup[$result->property_id][$this->area . '_AREA']
                        ) && (float) $targetYearSquareFootageLookup[$result->property_id][$this->area . '_AREA'] != 0 &&
                        ! is_null(
                            $previousYearSquareFootageLookup[$result->property_id][$this->area . '_AREA']
                        ) && (float) $previousYearSquareFootageLookup[$result->property_id][$this->area . '_AREA'] != 0;
                }
            );

            $resultsWithNoZeroColorHeader = $resultsWithNonZeroArea->filter(
                function ($result)
                {
                    return $result->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                    true)->deprecated_waypoint_code && ($result->target_year_color == 0 || $result->previous_year_color == 0) ? false : true;
                }
            );

            $resultsWithNoMissingHeaders = $resultsWithNoZeroColorHeader->filter(
                function ($result) use ($resultsWithNoZeroColorHeader, $ReportTemplateAccountGroupObj)
                {
                    // if lower order line item result
                    if ($result->code == $ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                    {
                        // get header if exists
                        $header = $resultsWithNoZeroColorHeader->filter(
                            function ($item) use ($result)
                            {
                                return $item->property_id == $result->property_id && $item->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                                            true)->deprecated_waypoint_code;
                            }
                        );
                        return $header->count() != 0;
                    }
                    return true;
                }
            );

            $resultsWithNoHeaders = $resultsWithNoMissingHeaders->filter(
                function ($result) use ($ReportTemplateAccountGroupObj)
                {

                    if ($ReportTemplateAccountGroupObj->deprecated_waypoint_code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                            true)->deprecated_waypoint_code)
                    {
                        return true;
                    }
                    else
                    {
                        return $result->code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                        true)->deprecated_waypoint_code;
                    }
                }
            );

            $completeResults                       = $resultsWithNoHeaders;
            $incompletes                           = array_diff($property_id_old_arr, $completeResults->pluck('property_id')->toArray());
            $this->tallyOfIncompleteDataProperties = count($incompletes);

            // create payload items for all complete results
            $rankCount = 1;
            foreach ($completeResults as $result)
            {
                // populate payload item
                $propertyDetails = [
                    'LedgerController'           => $LedgerObj,
                    'id'                         => $this->getPayloadItemId(),
                    'client_id'                  => $ClientObj->id,
                    'property_id'                => $result->property_id,
                    'code'                       => $ReportTemplateAccountGroupObj->code,
                    'amount'                     => $result->percentage_change,
                    'targetYearAmount'           => $result->target_year_group_avg_amount,
                    'previousYearAmount'         => $result->previous_year_group_avg_amount,
                    'targetYear'                 => $result->target_year,
                    'previousYear'               => $result->previous_year,
                    'rank'                       => $rankCount++,
                    'incompleteData'             => false,
                    'targetYearOccupancy'        => $this->getOccupancyFromSquareFootage(
                        $targetYearSquareFootageLookup[$result->property_id]['RENTABLE_AREA'], $targetYearSquareFootageLookup[$result->property_id]['OCCUPIED_AREA']
                    ),
                    'previousYearOccupancy'      => $this->getOccupancyFromSquareFootage(
                        $previousYearSquareFootageLookup[$result->property_id]['RENTABLE_AREA'], $previousYearSquareFootageLookup[$result->property_id]['OCCUPIED_AREA']
                    ),
                    'gross_amount_target_year'   => $result->target_year_group_avg_amount * $result->target_year_relevant_area,
                    'gross_amount_previous_year' => $result->previous_year_group_avg_amount * $result->previous_year_relevant_area,
                    'squareFootageTargetYear'    => $result->target_year_rentable_area,
                    'squareFootagePreviousYear'  => $result->previous_year_rentable_area,
                ];

                $payloadCollection->push(new YearOverYearPropertyGroupRanking($propertyDetails));
            };

            // create incomplete entries
            if ($payloadCollection->count() > 0)
            {
                foreach ($incompletes as $property_id)
                {
                    $property            = [
                        'LedgerController' => $LedgerObj,
                        'id'               => $this->getPayloadItemId(),
                        'client_id'        => $ClientObj->id,
                        'property_id'      => $property_id,
                        'code'             => $ReportTemplateAccountGroupObj->code,
                        'targetYear'       => $LedgerObj->targetYear,
                        'previousYear'     => $LedgerObj->previousYear,
                        'period'           => $LedgerObj->period,
                        'area'             => $LedgerObj->area,
                        'amount'           => null,
                        'rank'             => 0,
                    ];
                    $payloadCollection[] = new YearOverYearPropertyGroupRanking($property);
                }
            }
        }

        // payload collection
        return $payloadCollection;
    }

    /**
     * @param $data
     * @param $occupancy
     * @return float|int
     * The column CLIENT_BENCHMARKS.AMOUNT_OCC cannot be relied upon, so when looking for that value
     * it must be calculated using: CLIENT_BENCHMARKS.AMOUNT_RNT / occupancy rate
     */
    private function calculateAmount($data, $occupancy)
    {
        if ($this->area == LedgerController::OCCUPIED_SELECTION)
        {
            return $occupancy == 0 ? 0 : $data->rentable_amount / ($occupancy / 100);
        }
        return $data->amount;
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function getDefaultTargetPayloadSlice(): array
    {
        return [
            'apiTitle'     => $this->LedgerControllerObj->apiTitle,
            'name'         => $this->ReportTemplateAccountGroupObj->display_name,
            'code'         => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'targetYear'   => (int) $this->targetYear,
            'previousYear' => (int) $this->previousYear,
            'fromDate'     => $this->LedgerControllerObj->getFromDate($this->previousYear, $this->period),
            'toDate'       => $this->LedgerControllerObj->getToDate($this->targetYear, $this->period),
            'entityName'   => $this->LedgerControllerObj->entityName,
            'units'        => $this->LedgerControllerObj->units,
            'period'       => $this->period,
        ];
    }

    /**
     * @param $targetYearMonths
     * @param $previousYearMonths
     * @return bool
     * @throws GeneralException
     */
    private function matchingMonths($targetYearMonths, $previousYearMonths)
    {
        if ( ! $this->usablePeriod())
        {
            throw new GeneralException('unusable period given');
        }

        if ( ! $this->LedgerControllerObj)
        {
            throw new GeneralException('no ledger controller object');
        }

        if ($this->period == LedgerController::CALENDAR_YEAR_ABBREV || $this->period == LedgerController::TRAILING_12_ABBREV)
        {
            if (count(explode(',', $targetYearMonths)) != 12 || count(explode(',', $previousYearMonths)) != 12)
            {
                return false;
            }
        }
        elseif ($this->period == LedgerController::YEAR_TO_DATE_ABBREV)
        {
            if (count(explode(',', $targetYearMonths)) != $this->LedgerControllerObj->get_client_asof_date($this->ClientObj->id)->month || count(
                                                                                                                                               explode(',', $previousYearMonths)
                                                                                                                                           ) != $this->LedgerControllerObj->get_client_asof_date(
                    $this->ClientObj->id
                )->month)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $target_year_amount
     * @param $previous_year_amount
     * @return float|int
     */
    private function getPercentageChange($target_year_amount, $previous_year_amount)
    {
        return empty((double) $previous_year_amount) ? null : (((double) $target_year_amount - (double) $previous_year_amount) / (double) $previous_year_amount) * 100;
    }

    /**
     * @param $area
     * @return string
     */
    private function getPercentageChangeField($area)
    {
        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return self::PERCENTAGE_CHANGE_RENTABLE;
            case LedgerController::OCCUPIED_SELECTION:
                return self::PERCENTAGE_CHANGE_OCCUPIED;
            case LedgerController::ADJUSTED_SELECTION:
                return self::PERCENTAGE_CHANGE_ADJUSTED;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @return string
     */
    private function getColorFieldForLedger()
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }

        return 'COLOR_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    /**
     * @param $area
     * @param $type
     * @return string
     */
    private function getColorField($area, $type)
    {
        if ( ! in_array($type, ['target', 'previous']))
        {
            throw new GeneralException('unusable year type');
        }

        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return $type == 'target' ? self::COLOR_RENTABLE_TARGET_YEAR : self::COLOR_RENTABLE_PREVIOUS_YEAR;
            case LedgerController::OCCUPIED_SELECTION:
                return $type == 'target' ? self::COLOR_OCCUPIED_TARGET_YEAR : self::COLOR_OCCUPIED_PREVIOUS_YEAR;
            case LedgerController::ADJUSTED_SELECTION:
                return $type == 'taget' ? self::COLOR_ADJUSTED_TARGET_YEAR : self::COLOR_ADJUSTED_PREVIOUS_YEAR;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @param $type
     * @return string
     */
    private function getAreaField($area, $type)
    {
        if ( ! in_array($type, ['target', 'previous']))
        {
            throw new GeneralException('unusable year type');
        }

        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return $type == 'target' ? self::AREA_RENTABLE_TARGET_YEAR_FIELD : self::AREA_RENTABLE_PREVIOUS_YEAR_FIELD;
            case LedgerController::OCCUPIED_SELECTION:
                return $type == 'target' ? self::AREA_OCCUPIED_TARGET_YEAR_FIELD : self::AREA_OCCUPIED_PREVIOUS_YEAR_FIELD;
            case LedgerController::ADJUSTED_SELECTION:
                return $type == 'taget' ? self::AREA_ADJUSTED_TARGET_YEAR_FIELD : self::AREA_ADJUSTED_PREVIOUS_YEAR_FIELD;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    private function getAreaFieldYoyV1($area = null)
    {
        if ( ! $this->usableArea() || ! $this->usableArea($area))
        {
            throw new GeneralException('ususable area given');
        }
        if ($area)
        {
            return 'INDIVIDUAL_' . $area . '_AREA_FROM_PYTHON';
        }
        return 'INDIVIDUAL_' . $this->area . '_AREA_FROM_PYTHON';
    }

    /**
     * @param $area
     * @return string
     */
    private function getRankField($area)
    {
        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return self::RANK_RENTABLE_FIELD;
            case LedgerController::OCCUPIED_SELECTION:
                return self::RANK_OCCUPIED_FIELD;
            case LedgerController::ADJUSTED_SELECTION:
                return self::RANK_ADJUSTED_FIELD;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return string
     */
    protected function getAmountField($double = false)
    {
        if ( ! in_array($this->area, LedgerController::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable area given');
        }
        return $double ? 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area] . '_DOUBLE' : 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    /**
     * @param $area
     * @return string
     */
    private function getAmountFieldForTargetYear($area)
    {
        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return self::AMOUNT_FIELD_RENTABLE_TARGET_YEAR;
            case $area == LedgerController::OCCUPIED_SELECTION:
                return self::AMOUNT_FIELD_OCCUPIED_TARGET_YEAR;
            case LedgerController::ADJUSTED_SELECTION:
                return self::AMOUNT_FIELD_ADJUSTED_TARGET_YEAR;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return string
     */
    private function getAmountFieldForPreviousYear($area)
    {
        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return self::AMOUNT_FIELD_RENTABLE_PREVIOUS_YEAR;
            case $area == LedgerController::OCCUPIED_SELECTION:
                return self::AMOUNT_FIELD_OCCUPIED_PREVIOUS_YEAR;
            case LedgerController::ADJUSTED_SELECTION:
                return self::AMOUNT_FIELD_ADJUSTED_PREVIOUS_YEAR;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    protected function getPayloadItemId()
    {
        return ++$this->payloadItemId;
    }

}
