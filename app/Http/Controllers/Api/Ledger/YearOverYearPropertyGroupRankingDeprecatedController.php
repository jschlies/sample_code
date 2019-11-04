<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\Ledger\YearOverYearRankingRepository;
use DB;
use App\Waypoint\Models\Client;

/**
 * Class YearOverYearPropertyGroupRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class YearOverYearPropertyGroupRankingDeprecatedController extends LedgerController
{
    /** @var  YearOverYearRankingRepository */
    private $YearOverYearRankingRepository;

    /** @var string */
    public $apiTitle = 'YearOverYearPropertyGroupRanking';

    /** @var string */
    public $apiDisplayName = 'Yearly Trend Ranking';

    /** @var string */
    public $unitsDisplayText = '% Change';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'                    => 'Rank',
        'name'                    => 'Entity Name',
        'amount'                  => '% Change',
        'grossAmountTargetYear'   => 'Gross Amount (target year)',
        'grossAmountPreviousYear' => 'Gross Amount (previous year)',
    ];

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '0.00"%"',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /** @var null|string */
    public $originalYoyGroupCalcTable = null;

    /** @var null|string */
    public $newYoyGroupCalcTable = null;

    /** @var null|integer */
    public $tallyOfIncompletes = 0;

    /**
     * YearOverYearPropertyGroupRankingController constructor.
     * @param YearOverYearRankingRepository $YearOverYearRankingRepo
     */
    public function __construct(YearOverYearRankingRepository $YearOverYearRankingRepo)
    {
        parent::__construct($YearOverYearRankingRepo);
        $this->YearOverYearRankingRepository = $YearOverYearRankingRepo;
        $this->targetYear                    = (int) $this->year;
        $this->previousYear                  = $this->targetYear - self::YEAR_OFFSET;
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

        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        $this->entityName = $this->PropertyGroupObj->is_all_property_group
            ?
            self::DEFAULT_PORTFOLIO_NAME
            :
            $this->PropertyGroupObj->name;

        // TODO: decide on this check: $this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->targetYear, $this->ClientObj, false)
        if ( ! $this->isCurrentYearEqualToAsOfYearAndCalendarYearRequested($this->period, $this->targetYear, $this->ClientObj))
        {
            $this->originalYoyGroupCalcTable = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_FINAL';
            $this->newYoyGroupCalcTable      = 'GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YOY';
            list($table, $this->status) = $this->getYoyTableAndStatus($this->ClientObj);

            // switch - if new yoy table exists
            if ($table == $this->newYoyGroupCalcTable)
            {
                $this->payload = $this->YearOverYearRankingRepository->getGroupDataVersionTwo(
                    $this, $this->ClientObj, $this->PropertyGroupObj, $this->ReportTemplateAccountGroupObj
                );
            }
            else
            {
                $this->payload = $this->YearOverYearRankingRepository->getGroupDataVersionOne(
                    $this, $this->ClientObj, $this->PropertyGroupObj, $this->ReportTemplateAccountGroupObj
                );
            }
        }
        else
        {
            $this->warnings[] = 'no data for this time period';
        }

        $this->targetPayloadSlice = [
            'apiTitle'     => $this->apiTitle,
            'name'         => $this->ReportTemplateAccountGroupObj->display_name,
            'code'         => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'targetYear'   => $this->targetYear,
            'previousYear' => $this->previousYear,
            'fromDate'     => $this->getFromDate($this->previousYear, $this->period),
            'toDate'       => $this->getToDate($this->targetYear, $this->period),
            'entityName'   => $this->entityName,
            'units'        => $this->units,
            'period'       => $this->period,
        ];

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
