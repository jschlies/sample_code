<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\OperatingExpensesPropertyGroup;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;

/**
 * Class OperatingExpensesPropertyGroupController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 */
class OperatingExpensesPropertyGroupDeprecatedController extends LedgerController
{
    /** @var  OperatingExpensesRepository */
    private $OperatingExpensesRepository;

    /** @var string */
    public $apiTitle = 'OperatingExpensesPropertyGroup';

    /** @var string */
    public $apiDisplayName = 'Account Breakdown';

    /** @var string */
    public $entityName = 'Property Ranking';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var null */
    protected $target_account = null;

    /** @var array */
    protected $child_accounts_arr = [];

    /** @var array */
    protected $grand_children_accounts_arr = [];

    /** @var array */
    protected $grand_children_lookup_arr = [];

    /** @var array */
    protected $all_generations_of_accounts_arr = [];

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '"$"0.00',
            'C' => '"$"#,##0.00',
            'D' => '"$"0.00',
            'F' => '0.00"%"',
        ],
    ];

    protected $target_result = null;

    /**
     * OperatingExpensesController constructor.
     * @param OperatingExpensesRepository $OperatingExpensesRepositoryObj
     */
    public function __construct(OperatingExpensesRepository $OperatingExpensesRepositoryObj)
    {
        parent::__construct($OperatingExpensesRepositoryObj);
        $this->OperatingExpensesRepository                      = $OperatingExpensesRepositoryObj;
        $this->PropertyGroupRepositoryObj                       = App::make(PropertyGroupRepository::class);
        $this->OperatingExpensesRepository->LedgerControllerObj = $this;
        $this->ReportTemplateAccountGroupRepositoryObj          = App::make(App\Waypoint\Repositories\ReportTemplateAccountGroupRepository::class);

    }

    /**
     * @param integer $property_group_id
     * @param $report
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
        $property_group_id,
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
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();
        $this->DatabaseConnection          = DatabaseConnectionRepository::getGroupDatabaseConnection($this->ClientObj);
        $this->grand_children_accounts_arr = $this->ReportTemplateAccountGroupObj->getGrandChildrenDeprecatedCoaCodes();
        $this->child_accounts_arr          = $this->ReportTemplateAccountGroupObj->getChildren()->pluck('deprecated_waypoint_code')->toArray();
        $this->target_account              = $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
        $this->entityName                  = $this->PropertyGroupObj->is_all_property_group ? self::DEFAULT_PORTFOLIO_NAME : $this->PropertyGroupObj->name;
        $this->propertyGroupAvgOccupancy   = $this->getOccupancy();

        if ($this->dataIsAvailableForThisTimePeriod())
        {
            $results = $this->perform_query();
            $this->package_data(collect_waypoint($results));
        }
        else
        {
            $this->warnings[] = 'no data for this time period';
        }

        if ($suppressResponse)
        {
            return $this->createDataPackageForCombinedSpreadsheets();
        }
        else
        {
            return $this->sendResponse(
                $this->payload->toArray(),
                'operating expense benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @return array|float|int
     * @throws GeneralException
     */
    protected function getOccupancy()
    {
        return $this->OperatingExpensesRepository->getGroupAverageOccupancy(
            $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray(),
            $this->year
        );
    }

    /**
     * @return bool
     * @throws GeneralException
     */
    protected function dataIsAvailableForThisTimePeriod()
    {
        return $this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj, false);
    }

    /**
     * @param Collection $ResultsObj
     * @throws GeneralException
     */
    protected function package_data(Collection $ResultsObj)
    {
        if ($ResultsObj->count() > 0)
        {
            foreach ($ResultsObj as $result)
            {
                $result                     = (array) $result;
                $result['LedgerController'] = $this;
                $result['PropertyGroup']    = $this->PropertyGroupObj;
                $result['entityOccupancy']  = $this->propertyGroupAvgOccupancy;
                $result['grossAmount']      = (float) $result['amount'] * (float) $result['area'];
                $result['rentable_area']    = (float) $result['rentable_area'];

                if ($this->isTarget($result))
                {
                    $this->target_result = $result;
                    $this->getTargetPayloadSlice(
                        [
                            'amount'          => (float) $result['amount'],
                            'grossAmount'     => (float) $result['grossAmount'],
                            'area'            => (float) $result['area'],
                            'rentable_area'   => (float) $result['rentable_area'],
                            'entityOccupancy' => $this->propertyGroupAvgOccupancy,
                        ]
                    );
                }
                elseif ($this->isChild($result))
                {
                    $result['id']               = $this->generateMD5($result);
                    $children_from_result_arr[] = $result;
                }
                elseif ($this->isGrandchild($result))
                {
                    $this->tallyGrandchildrenPerChild($result);
                }
            }

            if (empty($children_from_result_arr))
            {
                $children_from_result_arr[] = $this->target_result;
            }

            foreach ($children_from_result_arr as $child_result)
            {
                $updates         = [
                    'childCount'                       => $this->getChildCount($child_result),
                    'report_template_account_group_id' => $this->getReportTemplateAccountGroupAttribute($child_result),
                    'code'                             => $this->getReportTemplateAccountGroupAttribute($child_result, 'report_template_account_group_code'),
                    'areaType'                         => $this->area,
                ];
                $this->payload[] = new OperatingExpensesPropertyGroup(array_merge($child_result, $updates));
            }
        }
    }

    /**
     * @param $result
     * @return mixed
     */
    protected function getReportTemplateAccountGroupAttribute($result, $attribute = 'id')
    {
        return $this->ReportTemplateObj
            ->reportTemplateAccountGroups
            ->where('deprecated_waypoint_code', $result['deprecated_waypoint_code'])
            ->first()
            ->{$attribute};
    }

    /**
     * @param $result
     * @return string
     */
    protected function generateMD5($result)
    {
        return md5($result['property_group_id'] . $result['benchmark_id']);
    }

    /**
     * @param $result
     * @return bool
     */
    protected function isTarget($result)
    {
        return $result['deprecated_waypoint_code'] == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
    }

    /**
     * @param string $group_calc_data_table
     * @return \Illuminate\Support\Collection
     * @throws GeneralException
     */
    protected function perform_query()
    {
        $group_calc_data_table = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_FINAL_GROUP_ONLY';
        list($group_calc_data_table, $status) = $this->getCorrectTableBasedOnAvailabilityStatus($this->ClientObj, $group_calc_data_table, true);
        $this->status = $status;

        return $this->DatabaseConnection
            ->table($group_calc_data_table)
            ->where(
                [
                    ['FROM_YEAR', $this->year],
                    ['BENCHMARK_TYPE', $this->getBenchmarkType($this->report, $this->period)],
                    ['REF_GROUP_ID', $this->PropertyGroupObj->id],
                ]
            )
            ->whereIn('ACCOUNT_CODE', $this->getAccountCodes())
            ->select(
                "FK_BENCHMARK_ID as benchmark_id",
                $this->getGroupAmountField() . ' as amount',
                'ACCOUNT_CODE as deprecated_waypoint_code',
                'REF_GROUP_ID as property_group_id',
                'ACCOUNT_NAME_UPPER as name',
                $this->getPropertyGroupAreaField() . ' as area',
                $this->getPropertyGroupAreaField('RENTABLE') . ' as rentable_area'
            )
            ->get();
    }

    /**
     * @param $result
     * @return bool
     */
    protected function isGrandchild($result)
    {
        return in_array($result['deprecated_waypoint_code'], $this->grand_children_accounts_arr);
    }

    /**
     * @param $result
     * @return bool
     */
    protected function isChild($result)
    {
        return in_array($result['deprecated_waypoint_code'], $this->child_accounts_arr);
    }

    /**
     * @return array
     */
    protected function getAccountCodes()
    {
        return array_merge(
            [$this->target_account],
            $this->child_accounts_arr,
            $this->grand_children_accounts_arr
        );
    }

    /**
     * @param $area
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyGroupAreaField($area = null)
    {
        if ($area)
        {
            return 'GROUP_SUM_' . $area . '_AREA';
        }
        elseif ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'GROUP_SUM_' . $this->area . '_AREA';
    }

    /**
     * @param $result
     * @throws LedgerException
     */
    protected function tallyGrandchildrenPerChild($result)
    {
        /** @var App\Waypoint\Models\ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        if ( ! $ReportTemplateAccountGroupObj = $this->ReportTemplateObj->reportTemplateAccountGroups->where('deprecated_waypoint_code', '=', $result['deprecated_waypoint_code'])
                                                                                                     ->first())
        {
            throw new LedgerException('could not find report template', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        $ReportTemplateAccountGroupParentObj = $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent;

        if ( ! isset($this->grand_children_lookup_arr[$ReportTemplateAccountGroupParentObj->deprecated_waypoint_code]))
        {
            $this->grand_children_lookup_arr[$ReportTemplateAccountGroupParentObj->deprecated_waypoint_code] = 1;
        }
        else
        {
            $this->grand_children_lookup_arr[$ReportTemplateAccountGroupParentObj->deprecated_waypoint_code] += 1;
        }
    }

    /**
     * @param $result
     * @return int|mixed
     */
    protected function getChildCount($result)
    {
        if (isset($this->grand_children_lookup_arr[$result['deprecated_waypoint_code']]))
        {
            return $this->grand_children_lookup_arr[$result['deprecated_waypoint_code']];
        }
        else
        {
            return 0;
        }
    }

}
