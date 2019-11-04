<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\Ledger\YearOverYear;
use App\Waypoint\Models\Ledger\YearOverYearPropertyGroup;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;

/**
 * Class YearOverYearRepository
 */
class YearOverYearRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @param LedgerController $LedgerObj
     * @param PropertyGroup $PropertyGroupObj
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @param $boma_coa_code_arr
     * @return Collection
     * @throws GeneralException
     */
    public function getDataVersionOne($LedgerObj, PropertyGroup $PropertyGroupObj, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj, $boma_coa_code_arr)
    {
        $benchmarkType = $LedgerObj->getBenchmarkType($LedgerObj->report, $LedgerObj->period);

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($amountField, $benchmarkAvgField) = $LedgerObj->getGroupAmountFieldsFromArea($LedgerObj->area);

        if ( ! $LedgerObj->originalYoyGroupCalcTable = $LedgerObj->getCorrectTableBasedOnAvailabilityStatus($this->ClientObj, $LedgerObj->originalYoyGroupCalcTable))
        {
            throw new GeneralException('group calc in progress and data is not available');
        }

        $results = $LedgerObj->DatabaseConnection
            ->table($LedgerObj->originalYoyGroupCalcTable)
            ->where(
                [
                    ['REF_GROUP_ID', $PropertyGroupObj->id],
                    ['BENCHMARK_TYPE', $benchmarkType],
                ]
            )
            ->whereNotNull($amountField)
            ->whereIn('ACCOUNT_CODE', $boma_coa_code_arr)
            ->where(
                function ($query) use ($LedgerObj)
                {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $query->where('FROM_YEAR', $LedgerObj->targetYear)
                          ->orWhere('FROM_YEAR', $LedgerObj->previousYear);
                }
            )
            ->select(
                'FK_BENCHMARK_ID as benchmark_id',
                'FK_PROPERTY_ID as property_id',
                'BENCHMARK_TYPE as type',
                'ACCOUNT_CODE as code',
                'ACCOUNT_NAME_UPPER as name',
                "$amountField as amount",
                'FROM_YEAR as year'
            )
            ->get();

        // processs results
        $resultsCollection                     = collect($results);
        $yearOverYearCollection                = new Collection();
        $square_footage_target_year            = $this->getGroupSumSquareFootage($PropertyGroupObj->getAllProperties()->pluck('property_id_old'), $LedgerObj->targetYear);
        $square_footage_previous_year          = $this->getGroupSumSquareFootage($PropertyGroupObj->getAllProperties()->pluck('property_id_old'), $LedgerObj->previousYear);
        $rentable_square_footage_target_year   = $this->getGroupSumSquareFootage(
            $PropertyGroupObj->getAllProperties()->pluck('property_id_old'),
            $LedgerObj->targetYear,
            LedgerController::RENTABLE_SELECTION);
        $rentable_square_footage_previous_year = $this->getGroupSumSquareFootage(
            $PropertyGroupObj->getAllProperties()->pluck('property_id_old'),
            $LedgerObj->previousYear,
            LedgerController::RENTABLE_SELECTION
        );

        // loop over account codes then package up each result
        foreach (collect($resultsCollection->pluck('code'))->unique() as $code)
        {
            // process yearly data
            $searchCollection = $resultsCollection->filter(
                function ($result) use ($code)
                {
                    return $result->code == $code;
                }
            );

            $searchCollection = $searchCollection->sortByDesc('year');

            // if there is a pair of results
            if ($searchCollection->count() == 2 && ! empty($searchCollection->last()->amount))
            {
                $search_code      = $searchCollection->first()->code;
                $percentageChange = (($searchCollection->first()->amount - $searchCollection->last()->amount) / $searchCollection->last()->amount) * 100;
                /** @var ReportTemplateAccountGroup $NeededReportTemplateAccountGroupsObj */
                $NeededReportTemplateAccountGroupsObj = $this->ReportTemplateObj
                    ->reportTemplateAccountGroups
                    ->where('deprecated_waypoint_code', $search_code)
                    ->first();
                $yearOverYearCollectionItem           = [
                    'LedgerController'                 => $LedgerObj,
                    'id'                               => random_int(1, 100000),
                    'name'                             => $NeededReportTemplateAccountGroupsObj->display_name,
                    'code'                             => $NeededReportTemplateAccountGroupsObj->report_template_account_group_code,
                    'report_template_account_group_id' => $NeededReportTemplateAccountGroupsObj->id,
                    'amount'                           => $percentageChange,
                    'targetYear'                       => $searchCollection->first()->year,
                    'previousYear'                     => $searchCollection->last()->year,
                    'targetYearAmount'                 => $searchCollection->first()->amount,
                    'previousYearAmount'               => $searchCollection->last()->amount,
                    'targetYearOccupancy'              => $LedgerObj->targetYearOccupancy,
                    'previousYearOccupancy'            => $LedgerObj->previousYearOccupancy,
                    'gross_amount_target_year'         => $searchCollection->first()->amount * $square_footage_target_year,
                    'gross_amount_previous_year'       => $searchCollection->last()->amount * $square_footage_previous_year,
                    'squareFootageTargetYear'          => $rentable_square_footage_target_year,
                    'squareFootagePreviousYear'        => $rentable_square_footage_previous_year,
                ];

                if ($searchCollection->first()->code == $ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                {
                    $payload_slice_additions = [
                        'amount'                    => $percentageChange,
                        'targetYearAmount'          => $searchCollection->first()->amount,
                        'previousYearAmount'        => $searchCollection->last()->amount,
                        'grossAmountTargetYear'     => $searchCollection->first()->amount * $square_footage_target_year,
                        'grossAmountPreviousYear'   => $searchCollection->last()->amount * $square_footage_previous_year,
                        'squareFootageTargetYear'   => $rentable_square_footage_target_year,
                        'squareFootagePreviousYear' => $rentable_square_footage_previous_year,
                        'targetYearOccupancy'       => $LedgerObj->targetYearOccupancy,
                        'previousYearOccupancy'     => $LedgerObj->previousYearOccupancy,
                    ];

                    $LedgerObj->targetPayloadSlice = array_merge($LedgerObj->targetPayloadSlice, $payload_slice_additions);

                    if ($ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() == 0)
                    {
                        $yearOverYearCollection->push(new YearOverYearPropertyGroup($yearOverYearCollectionItem));
                    }
                }
                else
                {
                    $yearOverYearCollection->push(new YearOverYearPropertyGroup($yearOverYearCollectionItem));
                }
            }
        }

        return $yearOverYearCollection;
    }

    /**
     * @param LedgerController $LedgerObj
     * @param PropertyGroup $PropertyGroupObj
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @param $boma_coa_code_arr
     * @return \App\Waypoint\Collection
     */
    public function getDataVersionTwo($LedgerObj, PropertyGroup $PropertyGroupObj, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj, $boma_coa_code_arr)
    {
        list($percentageChange, $targetYearAmount, $previousYearAmount) = $this->getYoyGroupAmountFieldsFromArea($LedgerObj->area);
        $benchmarkType          = $LedgerObj->getBenchmarkType($LedgerObj->report, $LedgerObj->period);
        $yearOverYearCollection = new Collection();

        $results = $LedgerObj->DatabaseConnection
            ->table($LedgerObj->newYoyGroupCalcTable)
            ->where(
                [
                    ['REF_GROUP_ID', $PropertyGroupObj->id],
                    ['BENCHMARK_TYPE', $benchmarkType],
                    ['FROM_YEAR2', $LedgerObj->targetYear],
                ]
            )
            ->whereIn('ACCOUNT_CODE', $boma_coa_code_arr)
            ->whereNotNull($percentageChange)
            ->whereNotNull($targetYearAmount)
            ->whereNotNull($previousYearAmount)
            ->select(
                'FK_PROPERTY_ID as property_id',
                'BENCHMARK_TYPE as type',
                'ACCOUNT_CODE as code',
                "$percentageChange as percentageChange",
                "$targetYearAmount as targetYearAmount",
                "$previousYearAmount as previousYearAmount",
                'FROM_YEAR2 as targetYear',
                'FROM_YEAR1 as previousYear',
                $this->getGroupSquareFootageField(LedgerController::RENTABLE_SELECTION, 'target') . ' as targetYearRentableArea',
                $this->getGroupSquareFootageField(LedgerController::OCCUPIED_SELECTION, 'target') . ' as targetYearOccupiedArea',
                $this->getGroupSquareFootageField(LedgerController::RENTABLE_SELECTION, 'previous') . ' as previousYearRentableArea',
                $this->getGroupSquareFootageField(LedgerController::OCCUPIED_SELECTION, 'previous') . ' as previousYearOccupiedArea',
                $this->getGroupSquareFootageField($LedgerObj->area, 'target') . ' as targetYearRelevantArea',
                $this->getGroupSquareFootageField($LedgerObj->area, 'previous') . ' as previousYearRelevantArea'
            )
            ->get();

        if ($results->count() > 0)
        {
            foreach ($results as $result)
            {
                $percentageChange      = $result->percentageChange;
                $targetYearOccupancy   = $this->getOccupancyFromSquareFootage($result->targetYearRentableArea, $result->targetYearOccupiedArea);
                $previousYearOccupancy = $this->getOccupancyFromSquareFootage($result->previousYearRentableArea, $result->previousYearOccupiedArea);

                $yearOverYearCollectionItem = [
                    'LedgerController'                 => $LedgerObj,
                    'id'                               => random_int(1, 100000),
                    'name'                             => $this->ReportTemplateObj->reportTemplateAccountGroups->where('deprecated_waypoint_code', $result->code)
                                                                                                               ->first()->display_name,
                    'code'                             => $this->ReportTemplateObj
                        ->reportTemplateAccountGroups
                        ->where('deprecated_waypoint_code', $result->code)
                        ->first()
                        ->report_template_account_group_code,
                    'report_template_account_group_id' => $this->ReportTemplateObj
                        ->reportTemplateAccountGroups
                        ->where('deprecated_waypoint_code', $result->code)
                        ->first()
                        ->id,
                    'amount'                           => $percentageChange,
                    'targetYear'                       => $LedgerObj->targetYear,
                    'previousYear'                     => $LedgerObj->previousYear,
                    'targetYearAmount'                 => $result->targetYearAmount,
                    'previousYearAmount'               => $result->previousYearAmount,
                    'targetYearOccupancy'              => $targetYearOccupancy,
                    'previousYearOccupancy'            => $previousYearOccupancy,
                    'gross_amount_target_year'         => $result->targetYearAmount * $result->targetYearRelevantArea,
                    'gross_amount_previous_year'       => $result->previousYearAmount * $result->previousYearRelevantArea,
                    'squareFootageTargetYear'          => $result->targetYearRentableArea,
                    'squareFootagePreviousYear'        => $result->previousYearRentableArea,
                    'native_account_type_coefficient'  => $ReportTemplateAccountGroupObj->nativeAccountType->nativeAccountTypeTrailers->first()->advanced_variance_coefficient,
                ];

                if ($result->code == $ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                {
                    $payload_slice_additions = [
                        'targetYearOccupancy'       => $targetYearOccupancy,
                        'previousYearOccupancy'     => $previousYearOccupancy,
                        'amount'                    => $percentageChange,
                        'targetYearAmount'          => $result->targetYearAmount,
                        'previousYearAmount'        => $result->previousYearAmount,
                        'targetYear'                => $result->targetYear,
                        'previousYear'              => $result->previousYear,
                        'grossAmountTargetYear'     => $result->targetYearAmount * $result->targetYearRelevantArea,
                        'grossAmountPreviousYear'   => $result->previousYearAmount * $result->previousYearRelevantArea,
                        'squareFootageTargetYear'   => $result->targetYearRelevantArea,
                        'squareFootagePreviousYear' => $result->previousYearRelevantArea,
                    ];

                    $LedgerObj->targetPayloadSlice = array_merge($LedgerObj->targetPayloadSlice, $payload_slice_additions);

                    if ($ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() == 0)
                    {
                        $yearOverYearCollection->push(new YearOverYearPropertyGroup($yearOverYearCollectionItem));
                    }
                }
                else
                {
                    if ($result->code !=
                        $this->ReportTemplateAccountGroupObj
                            ->nativeAccountType
                            ->getUltimateParentForReportTemplateAccountGroup(
                                $this->ClientObj->id,
                                true
                            )
                            ->deprecated_waypoint_code)
                    {
                        $yearOverYearCollection->push(new YearOverYearPropertyGroup($yearOverYearCollectionItem));
                    }

                }
            };
        }
        return $yearOverYearCollection;
    }

    /**
     * @param $area
     * @param $type
     * @return string
     */
    private function getGroupSquareFootageField($area, $type)
    {
        if ( ! in_array($type, ['target', 'previous']))
        {
            throw new GeneralException('unusable year value');
        }

        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return $type == 'target' ? 'GROUP_SUM_RENTABLE_AREA2' : 'GROUP_SUM_RENTABLE_AREA1';
            case LedgerController::OCCUPIED_SELECTION:
                return $type == 'target' ? 'GROUP_SUM_OCCUPIED_AREA2' : 'GROUP_SUM_OCCUPIED_AREA1';
            case LedgerController::ADJUSTED_SELECTION:
                return $type == 'target' ? 'GROUP_SUM_ADJUSTED_AREA2' : 'GROUP_SUM_ADJUSTED_AREA1';
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return array
     */
    private function getYoyGroupAmountFieldsFromArea($area)
    {
        switch ($area)
        {
            case LedgerController::RENTABLE_SELECTION:
                return ['GROUP_RNT_YOY', 'CalcAmountRNTPerSqFt2', 'CalcAmountRNTPerSqFt1'];
            case LedgerController::OCCUPIED_SELECTION:
                return ['GROUP_OCC_YOY', 'CalcAmountOCCPerSqFt2', 'CalcAmountOCCPerSqFt1'];
            case LedgerController::ADJUSTED_SELECTION:
                return ['GROUP_ADJ_YOY', 'CalcAmountADJPerSqFt2', 'CalcAmountADJPerSqFt1'];
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return YearOverYear::class;
    }
}
