<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\OperatingExpensesRanking;
use App\Waypoint\Models\Ledger\OperatingExpensesPropertyGroupRanking;
use App\Waypoint\Models\Ledger\OperatingExpensesPropertyRanking;
use App\Waypoint\Models\PropertyGroup;
use DB;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class OperatingExpensesRankingRepository
 */
class OperatingExpensesRankingRepository extends LedgerRepository
{
    const AREA_RENTABLE_FIELD = 'INDIVIDUAL_RENTABLE_AREA_FROM_PYTHON';
    const AREA_OCCUPIED_FIELD = 'INDIVIDUAL_OCCUPIED_AREA_FROM_PYTHON';
    const AREA_ADJUSTED_FIELD = 'INDIVIDUAL_ADJUSTED_AREA_FROM_PYTHON';
    const COLOR_RENTABLE      = 'COLOR_RNT';
    const COLOR_OCCUPIED      = 'COLOR_OCC';
    const COLOR_ADJUSTED      = 'COLOR_ADJ';

    /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
    public $ReportTemplateAccountGroupObj;

    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @return Collection
     * @throws GeneralException
     */
    public function getPropertyData(ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
    {
        if ( ! $this->LedgerControllerObj)
        {
            throw new GeneralException('ledger controller object missing');
        }

        if ( ! $this->ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupObj)
        {
            throw new GeneralException('boma coa line item object missing');
        }

        // get all property ids from the all property group
        if ( ! $this->UserAllPropertyGroup = $this->LedgerControllerObj->getUserObject()->allPropertyGroup)
        {
            throw new GeneralException('property group missing', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        /** @var \App\Waypoint\Collection $payload */
        $payload             = new Collection();
        $property_id_old_arr = $this->UserAllPropertyGroup->getAllProperties()->pluck('property_id_old')->toArray();

        $results = $this->getLedgerDatabaseConnection()
                        ->table('BENCHMARK_LEVELS')
                        ->where(
                            [
                                ['BENCHMARK_LEVELS.FROM_YEAR', $this->year],
                                ['BENCHMARK_LEVELS.BENCHMARK_TYPE', $this->LedgerControllerObj->getBenchmarkType($this->report, $this->period)],
                            ]
                        )
                        ->whereIn(
                            'BENCHMARK_LEVELS.ACCOUNT_CODE',
                            [
                                $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                                $this->ReportTemplateAccountGroupObj
                                    ->nativeAccountType->getUltimateParentForReportTemplateAccountGroup(
                                        $this->ClientObj->id,
                                        true
                                    )->deprecated_waypoint_code,
                            ]
                        )
                        ->whereIn('CLIENT_BENCHMARKS.FK_PROPERTY_ID', $property_id_old_arr)
                        ->join('CLIENT_BENCHMARKS', 'BENCHMARK_LEVELS.BENCHMARK_ID', '=', 'CLIENT_BENCHMARKS.FK_BENCHMARK_ID')
                        ->select(
                            'CLIENT_BENCHMARKS.FK_PROPERTY_ID as property_id',
                            'CLIENT_BENCHMARKS.ACCOUNT_CODE as code',
                            'CLIENT_BENCHMARKS.PROPERTY_NAME as name',
                            'CLIENT_BENCHMARKS.' . $this->getAmountField(true) . ' as amount',
                            'CLIENT_BENCHMARKS.' . $this->getRentableAmountField() . ' as rentable_amount',
                            'CLIENT_BENCHMARKS.' . $this->getColorField() . ' as color',
                            'CLIENT_BENCHMARKS.' . $this->getPropertyAreaField() . ' as area',
                            'CLIENT_BENCHMARKS.' . $this->getRentableAreaField() . ' as rentable_area'
                        )
                        ->orderBy('amount')
                        ->get();

        if ($results->count() > 0)
        {
            $rankCounter                            = 1;
            $complete_results                       = $this->filterIncompleteDataResults($results);
            $occupancy_by_property_arr              = $this->getOccupancyForEachProperty($property_id_old_arr, $this->year);
            $this->incomplete_property_id_old_arr   = array_diff($property_id_old_arr, $complete_results->pluck('property_id')->toArray());
            $this->incomplete_data_properties_count = count($this->incomplete_property_id_old_arr);

            if ($complete_results->count() > 0)
            {
                foreach ($complete_results as $result)
                {
                    $occupancy   = isset($occupancy_by_property_arr[$result->property_id]) && ! $this->renaming_occupancy_table ? $occupancy_by_property_arr[$result->property_id] : 0;
                    $payloadItem = [
                        'LedgerController' => $this->LedgerControllerObj,
                        'property_id'      => $result->property_id,
                        'id'               => md5(
                            $result->property_id . $this->year . $this->report . $this->period . $this->area . $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code
                        ),
                        'client_id'        => $this->ClientObj->id,
                        'code'             => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'year'             => $this->year,
                        'type'             => $this->report,
                        'period'           => $this->period,
                        'area'             => $result->area,
                        'areaType'         => strtolower($this->area),
                        'amount'           => $this->calculateAmount($result, $occupancy),
                        'rank'             => $rankCounter++,
                        'occupancy'        => $occupancy,
                        'gross_amount'     => $result->rentable_amount * $result->rentable_area,
                        'rentable_area'    => $result->rentable_area,
                    ];

                    $payload[] = new OperatingExpensesPropertyRanking($payloadItem);
                }
            }

            // add incomplete or missing properties for this particular account code
            if ($payload->count() > 0)
            {
                foreach ($this->incomplete_property_id_old_arr as $incomplete_property_id_old)
                {
                    $occupancy                       = isset($occupancy_by_property_arr[$incomplete_property_id_old]) && ! $this->renaming_occupancy_table ? $occupancy_by_property_arr[$incomplete_property_id_old] : 0;
                    $incomplete_property_details_arr = [
                        'LedgerController' => $this->LedgerControllerObj,
                        'id'               => md5($incomplete_property_id_old . $this->year . $this->report . $this->period . $this->area . $this->ReportTemplateAccountGroupObj->report_template_account_group_code),
                        'client_id'        => $this->ClientObj->id,
                        'property_id'      => $incomplete_property_id_old,
                        'code'             => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'year'             => $this->year,
                        'type'             => $this->report,
                        'period'           => $this->period,
                        'area'             => $this->area,
                        'amount'           => null,
                        'rank'             => 0,
                        'occupancy'        => $occupancy,
                        'gross_amount'     => 0,
                    ];
                    $payload[]                       = new OperatingExpensesPropertyRanking($incomplete_property_details_arr);
                }
            }
        }
        return $payload;
    }

    /**
     * @param LedgerController $LedgerControllerObj
     * @param Client $ClientObj
     * @param PropertyGroup $PropertyGroupObj
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @return \App\Waypoint\Collection|array
     */
    public function getGroupData($LedgerControllerObj, Client $ClientObj, PropertyGroup $PropertyGroupObj, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
    {
        $payload                            = new Collection();
        $property_group_property_id_old_arr = $PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $benchmarkType                      = $LedgerControllerObj->getBenchmarkType($this->report, $this->period);
        $this->DatabaseConnectionObj        = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $ClientObj->client_id_old);
        $group_calc_data_table              = 'GROUP_CALC_CLIENT_' . $ClientObj->client_id_old . '_YEARLY_FINAL';
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($amountField, $rankField) = $LedgerControllerObj->getFields($this->area, true);
        list($group_calc_data_table, $LedgerControllerObj->status) = $LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus($ClientObj, $group_calc_data_table, true);

        $results = $this->DatabaseConnectionObj
            ->table($group_calc_data_table)
            ->where(
                [
                    ['FROM_YEAR', $this->year],
                    ['BENCHMARK_TYPE', $benchmarkType],
                    ['REF_GROUP_ID', $PropertyGroupObj->id],
                ]
            )
            ->whereIn('FK_PROPERTY_ID', $property_group_property_id_old_arr)
            ->whereIn('ACCOUNT_CODE', [
                $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                $this->ReportTemplateAccountGroupObj
                    ->nativeAccountType
                    ->getUltimateParentForReportTemplateAccountGroup(
                        $this->ClientObj->id,
                        true)
                    ->deprecated_waypoint_code,
            ])
            ->select(
                'FK_PROPERTY_ID as property_id',
                'ACCOUNT_CODE as code',
                "$amountField as amount",
                $this->getColorField() . ' as color',
                $this->getPythonPropertyAreaField() . ' as area',
                $this->getPythonPropertyAreaField('RENTABLE') . ' as rentable_area'
            )
            ->orderBy('amount', 'asc')
            ->get();

        if (count($results) > 0)
        {
            $this->ClientObj                        = $ClientObj;
            $this->square_footage_lookup_arr        = $this->getOccupancyForEachProperty($property_group_property_id_old_arr, $this->year, true);
            $this->PropertyGroupObj                 = $PropertyGroupObj;
            $completeResults                        = $this->filterIncompleteDataResults($results, $ReportTemplateAccountGroupObj);
            $this->incomplete_property_id_old_arr   = array_diff($property_group_property_id_old_arr, $completeResults->pluck('property_id')->toArray());
            $this->incomplete_data_properties_count = count($this->incomplete_property_id_old_arr);

            if ($completeResults->count() > 0)
            {
                // create payload items for all complete results
                $rankCounter = 1;
                foreach ($completeResults as $result)
                {
                    $property = [
                        'LedgerController' => $LedgerControllerObj,
                        'PropertyGroup'    => $PropertyGroupObj,
                        'id'               => md5($result->property_id . $this->year . $this->report . $this->period . $this->area . $ReportTemplateAccountGroupObj->code),
                        'client_id'        => $ClientObj->id,
                        'code'             => $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                        'year'             => $this->year,
                        'type'             => $this->report,
                        'period'           => $this->period,
                        'area'             => (float) $result->area,
                        'areaType'         => $this->area,
                        'amount'           => (float) $result->amount,
                        'occupancy'        => $this->getOccupancyFromSquareFootage(
                            $this->square_footage_lookup_arr[$result->property_id]['RENTABLE_AREA'], $this->square_footage_lookup_arr[$result->property_id]['OCCUPIED_AREA']
                        ),
                        'rank'             => $rankCounter++,
                        'property_id'      => $result->property_id,
                        'gross_amount'     => $result->amount * $result->area,
                        'rentable_area'    => (float) $result->rentable_area,
                    ];

                    $payload[] = new OperatingExpensesPropertyGroupRanking($property);
                }
            }
        }

        // if payload is empty, just return an empty collection rather than a collection full of incomplete results
        if ($payload->count() > 0)
        {
            // create a result for each incomplete property
            foreach ($this->incomplete_property_id_old_arr as $property_id)
            {
                $property  = [
                    'LedgerController' => $LedgerControllerObj,
                    'id'               => md5($property_id . $this->year . $this->report . $this->period . $this->area . $ReportTemplateAccountGroupObj->deprecated_waypoint_code),
                    'client_id'        => $ClientObj->id,
                    'property_id'      => $property_id,
                    'code'             => $ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                    'year'             => $this->year,
                    'type'             => $this->report,
                    'period'           => $this->period,
                    'area'             => $this->area,
                    'rank'             => 0,
                    'gross_amount'     => 0,
                ];
                $payload[] = new OperatingExpensesPropertyGroupRanking($property);
            }
        }

        return $payload;
    }

    private function calculateAmount($data, $occupancy)
    {
        if ($this->area == LedgerController::OCCUPIED_SELECTION)
        {
            return $occupancy == 0 ? 0 : (float) $data->rentable_amount / ($occupancy / 100);
        }
        return (float) $data->amount;
    }

    /**
     * @param bool $double
     * @return string
     */
    private function getAmountField($double = false)
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }

        return $double ? 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area] . '_DOUBLE' : 'AMOUNT_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    private function getRentableAmountField(): string
    {
        return 'AMOUNT_' . LedgerController::AREA_LOOKUP[LedgerController::RENTABLE_SELECTION] . '_DOUBLE';
    }

    /**
     * @param $area
     * @param $type
     * @return string
     */
    private function getColorField()
    {
        if ( ! $this->usableArea())
        {
            throw new GeneralException('unusable area given');
        }
        return 'COLOR_' . LedgerController::AREA_LOOKUP[$this->area];
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return OperatingExpensesRanking::class;
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField(): string
    {
        if ( ! $this->area && in_array($this->area, LedgerController::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return $this->area . '_AREA';
    }

    private function getRentableAreaField(): string
    {
        return LedgerController::RENTABLE_SELECTION . '_AREA';
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPythonPropertyAreaField($area = null): string
    {
        if ($area)
        {
            return 'INDIVIDUAL_' . $area . '_AREA_FROM_PYTHON';
        }
        if ( ! $this->area && in_array($this->area, LedgerController::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'INDIVIDUAL_' . $this->area . '_AREA_FROM_PYTHON';
    }
}
