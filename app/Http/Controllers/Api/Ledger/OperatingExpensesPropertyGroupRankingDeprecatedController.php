<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRankingRepository;

/**
 * Class OperatingExpensesPropertyGroupRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class OperatingExpensesPropertyGroupRankingDeprecatedController extends LedgerController
{
    /** @var  OperatingExpensesRankingRepository $OperatingExpensesRankingRepo */
    private $OperatingExpensesRankingRepo;

    /** @var string */
    public $apiTitle = 'OperatingExpensesPropertyGroupRanking';

    /** @var string */
    public $apiDisplayName = 'Account Breakdown Ranking';

    /** @var string */
    public $entityName = 'Property Ranking';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'             => 'Rank',
        'name'             => 'Entity Name',
        'amount'           => 'Expense Amount ($/sq ft)',
        'targetYearAmount' => 'Target Year Amount',
        'targetYear'       => 'Target Year',
        'entityName'       => 'Entity Name',
        'grossAmount'      => 'Gross Amount',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
        ],
    ];

    /**
     * OperatingExpensesPropertyGroupRankingController constructor.
     * @param OperatingExpensesRankingRepository $OperatingExpensesRankingRepo
     */
    public function __construct(OperatingExpensesRankingRepository $OperatingExpensesRankingRepo)
    {
        parent::__construct($OperatingExpensesRankingRepo);
        $this->OperatingExpensesRankingRepo = $OperatingExpensesRankingRepo;
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
        $this->entityName = $this->PropertyGroupObj->name;
        $this->payload    = $this->OperatingExpensesRankingRepo->getGroupData(
            $this,
            $this->ClientObj,
            $this->PropertyGroupObj,
            $this->ReportTemplateAccountGroupObj
        );

        $this->targetPayloadSlice = [
            'apiTitle'   => $this->apiTitle,
            'name'       => $this->ReportTemplateAccountGroupObj->display_name,
            'code'       => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'fromDate'   => $this->getFromDate($this->year, $this->period),
            'toDate'     => $this->getToDate($this->year, $this->period),
            'period'     => $this->period,
            'entityName' => $this->entityName,
            'units'      => $this->units,
            'targetYear' => $this->year,
        ];

        // suppression of the normal response is used for combined spreadsheet generation only
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
                'operating expenses property ranking generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }
}


