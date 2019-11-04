<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\VariancePropertyGroupRanking;
use App\Waypoint\Models\Ledger\VariancePropertyRanking;
use App\Waypoint\Models\Ledger\VarianceRanking;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Illuminate\Support\Collection;

/**
 * Class VarianceRankingRepository
 */
class VarianceRankingRepository extends LedgerRepository
{
    /** @var array $fieldSearchable */
    protected $fieldSearchable = [];

    /** @var null|ReportTemplateAccountGroupRepository */
    public $ReportTemplateAccountGroupRepositoryObj = null;

    /** Configure the Repository */
    public function model()
    {
        return VarianceRanking::class;
    }

    const COLOR_RENTABLE_YEAR_TO_DATE  = 'COLOR_VARIANCE_RNT_YTD';
    const COLOR_RENTABLE_CALENDAR_YEAR = 'COLOR_VARIANCE_RNT_CY';
    const COLOR_RENTABLE_TRAILING_12   = 'COLOR_VARIANCE_RNT_T12';
    const COLOR_OCCUPIED_YEAR_TO_DATE  = 'COLOR_VARIANCE_OCC_YTD';
    const COLOR_OCCUPIED_CALENDAR_YEAR = 'COLOR_VARIANCE_OCC_CY';
    const COLOR_OCCUPIED_TRAILING_12   = 'COLOR_VARIANCE_OCC_T12';
    const COLOR_ADJUSTED_YEAR_TO_DATE  = 'COLOR_VARIANCE_ADJ_YTD';
    const COLOR_ADJUSTED_CALENDAR_YEAR = 'COLOR_VARIANCE_ADJ_CY';
    const COLOR_ADJUSTED_TRAILING_12   = 'COLOR_VARIANCE_ADJ_T12';
    const RENTABLE_AREA                = 'INDIVIDUAL_RENTABLE_AREA_FROM_PYTHON';
    const OCCUPIED_AREA                = 'INDIVIDUAL_OCCUPIED_AREA_FROM_PYTHON';
    const ADJUSTED_AREA                = 'INDIVIDUAL_ADJUSTED_AREA_FROM_PYTHON';

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getPropertyData(ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
    {
        $this->ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupObj;

        $this->checkForNecessaryInputData();

        if ( ! $this->UserAllPropertyGroup = $this->LedgerControllerObj->getUserObject()->allPropertyGroup)
        {
            throw new GeneralException('property group missing', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        $payload                    = new Collection();
        $property_id_old_arr        = $this->UserAllPropertyGroup->getAllProperties()->pluck('property_id_old')->toArray();
        $this->perPropertyOccupancy = $this->getOccupancyForEachProperty($property_id_old_arr, $this->year);

        $results = $this->performPropertyDatabaseQuery($property_id_old_arr);

        if ($results->count() > 0)
        {
            $this->square_footage_lookup_arr      = $this->getOccupancyForEachProperty($property_id_old_arr, $this->year, true);
            $complete_data_results                = $this->filterIncompleteDataResults($results);
            $this->incomplete_property_id_old_arr = array_diff($property_id_old_arr, $complete_data_results->pluck('property_id')->toArray());

            if ($complete_data_results->count() > 0)
            {
                foreach ($complete_data_results->pluck('property_id')->unique() as $property_id_old)
                {
                    $results_for_single_property = $complete_data_results->filter(
                        function ($item) use ($property_id_old)
                        {
                            return $item->property_id == $property_id_old;
                        }
                    );

                    if ($results_for_single_property->count() != 3)
                    {
                        $this->incomplete_property_id_old_arr[] = $property_id_old;
                        continue;
                    }

                    $sorted_results_for_single_property = $results_for_single_property->sortBy('report');
                    $entity_occupancy                   = isset($this->square_footage_lookup_arr[$property_id_old]) && ! $this->renaming_occupancy_table ? $this->getOccupancyFromSquareFootage(
                        $this->square_footage_lookup_arr[$property_id_old]['RENTABLE_AREA'], $this->square_footage_lookup_arr[$property_id_old]['OCCUPIED_AREA']
                    ) : 0;

                    $payload_item_arr = [
                        'LedgerController'                 => $this->LedgerControllerObj,
                        'id'                               => md5($property_id_old . $sorted_results_for_single_property->first()->benchmark_id),
                        'client_id'                        => $this->ClientObj->id,
                        'name'                             => $sorted_results_for_single_property->first()->name,
                        'property_id'                      => $property_id_old,
                        'code'                             => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'varianceAmount'                   => $this->calculateAmount($sorted_results_for_single_property->slice(1, 1)->first(), $entity_occupancy),
                        'actualAmount'                     => $this->calculateAmount($sorted_results_for_single_property->first(), $entity_occupancy),
                        'budgetAmount'                     => $this->calculateAmount($sorted_results_for_single_property->last(), $entity_occupancy),
                        'targetYear'                       => $this->year,
                        'targetYearAmount'                 => $sorted_results_for_single_property->slice(1, 1)->first()->amount,
                        'rank'                             => null,
                        'entityOccupancy'                  => $entity_occupancy,
                        'actual_gross_amount'              => $sorted_results_for_single_property->first()->rentable_amount * $sorted_results_for_single_property->first()->rentable_area,
                        'budget_gross_amount'              => $sorted_results_for_single_property->last()->rentable_amount * $sorted_results_for_single_property->last()->rentable_area,
                        'rentable_area'                    => $sorted_results_for_single_property->last()->rentable_area,
                        'report_template_account_group_id' => $ReportTemplateAccountGroupObj->id,
                    ];

                    $payload[] = new VariancePropertyRanking($payload_item_arr);
                }
                $this->incomplete_data_properties_count = count($this->incomplete_property_id_old_arr);
            }

            if ($payload->count() > 0)
            {
                // set rank
                $rankCounter = 1;
                foreach ($payload->sortBy('varianceAmount') as $payload_item)
                {
                    $payload_item->rank = $rankCounter++;
                }

                // process incomplete
                foreach ($this->incomplete_property_id_old_arr as $property_id_old)
                {
                    $entity_occupancy         = isset($this->square_footage_lookup_arr[$property_id_old]) ? $this->getOccupancyFromSquareFootage(
                        $this->square_footage_lookup_arr[$property_id_old]['RENTABLE_AREA'], $this->square_footage_lookup_arr[$property_id_old]['OCCUPIED_AREA']
                    ) : 0;
                    $incomplete_data_property = [
                        'LedgerController' => $this->LedgerControllerObj,
                        'id'               => md5($property_id_old . $this->year . $this->period . $this->area . $ReportTemplateAccountGroupObj->report_template_account_group_code),
                        'client_id'        => $this->ClientObj->id,
                        'property_id'      => $property_id_old,
                        'code'             => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'year'             => $this->year,
                        'period'           => $this->period,
                        'area'             => $this->area,
                        'amount'           => null,
                        'rank'             => 0,
                        'entityOccupancy'  => $entity_occupancy,
                    ];
                    $payload[]                = new VariancePropertyRanking($incomplete_data_property);
                }
            }
        }
        return $payload;
    }

    /**
     * @param LedgerController $LedgerControllerObj
     * @param Client $ClientObj
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @return \App\Waypoint\Collection|array|Collection
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getGroupData($LedgerControllerObj, Client $ClientObj, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
    {
        /** @var \App\Waypoint\Collection $payload */
        $payload = new Collection();

        $property_id_old_arr   = $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $group_calc_data_table = 'GROUP_CALC_CLIENT_' . $ClientObj->client_id_old . '_YEARLY_FINAL_VARIANCE';
        list($group_calc_data_table, $LedgerControllerObj->status) = $LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus($ClientObj, $group_calc_data_table, true);
        $results = $this->performGroupDatabaseQuery(
            $group_calc_data_table,
            $property_id_old_arr,
            $ReportTemplateAccountGroupObj
        );

        // process results
        if (count($results) > 0)
        {
            $this->ClientObj                        = $ClientObj;
            $this->square_footage_lookup_arr        = $this->getOccupancyForEachProperty($property_id_old_arr, $this->year, true);
            $complete_results                       = $this->filterIncompleteDataResults($results, $ReportTemplateAccountGroupObj);
            $this->incomplete_property_id_old_arr   = array_diff($property_id_old_arr, $complete_results->pluck('property_id')->toArray());
            $this->incomplete_data_properties_count = count($this->incomplete_property_id_old_arr);

            $rankCounter = 1;
            foreach ($complete_results->sortBy('variance_amount') as $result)
            {
                $property = [
                    'LedgerController'    => $LedgerControllerObj,
                    'id'                  => md5($result->property_id . $rankCounter),
                    'property_id'         => $result->property_id,
                    'client_id'           => $ClientObj->id,
                    'code'                => $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                    'entityOccupancy'     => $this->getOccupancyFromSquareFootage(
                        $this->square_footage_lookup_arr[$result->property_id]['RENTABLE_AREA'],
                        $this->square_footage_lookup_arr[$result->property_id]['OCCUPIED_AREA']
                    ),
                    'varianceAmount'      => $result->variance_amount,
                    'budgetAmount'        => $result->budget_amount,
                    'actualAmount'        => $result->actual_amount,
                    'actual_gross_amount' => $result->actual_amount * $result->area,
                    'budget_gross_amount' => $result->budget_amount * $result->area,
                    'rank'                => $rankCounter++,
                    'area'                => $result->area,
                    'rentable_area'       => $result->rentable_area,
                ];

                $payload[] = new VariancePropertyGroupRanking($property);
            }
        }

        if ($payload->count() > 0)
        {
            foreach ($this->incomplete_property_id_old_arr as $property_id_old)
            {
                $property  = [
                    'LedgerController' => $LedgerControllerObj,
                    'id'               => md5($property_id_old . $LedgerControllerObj->year . $this->period . $this->area . $ReportTemplateAccountGroupObj->code),
                    'client_id'        => $ClientObj->id,
                    'property_id'      => $property_id_old,
                    'code'             => $ReportTemplateAccountGroupObj->code,
                    'year'             => $LedgerControllerObj->year,
                    'period'           => $this->period,
                    'area'             => $this->area,
                    'rank'             => 0,
                ];
                $payload[] = new VariancePropertyGroupRanking($property);
            }
        }
        return $payload;
    }

    /**
     * @param $property_id_old_arr
     * @return Collection
     * @throws GeneralException
     */
    private function performPropertyDatabaseQuery($property_id_old_arr)
    {
        return $this->getLedgerDatabaseConnection()
                    ->table('BENCHMARK_LEVELS')
                    ->where('BENCHMARK_LEVELS.FROM_YEAR', $this->year)
                    ->whereIn(
                        'BENCHMARK_LEVELS.ACCOUNT_CODE',
                        [
                            $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                            $this->ReportTemplateAccountGroupObj
                                ->nativeAccountType
                                ->getUltimateParentForReportTemplateAccountGroup(
                                    $this->ClientObj->id,
                                    true
                                )->deprecated_waypoint_code,
                        ]
                    )
                    ->whereIn('BENCHMARK_LEVELS.BENCHMARK_TYPE', $this->LedgerControllerObj->getBenchmarkTypes($this->period))
                    ->whereIn('FK_PROPERTY_ID', $property_id_old_arr)
                    ->whereNotNull($this->getLedgerAmountField(true))
                    ->join('CLIENT_BENCHMARKS', 'BENCHMARK_LEVELS.BENCHMARK_ID', '=', 'CLIENT_BENCHMARKS.FK_BENCHMARK_ID')
                    ->select(
                        'CLIENT_BENCHMARKS.PROPERTY_NAME as name',
                        'CLIENT_BENCHMARKS.' . $this->getLedgerAmountField(true) . ' as amount',
                        'CLIENT_BENCHMARKS.' . LedgerController::AMOUNT_RENTABLE_DOUBLE_FIELD . ' as rentable_amount',
                        'CLIENT_BENCHMARKS.ACCOUNT_CODE as code',
                        'CLIENT_BENCHMARKS.BENCHMARK_TYPE as report',
                        'CLIENT_BENCHMARKS.' . $this->getLedgerColorField() . ' as color',
                        'CLIENT_BENCHMARKS.FK_BENCHMARK_ID as benchmark_id',
                        'CLIENT_BENCHMARKS.FK_PROPERTY_ID as property_id',
                        'CLIENT_BENCHMARKS.' . $this->getPropertyAreaField() . ' as area',
                        'CLIENT_BENCHMARKS.' . $this->getPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_area'
                    )
                    ->get();
    }

    /**
     * @param $group_calc_data_table
     * @param $property_id_old_arr
     * @param $ReportTemplateAccountGroupObj
     * @return Collection
     * @throws GeneralException
     */
    private function performGroupDatabaseQuery(
        $group_calc_data_table,
        $property_id_old_arr,
        $ReportTemplateAccountGroupObj
    ) {
        return $this->getGroupDatabaseConnection()
                    ->table($group_calc_data_table)
                    ->where(
                        [
                            ['FROM_YEAR', $this->year],
                            ['REF_GROUP_ID', $this->PropertyGroupObj->id],
                        ]
                    )
                    ->whereIn('FK_PROPERTY_ID', $property_id_old_arr)
                    ->whereIn(
                        'ACCOUNT_CODE',
                        [
                            $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                            $this->ReportTemplateAccountGroupObj
                                ->nativeAccountType
                                ->getUltimateParentForReportTemplateAccountGroup(
                                    $this->ClientObj->id,
                                    true
                                )
                                ->deprecated_waypoint_code,
                        ]
                    )
                    ->whereNotNull($this->getVarianceField())
                    ->whereNotNull($this->getGroupAmountField(LedgerController::ACTUAL))
                    ->whereNotNull($this->getGroupAmountField(LedgerController::BUDGET))
                    ->select(
                        'PROPERTY_NAME as name',
                        $this->getVarianceField() . ' as variance_amount',
                        'ACCOUNT_CODE as code',
                        $this->getGroupColorField() . ' as color',
                        'FK_PROPERTY_ID as property_id',
                        $this->getGroupAmountField(LedgerController::ACTUAL) . ' as actual_amount',
                        $this->getGroupAmountField(LedgerController::BUDGET) . ' as budget_amount',
                        $this->getPythonPropertyAreaField() . ' as area',
                        $this->getPythonPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_area'
                    )
                    ->get();
    }

    /**
     * @param $data
     * @param $occupancy
     * @return float|int
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
     * @return bool
     */
    private function checkForNecessaryInputData()
    {
        if ( ! $this->LedgerControllerObj)
        {
            throw new GeneralException('ledger controller object missing');
        }
        if ( ! $this->ClientObj)
        {
            throw new GeneralException('client object missing');
        }
        if ( ! $this->ReportTemplateAccountGroupObj)
        {
            throw new GeneralException('boma coa line item object missing');
        }
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        if ( ! $this->usablePeriod())
        {
            throw new GeneralException('unusable period given');
        }
        if ( ! $this->usableReport())
        {
            throw new GeneralException('unusable report given');
        }
        return true;
    }

    /**
     * @param bool $double
     * @return string
     */
    private function getLedgerAmountField($double = false)
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        return $double ? 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area] . '_DOUBLE' : 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    /**
     * @param $report
     * @return string
     * @throws GeneralException
     */
    private function getGroupAmountField($report)
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        if ( ! $this->usablePeriod())
        {
            throw new GeneralException('unusable period given');
        }
        if ( ! in_array($report, LedgerController::ACCEPTABLE_REPORTS))
        {
            throw new GeneralException('unusable report given');
        }
        return $this->period == LedgerController::CALENDAR_YEAR_ABBREV ? 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area] . '_' . strtoupper(
                $report
            ) : 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area] . '_' . strtoupper($report) . '_' . LedgerController::PERIOD_LOOKUP[$this->period];
    }

    /**
     * @return string
     * @throws GeneralException
     */
    private function getVarianceField()
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        if ( ! $this->usablePeriod())
        {
            throw new GeneralException('unusable period given');
        }
        return $this->period == LedgerController::CALENDAR_YEAR_ABBREV ? 'VARIANCE_' . LedgerController::AREA_LOOKUP[$this->area] : 'VARIANCE_' . LedgerController::AREA_LOOKUP[$this->area] . '_' . LedgerController::PERIOD_LOOKUP[$this->period];
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField(): string
    {
        if (func_num_args() > 0)
        {
            $area = func_get_arg(0);
            if ( ! $this->usableArea($area))
            {
                throw new GeneralException('unsuable area given');
            }
            return strtoupper($area) . '_AREA';
        }

        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        return strtoupper($this->area) . '_AREA';
    }

    /**
     * @return string
     */
    private function getLedgerColorField()
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unsuable area given');
        }
        return 'COLOR_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    /**
     * @return string
     * @throws GeneralException
     */
    private function getGroupColorField()
    {
        if ( ! $this->usablePeriod())
        {
            throw new GeneralException('unusable period given');
        }
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unsuable area given');
        }
        return $this->period == LedgerController::CALENDAR_YEAR_ABBREV ? 'COLOR_VARIANCE_' . LedgerController::AREA_LOOKUP[$this->area] : 'COLOR_VARIANCE_' . LedgerController::AREA_LOOKUP[$this->area] . '_' . LedgerController::PERIOD_LOOKUP[$this->period];
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPythonPropertyAreaField($area = null)
    {
        if ($area)
        {
            return 'INDIVIDUAL_' . $area . '_AREA_FROM_PYTHON';
        }
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable report given');
        }
        return 'INDIVIDUAL_' . $this->area . '_AREA_FROM_PYTHON';
    }
}
