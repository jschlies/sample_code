<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\VariancePropertyGroup;
use App\Waypoint\Repositories\Ledger\VarianceRepository;
use DB;

/**
 * Class VariancePropertyGroupController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class VariancePropertyGroupDeprecatedController extends LedgerController
{
    /** @var  VarianceRepository */
    private $VarianceRepository;

    /** @var string */
    public $apiTitle = 'VariancePropertyGroup';

    /** @var string */
    public $apiDisplayName = 'Budget Variance';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'              => 'Account Name',
        'actual'            => 'Actual Amount ($/sq ft)',
        'budget'            => 'Budget Amount ($/sq ft)',
        'actualGrossAmount' => 'Actual Gross Amount',
        'budgetGrossAmount' => 'Budget Gross Amount',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '"$"0.00',
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /**
     * VariancePropertyGroupController constructor.
     * @param VarianceRepository $VarianceRepo
     */
    public function __construct(VarianceRepository $VarianceRepo)
    {
        parent::__construct($VarianceRepo);
        $this->VarianceRepository = $VarianceRepo;
    }

    /**
     * @param integer $property_group_id
     * @param integer $year
     * @param $period
     * @param $area
     * @param string $report_template_account_group_id
     * @param boolean $suppressResponse
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function index(
        $property_group_id,
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

        $this->entityName = $this->PropertyGroupObj->is_all_property_group
            ?
            self::DEFAULT_PORTFOLIO_NAME
            :
            $this->PropertyGroupObj->name;

        $account_code_list = [$this->ReportTemplateAccountGroupObj->deprecated_waypoint_code];

        if ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren)
        {
            foreach ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren as $child)
            {
                $account_code_list[] = $child->deprecated_waypoint_code;
            }
        }

        $group_calc_data_table    = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_FINAL_GROUP_ONLY_VARIANCE';
        $this->occupancy          = $this->VarianceRepository->getGroupAverageOccupancy(
            $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray(),
            $this->year
        );
        $this->targetPayloadSlice = $this->setDefaultTarget();
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($actualField, $budgetField, $varianceField) = $this->getGroupVarianceFieldsFromPeriodAndArea($this->period, $this->area);

        // get table name based on status
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($group_calc_data_table, $status) = $this->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj, $group_calc_data_table, true
        );

        if ($this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj))
        {
            $results = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $this->ClientObj->client_id_old)
                         ->table($group_calc_data_table)
                         ->where(
                             [
                                 ['FROM_YEAR', $this->year],
                                 ['REF_GROUP_ID', $this->PropertyGroupObj->id],
                             ]
                         )
                         ->whereRaw("!($actualField IS NULL AND $budgetField IS NULL)")
                         ->whereIn('ACCOUNT_CODE', $account_code_list)
                         ->select(
                             'REF_GROUP_ID as group_id',
                             'ACCOUNT_CODE as deprecated_code',
                             'FROM_YEAR as year',
                             "$actualField as actual",
                             "$budgetField as budget"
                         )
                         ->get();

            if (count($results) > 0)
            {
                foreach ($results as $result)
                {

                    $result                                     = (array) $result;
                    $result['LedgerController']                 = $this;
                    $result['entityOccupancy']                  = $this->occupancy;
                    $group_sum_area                             = $this->VarianceRepository->getGroupSumSquareFootage(
                        $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray(),
                        $this->year
                    );
                    $group_sum_rentable_area                    = $this->VarianceRepository->getGroupSumSquareFootage(
                        $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray(),
                        $this->year,
                        LedgerController::RENTABLE_SELECTION
                    );
                    $result['actual_gross_amount']              = (float) $result['actual'] * $group_sum_area;
                    $result['budget_gross_amount']              = (float) $result['budget'] * $group_sum_area;
                    $result['area']                             = (float) $group_sum_area;
                    $result['rentable_area']                    = (float) $group_sum_rentable_area;
                    $result['report_template_account_group_id'] = $this->getReportTemplateAccountGroupIdFromDeprecatedCode($result['deprecated_code']);
                    $result['native_account_type_coefficient']  = $this->ReportTemplateAccountGroupObj->nativeAccountType->nativeAccountTypeTrailers->first()->advanced_variance_coefficient;

                    if ($this->isTarget($result))
                    {
                        $this->targetPayloadSlice['actual']            = (float) $result['actual'];
                        $this->targetPayloadSlice['budget']            = (float) $result['budget'];
                        $this->targetPayloadSlice['actualGrossAmount'] = $result['actual_gross_amount'];
                        $this->targetPayloadSlice['budgetGrossAmount'] = $result['budget_gross_amount'];
                        $this->targetPayloadSlice['area']              = (float) $group_sum_area;
                        $this->targetPayloadSlice['rentable_area']     = (float) $group_sum_rentable_area;

                        if ($this->isTerminal())
                        {
                            $result['id']    = md5($result['group_id'] . $result['deprecated_code']);
                            $result['name']  = $this->ReportTemplateObj
                                ->reportTemplateAccountGroups
                                ->where('deprecated_waypoint_code', $result['deprecated_code'])
                                ->first()
                                ->display_name;
                            $result['code']  = $this->ReportTemplateObj
                                ->reportTemplateAccountGroups
                                ->where('deprecated_waypoint_code', $result['deprecated_code'])
                                ->first()
                                ->report_template_account_group_code;
                            $this->payload[] = new VariancePropertyGroup($result);
                        }
                    }
                    else
                    {
                        $result['id']    = md5($result['group_id'] . $result['deprecated_code']);
                        $result['name']  = $this->ReportTemplateObj
                            ->reportTemplateAccountGroups
                            ->where('deprecated_waypoint_code', $result['deprecated_code'])
                            ->first()
                            ->display_name;
                        $result['code']  = $this->ReportTemplateObj
                            ->reportTemplateAccountGroups
                            ->where('deprecated_waypoint_code', $result['deprecated_code'])
                            ->first()
                            ->report_template_account_group_code;
                        $this->payload[] = new VariancePropertyGroup($result);
                    }
                }
            }
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
                'variance benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @param $result
     * @return bool
     */
    private function isTarget($result): bool
    {
        return $result['deprecated_code'] == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
    }

    /**
     * @return bool
     */
    private function isTerminal(): bool
    {
        return $this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() == 0;
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function setDefaultTarget(): array
    {
        return [
            'apiTitle'        => $this->apiTitle,
            'name'            => $this->ReportTemplateAccountGroupObj->display_name,
            'code'            => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'fromDate'        => $this->getFromDate($this->year, $this->period),
            'toDate'          => $this->getToDate($this->year, $this->period),
            'period'          => $this->period,
            'entityName'      => $this->entityName,
            'units'           => $this->units,
            'entityOccupancy' => $this->occupancy,
        ];
    }
}
