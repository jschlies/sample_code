<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Repositories\Ledger\YearOverYearRankingRepository;

/**
 * Class YearOverYearPropertyRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 *
 */
class YearOverYearPropertyRankingController extends LedgerController
{
    /** @var  YearOverYearRankingRepository */
    private $YearOverYearRankingRepository;

    /** @var string */
    public $apiTitle = 'YearOverYearPropertyRanking';

    /** @var string */
    public $apiDisplayName = 'Yearly Trend Ranking';

    /** @var string */
    public $unitsDisplayText = 'Expense % Change';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'                    => 'Rank',
        'name'                    => 'Entity Name',
        'amount'                  => '% Change',
        'grossAmountTargetYear'   => 'Gross Amount (target year)',
        'grossAmountPreviousYear' => 'Gross Amount (previous year)',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '0.00"%"',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /**
     * YearOverYearPropertyRankingController constructor.
     * @param YearOverYearRankingRepository $YearOverYearRankingRepo
     */
    public function __construct(YearOverYearRankingRepository $YearOverYearRankingRepo)
    {
        $this->YearOverYearRankingRepository = $YearOverYearRankingRepo;
        parent::__construct($YearOverYearRankingRepo);
    }

    /**
     * @param $client_id
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
     */
    public function index(
        $client_id,
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

        $this->YearOverYearRankingRepository->ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupObj;
        $this->YearOverYearRankingRepository->LedgerControllerObj           = $this;
        $this->entityName                                                   = $this->PropertyObj->name;
        $this->targetYear                                                   = $this->YearOverYearRankingRepository->targetYear = (int) $year;
        $this->previousYear                                                 = $this->YearOverYearRankingRepository->previousYear = $this->targetYear - self::YEAR_OFFSET;
        $this->period                                                       = $this->YearOverYearRankingRepository->period = $period;
        $this->area                                                         = $this->YearOverYearRankingRepository->area = $area;
        $this->report                                                       = $this->YearOverYearRankingRepository->report = $report;

        $payload = $this->YearOverYearRankingRepository->getPropertyData();

        if ($suppressResponse)
        {
            return [
                'data'            => $payload->toArray(),
                'metadata'        => $this->getMetadata(),
                'transformations' => $this->getSpreadsheetFields(),
            ];
        }
        else
        {
            return $this->sendResponse(
                $payload->toArray(),
                'year over year trends benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

}
