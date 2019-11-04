<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRankingRepository;

/**
 * Class OperatingExpensesPropertyRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 *
 */
class OperatingExpensesPropertyRankingDeprecatedController extends LedgerController
{
    /** @var  OperatingExpensesRankingRepository $OperatingExpensesRankingRepositoryObj */
    private $OperatingExpensesRankingRepositoryObj;

    /** @var string */
    public $apiTitle = 'OperatingExpensesPropertyRanking';

    /** @var string */
    public $apiDisplayName = 'Account Breakdown Ranking';

    /** @var string */
    public $entityName = 'Property Ranking';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'        => 'Rank',
        'name'        => 'Entity Name',
        'amount'      => 'Expense Amount ($/sq ft)',
        'grossAmount' => 'Gross Amount',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
        ],
    ];

    /**
     * OperatingExpensesPropertyRankingController constructor.
     * @param OperatingExpensesRankingRepository $OperatingExpensesRankingRepositoryObj
     */
    public function __construct(OperatingExpensesRankingRepository $OperatingExpensesRankingRepositoryObj)
    {
        parent::__construct($OperatingExpensesRankingRepositoryObj);
        $this->OperatingExpensesRankingRepositoryObj                      = $OperatingExpensesRankingRepositoryObj;
        $this->OperatingExpensesRankingRepositoryObj->LedgerControllerObj = $this;
    }

    /**
     * @param integer $property_id
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
        $property_id,
        $report,
        $year,
        $period,
        $area,
        $report_template_account_group_id,
        $suppressResponse = false
    ) {
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        $this->entityName = $this->PropertyObj->display_name;
        $this->payload    = $this->OperatingExpensesRankingRepositoryObj->getPropertyData($this->ReportTemplateAccountGroupObj);

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
                [],
                $this->getMetadata()
            );
        }
    }

    /**
     * @return array|null
     * @throws GeneralException
     */
    protected function getDefaultTargetPayloadSlice()
    {
        $this->targetPayloadSlice = [
            'apiTitle' => $this->apiTitle,
            'name'     => $this->OperatingExpensesRankingRepositoryObj->ReportTemplateAccountGroupObj->display_name,
            'code'     => $this->OperatingExpensesRankingRepositoryObj->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'fromDate' => $this->getFromDate($this->year, $this->period),
            'toDate'   => $this->getToDate($this->year, $this->period),
            'period'   => $this->period,
            'units'    => $this->units,
        ];
        return $this->targetPayloadSlice;
    }

}


