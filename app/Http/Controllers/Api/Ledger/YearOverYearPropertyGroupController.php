<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\Ledger\YearOverYearRepository;
use function array_unique;
use DB;

/**
 * Class YearOverYearPropertyGroupController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class YearOverYearPropertyGroupController extends LedgerController
{
    /** @var  YearOverYearRepository */
    private $YearOverYearRepository;

    /** @var string */
    public $apiTitle = 'YearOverYearPropertyGroup';

    /** @var string */
    public $apiDisplayName = 'Yearly Trend';

    /** @var string */
    public $unitsDisplayText = '% Change';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'                    => 'Expense Name',
        'amount'                  => '% Change',
        'grossAmountTargetYear'   => 'Gross Amount (target year)',
        'grossAmountPreviousYear' => 'Gross Amount (previous year)',
    ];

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '0.00"%"',
            'C' => '"$"#,##0.00',
            'D' => '"$"#,##0.00',
        ],
    ];

    /** @var null */
    public $originalYoyGroupCalcTable = null;

    /** @var null */
    public $newYoyGroupCalcTable = null;

    /**
     * YearOverYearPropertyGroupController constructor.
     * @param YearOverYearRepository $YearOverYearRepo
     */
    public function __construct(YearOverYearRepository $YearOverYearRepo)
    {
        parent::__construct($YearOverYearRepo);
        $this->YearOverYearRepository = $YearOverYearRepo;
        $this->targetYear             = (int) $this->year;
        $this->previousYear           = $this->targetYear - self::YEAR_OFFSET;
    }

    /**
     * @param $client_id
     * @param $property_group_id
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

        $report_template_deprecated_waypoint_code_arr = array_unique(array_merge(
                                                                         [
                                                                             $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                     true)->deprecated_waypoint_code,
                                                                             $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code,
                                                                         ],
                                                                         $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes()
                                                                     ));

        $this->YearOverYearRepository->LedgerControllerObj = $this;
        $property_id_old_arr                               = $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $this->entityName                                  = $this->PropertyGroupObj->is_all_property_group ? self::DEFAULT_PORTFOLIO_NAME : $this->PropertyGroupObj->name;
        $this->targetYearOccupancy                         = $this->YearOverYearRepository->getGroupAverageOccupancy($property_id_old_arr, $this->targetYear);
        $this->previousYearOccupancy                       = $this->YearOverYearRepository->getGroupAverageOccupancy($property_id_old_arr, $this->previousYear);
        $this->targetPayloadSlice                          = $this->getTargetPayloadSliceDefault();
        $this->YearOverYearRepository->ReportTemplateObj   = $this->ReportTemplateObj;

        if (
            ! $this->isCurrentYearEqualToAsOfYearAndCalendarYearRequested($this->period, $this->targetYear, $this->ClientObj) &&
            $this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->targetYear, $this->ClientObj)
        )
        {
            $this->originalYoyGroupCalcTable = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_FINAL_GROUP_ONLY';
            $this->newYoyGroupCalcTable      = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YOY_GROUP_ONLY';
            list($table, $this->status) = $this->getYoyTableAndStatus($this->ClientObj);

            // switch - if new YoY table exists
            if ($table == $this->newYoyGroupCalcTable)
            {
                $this->payload = $this->YearOverYearRepository->getDataVersionTwo(
                    $this, $this->PropertyGroupObj, $this->ReportTemplateAccountGroupObj, $report_template_deprecated_waypoint_code_arr
                );
            }
            else
            {
                $this->payload = $this->YearOverYearRepository->getDataVersionOne(
                    $this, $this->PropertyGroupObj, $this->ReportTemplateAccountGroupObj, $report_template_deprecated_waypoint_code_arr
                );
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
            return $this->sendResponse(
                $this->payload->toArray(),
                'year over year trends benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @param \App\Waypoint\Models\Client $Client
     * @return array
     */
    private function getYoyTableAndStatus(Client $Client)
    {
        // get ledger data from database waypoint_group_<client id>
        $this->DatabaseConnection = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $Client->client_id_old);
        $groupStatusTable         = 'GROUP_CALC_CLIENT_' . $Client->client_id_old . '_YEARLY_STATUS';

        $result = $this->DatabaseConnection
            ->table($groupStatusTable)
            ->whereIn('STEP_DESCRIPTION', [$this->originalYoyGroupCalcTable, $this->newYoyGroupCalcTable])
            ->select(
                'STEP_DESCRIPTION as table',
                'STATUS_DESCRIPTION as status'
            )
            ->get();

        if ($result->count() > 0)
        {
            $filtered = $result->where('table', $this->newYoyGroupCalcTable)->first();
            $table    = empty($filtered) ? $result->first()->table : $filtered->table;
            $status   = empty($filtered) ? $result->first()->status : $filtered->status;
            return [
                $this->renameTableBasedOnAvailabilityStatus($table, $status),
                $status,
            ];
        }
        else
        {
            throw new GeneralException('no yoy tables found');
        }
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function getTargetPayloadSliceDefault()
    {
        return [
            'apiTitle'      => $this->apiTitle,
            'name'          => $this->ReportTemplateAccountGroupObj->display_name,
            'code'          => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'period'        => $this->period,
            'target_year'   => $this->targetYear,
            'previous_year' => $this->previousYear,
            'fromDate'      => $this->getFromDate($this->previousYear, $this->period),
            'toDate'        => $this->getToDate($this->targetYear, $this->period),
            'entityName'    => $this->entityName,
            'units'         => $this->units,
            'targetYear'    => $this->targetYear,
            'previousYear'  => $this->previousYear,
        ];
    }

    /**
     * @param $table
     * @param $status
     * @return \Illuminate\Http\JsonResponse|null|string
     */
    private function renameTableBasedOnAvailabilityStatus($table, $status)
    {
        // if there is no data to access (group status:renaming)
        if ($status == 'RENAMING')
        {
            // return json payload
            return $this->sendResponse(
                [],
                'benchmark data not generated, warning given',
                [],
                ['data currently unavailable, group calculation in progress'],
                [
                    'count'       => 0,
                    'target'      => $this->targetResult,
                    'lineage'     => $this->getLineage(),
                    'currentData' => false,
                ]);
        }
        return $status == 'CALCULATING' ? $table . '_OLD' : $table;
    }

}

