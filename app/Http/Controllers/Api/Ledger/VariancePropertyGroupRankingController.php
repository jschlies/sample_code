<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\Ledger\VarianceRankingRepository;

/**
 * Class VariancePropertyGroupRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class VariancePropertyGroupRankingController extends LedgerController
{
    /** @var  VarianceRankingRepository */
    private $VarianceRankingRepositoryObj;

    /** @var string */
    public $apiTitle = 'VariancePropertyGroupRanking';

    /** @var string */
    public $apiDisplayName = 'Budget Variance Ranking';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    public $spreadsheetColumnsToHide = [
        'id',
        'targetYear',
        'property_id',
        'code',
        'unitsDisplayText',
        'entityName',
        'targetYearAmount',
        'budgetAmount',
        'actualAmount',
        'entityOccupancy',
        'units',
        'rentable_area',
        'report_template_account_type_id',
    ];

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'              => 'Rank',
        'name'              => 'Entity Name',
        'amount'            => 'Variance ($/sq ft)',
        'actualGrossAmount' => 'Actual Gross Amount',
        'budgetGrossAmount' => 'Budget Gross Amount',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /**
     * VariancePropertyGroupRankingController constructor.
     * @param VarianceRankingRepository $VarianceRankingRepositoryObj
     */
    public function __construct(VarianceRankingRepository $VarianceRankingRepositoryObj)
    {
        parent::__construct($VarianceRankingRepositoryObj);
        $this->VarianceRankingRepositoryObj = $VarianceRankingRepositoryObj;
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @param bool $suppressResponse
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \App\Waypoint\Exceptions\LedgerException
     * @throws \BadMethodCallException
     */
    public function index(
        $client_id,
        $property_group_id,
        $year,
        $period,
        $area,
        $report_template_account_group_id,
        $suppressResponse = false
    ) {
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        if ( ! $this->VarianceRankingRepositoryObj->PropertyGroupObj = PropertyGroup::find($property_group_id))
        {
            throw new GeneralException('property_group_id invalid', self::HTTP_ERROR_RESPONSE_CODE);
        }
        $this->entityName         = $this->VarianceRankingRepositoryObj->PropertyGroupObj->is_all_property_group
            ?
            self::DEFAULT_PORTFOLIO_NAME
            :
            $this->VarianceRankingRepositoryObj->PropertyGroupObj->name;
        $this->payload            = $this->VarianceRankingRepositoryObj->getGroupData(
            $this,
            $this->ClientObj,
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
            return $this->sendResponse(
                $this->payload->toArray(),
                'property ranking generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }
}
