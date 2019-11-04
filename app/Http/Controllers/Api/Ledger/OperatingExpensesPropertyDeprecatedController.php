<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\OperatingExpensesProperty;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRepository;
use function array_merge;
use function in_array;

/**
 * Class OperatingExpensesPropertyController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class OperatingExpensesPropertyDeprecatedController extends LedgerController
{
    /** @var  OperatingExpensesRepository */
    private $OperatingExpensesRepository;

    /** @var string */
    public $apiTitle = 'OperatingExpensesProperty';

    /** @var string */
    public $apiDisplayName = 'Account Breakdown';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    protected $grand_children_account_codes = [];

    protected $children_account_codes = [];

    protected $target_account_code = null;

    protected $grandchildren_to_children_lookup = [];

    protected $result = null;

    protected $grandchild_count = [];

    protected $deprecated_to_nondeprecated_account_lookup = [];

    protected $target_result = null;

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '"$"0.00',
            'C' => '"$"#,##0.00',
            'D' => '"$"0.00',
            'F' => '0.00"%"',
        ],
    ];

    /**
     * OperatingExpensesPropertyController constructor.
     * @param OperatingExpensesRepository $OperatingExpensesRepo
     */
    public function __construct(OperatingExpensesRepository $OperatingExpensesRepo)
    {
        $this->OperatingExpensesRepository = $OperatingExpensesRepo;
        parent::__construct($OperatingExpensesRepo);
    }

    /**
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
     * @throws \BadMethodCallException
     */
    public function index(
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
                'year'   => $year,
                'period' => $period,
                'area'   => $area,
                'report' => $report,
            ],
            $this->OperatingExpensesRepository
        );
        $this->initializeEssentialIngredients();
        $this->setGenerationalAccountCodes();
        $this->getTargetPayloadSlice();

        $this->entityName = $this->PropertyObj->name;
        $this->occupancy  = $this->OperatingExpensesRepository->getOccupancyForSingleProperty(
            $this->PropertyObj->property_id_old,
            $this->year
        );

        if ($this->checkForAvailableDataGivenPeriodAndYear($period, $year, $this->ClientObj))
        {
            $results = $this->perform_query(true);
            $this->package_data($results);
        }
        else
        {
            $this->warnings[] = 'no data for this time period';
        }

        // suppression of the normal response is used for combined spreadsheet generation only
        if ($suppressResponse)
        {
            return [
                'data'            => $this->payload->toArray(),
                'metadata'        => $this->getMetadata($this->payload),
                'transformations' => $this->getSpreadsheetFields(),
            ];
        }
        else
        {
            return $this->sendResponse(
                $this->payload->toArray(),
                'operating expense benchmark data generated successfully',
                $this->errors,
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @param bool $enable_query_log
     * @return \Illuminate\Support\Collection
     */
    protected function perform_query($enable_query_log = false)
    {
        $this->DatabaseConnection = DatabaseConnectionRepository::getLedgerDatabaseConnection($this->ClientObj, $enable_query_log);

        $results = $this->DatabaseConnection
            ->table('CLIENT_BENCHMARKS')
            ->where(
                [
                    ['FROM_YEAR', (int) $this->year],
                    ['BENCHMARK_TYPE', (string) $this->getBenchmarkType($this->report, $this->period)],
                    ['FK_PROPERTY_ID', (int) $this->PropertyObj->property_id_old],
                ]
            )
            ->whereIn('ACCOUNT_CODE', $this->getDeprecatedAccountCodes())
            ->select(
                'FK_BENCHMARK_ID as benchmark_id',
                'FK_PROPERTY_ID as property_id',
                $this->getAmountFieldFromArea($this->area, true) . ' as amount',
                'ACCOUNT_CODE as ' . $this->report_template_account_group_code_field_name,
                'ACCOUNT_NAME_UPPER as name',
                'ASOF_MONTH as month',
                $this->getAmountFieldFromArea(LedgerController::RENTABLE_SELECTION, true) . ' as rentable_amount',
                $this->getPropertyAreaField() . ' as area',
                $this->getPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_area'
            )
            ->get();

        if ($enable_query_log)
        {
            $this->prepareQueryLog();
        }

        return $results;
    }

    /**
     * @param $results
     * @throws GeneralException
     */
    protected function package_data($results)
    {
        if (count($results) > 0)
        {
            foreach ($results as $this->result)
            {
                $this->result                     = (array) $this->result; // cast object as array
                $this->result['entityOccupancy']  = ! $this->OperatingExpensesRepository->renaming_occupancy_table ? $this->occupancy : 0;
                $this->result['grossAmount']      = (float) $this->result['rentable_amount'] * (float) $this->result['rentable_area']; // occupied & adjusted gr amts = rentable gr amt
                $this->result['areaType']         = strtolower($this->area);
                $this->result['rentable_area']    = (float) $this->result['rentable_area'];
                $this->result['LedgerController'] = $this;
                $this->result['id']               = md5($this->result['property_id'] . $this->result['benchmark_id']);
                $this->result['amount']           = $this->calculateAmount($this->result);
                $this->result['report_template_account_group_id']
                                                  = $this->getReportTemplateAccountGroupIdFromDeprecatedCode($this->result[$this->report_template_account_group_code_field_name]);

                if ($this->isTargetAccount())
                {
                    $this->target_result = $this->result;
                    $this->getTargetPayloadSlice(
                        [
                            'amount'                           => $this->calculateAmount($this->result),
                            'rentable_area'                    => (float) $this->result['rentable_area'],
                            'grossAmount'                      => $this->result['grossAmount'],
                            'entityOccupancy'                  => $this->result['entityOccupancy'],
                            'report_template_account_group_id' => $this->ReportTemplateAccountGroupObj->id,
                        ]
                    );
                }
                if ($this->isChildAccount())
                {
                    $children_result_arr[] = $this->result;
                }
                elseif ($this->isGrandchildAccount())
                {
                    $parent_of_grandchild = $this->grandchildren_to_children_lookup[$this->result[$this->report_template_account_group_code_field_name]];
                    isset($this->grandchild_count[$parent_of_grandchild]) ? $this->grandchild_count[$parent_of_grandchild]++ : $this->grandchild_count[$parent_of_grandchild] = 1;
                }
            }

            if (empty($children_result_arr))
            {
                $children_result_arr[] = $this->target_result;
            }

            foreach ($children_result_arr as $child_result)
            {
                $child_result['childCount']
                    = isset($child_result[$this->report_template_account_group_code_field_name])
                      &&
                      isset($this->grandchild_count[$child_result[$this->report_template_account_group_code_field_name]])
                    ?
                    $this->grandchild_count[$child_result[$this->report_template_account_group_code_field_name]]
                    :
                    0;

                $child_result['code']
                                 = $this->deprecated_to_nondeprecated_account_lookup[$child_result[$this->report_template_account_group_code_field_name]];
                $this->payload[] = new OperatingExpensesProperty($child_result);
            }
        }
    }

    /**
     * @return array
     */
    protected function getDeprecatedAccountCodes()
    {
        return array_merge(
            [$this->target_account_code],
            $this->children_account_codes,
            $this->grand_children_account_codes
        );
    }

    /**
     * @return array|null
     * @throws GeneralException
     */
    protected function getDefaultTargetPayloadSlice()
    {
        if ( ! $this->targetPayloadSlice)
        {
            $this->targetPayloadSlice = [
                'apiTitle'        => $this->apiTitle,
                'name'            => $this->ReportTemplateAccountGroupObj->display_name,
                'code'            => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                'fromDate'        => $this->getFromDate($this->year, $this->period),
                'toDate'          => $this->getToDate($this->year, $this->period),
                'period'          => $this->period,
                'entityName'      => $this->entityName,
                'units'           => $this->units,
                'entityOccupancy' => ! $this->OperatingExpensesRepository->renaming_occupancy_table ? $this->occupancy : 0,
            ];
        }

        return $this->targetPayloadSlice;
    }

    /**
     * This is used for the older style of pulling accounts from ledger,
     * based on deprecated BOMA style accound codes
     */
    protected function setGenerationalAccountCodes()
    {
        $this->target_account_code
            = $this->ReportTemplateAccountGroupObj->{$this->report_template_account_group_code_field_name};

        $this->deprecated_to_nondeprecated_account_lookup[$this->target_account_code]
            = $this->ReportTemplateAccountGroupObj->report_template_account_group_code;

        if ($this->ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() > 0)
        {
            $ChildrenReportTemplateAccountGroups = $this->ReportTemplateAccountGroupObj->getChildren();
            $this->children_account_codes        = $ChildrenReportTemplateAccountGroups->pluck($this->report_template_account_group_code_field_name)->toArray();

            foreach ($ChildrenReportTemplateAccountGroups as $ChildReportTemplateAccountGroup)
            {
                $this->deprecated_to_nondeprecated_account_lookup[$ChildReportTemplateAccountGroup->{$this->report_template_account_group_code_field_name}]
                    = $ChildReportTemplateAccountGroup->report_template_account_group_code;
            }

            foreach ($ChildrenReportTemplateAccountGroups as $ChildReportTemplateAccountGroup)
            {
                $this->grand_children_account_codes = array_merge(
                    $this->grand_children_account_codes,
                    $ChildReportTemplateAccountGroup->getChildren()->pluck($this->report_template_account_group_code_field_name)->toArray()
                );

                foreach ($ChildReportTemplateAccountGroup->getChildren()->pluck($this->report_template_account_group_code_field_name)->toArray() as $grandchild_account_code)
                {
                    $this->grandchildren_to_children_lookup[$grandchild_account_code]
                        = $ChildReportTemplateAccountGroup->{$this->report_template_account_group_code_field_name};
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function isTargetAccount()
    {
        return
            $this->result[$this->report_template_account_group_code_field_name]
            ==
            $this->ReportTemplateAccountGroupObj->{$this->report_template_account_group_code_field_name};
    }

    /**
     * @return bool
     */
    protected function isChildAccount()
    {
        return in_array($this->result[$this->report_template_account_group_code_field_name], $this->children_account_codes);
    }

    /**
     * @return bool
     */
    protected function isGrandchildAccount()
    {
        return in_array($this->result[$this->report_template_account_group_code_field_name], $this->grand_children_account_codes);
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
            return $this->occupancy == 0 ? 0 : (float) $data['rentable_amount'] / ($this->occupancy / 100);
        }
        return (float) $data['amount'];
    }
}
