<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Ledger\Metadata;
use App\Waypoint\Repositories\Ledger\VarianceRankingRepository;

/**
 * Class VariancePropertyRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 *
 * IMPORTANT - PROPERTY RANKING APIS ARE DEPRECATED IN FAVOR OF GROUP RANKING APIS
 */
class VariancePropertyRankingController extends LedgerController
{
    /** @var  VarianceRankingRepository */
    private $RankingRepositoryObj;

    /** @var string */
    public $apiTitle = 'VariancePropertyRanking';

    /** @var string */
    public $apiDisplayName = 'Budget Variance Ranking';

    /** @var string */
    public $entityName = 'Entity Ranking';

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
     * VariancePropertyRankingController constructor.
     * @param VarianceRankingRepository $VarianceRankingRepositoryObj
     */
    public function __construct(VarianceRankingRepository $VarianceRankingRepositoryObj)
    {
        $this->RankingRepositoryObj = $VarianceRankingRepositoryObj;
        parent::__construct($VarianceRankingRepositoryObj);
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @param bool $suppressResponse
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function index(
        $client_id,
        $property_id,
        $year,
        $period,
        $area,
        $report_template_account_group_id,
        $suppressResponse = false
    ) {
        try
        {
            /**
             * if you are looking at this and asking, why not use the $this->RankingRepositoryObj->all(); so
             * you can take advantage of RequestCriteria and LimitOffsetCriteria. The answer is that Laravel seems to bind the model and the
             *       DB connection closely. This would require creating a class for each model for each potental connection.
             *       Too much hassle for a system that will be retired before XMAS of 2017
             */

            $this->ClientObj         = $this->RankingRepositoryObj->ClientObj = $this->getClientObject();
            $this->ReportTemplateObj = $this->ClientObj->getDefaultAnalyticsReportTemplate();

            /** Property $PropertyObj */
            if ( ! $PropertyObj = Property::find($property_id))
            {
                throw new GeneralException('property_id id invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }

            if ( ! $this->ReportTemplateAccountGroupObj = $this->getReportTemplateAccountGroup($report_template_account_group_id))
            {
                throw new GeneralException('Invalid aux coa line item code', self::HTTP_ERROR_RESPONSE_CODE);
            }

            $this->RankingRepositoryObj->LedgerControllerObj = $this;
            $this->ClientObj                                 = $this->RankingRepositoryObj->ClientObj = $this->getClientObject();
            $this->year                                      = $this->RankingRepositoryObj->year = $year;
            $this->period                                    = $this->RankingRepositoryObj->period = $period;
            $this->area                                      = $this->RankingRepositoryObj->area = $area;

            // get property ranking
            $payload = $this->RankingRepositoryObj->getPropertyData($this->ReportTemplateAccountGroupObj);

            $this->targetPayloadSlice = [
                'apiTitle' => $this->apiTitle,
                'name'     => $this->RankingRepositoryObj->ReportTemplateAccountGroupObj->display_name,
                'code'     => $this->RankingRepositoryObj->ReportTemplateAccountGroupObj->report_template_account_group_code,
                'fromDate' => $this->getFromDate($year, $period),
                'toDate'   => $this->getToDate($year, $period),
                'period'   => $period,
                'units'    => $this->units,
            ];

            $metadata = (new Metadata(
                [
                    'LedgerController'              => $this,
                    'Property'                      => $PropertyObj,
                    'ReportTemplateAccountGroupObj' => $this->RankingRepositoryObj->ReportTemplateAccountGroupObj,
                    'count'                         => $payload->count(),
                    'target'                        => $this->targetPayloadSlice,
                ]
            ))->toArray();

            if ($suppressResponse)
            {
                return [
                    'data'            => $payload->toArray(),
                    'metadata'        => $metadata,
                    'transformations' => $this->getSpreadsheetFields(),
                ];
            }
            else
            {
                // return json payload
                return $this->sendResponse(
                    $payload->toArray(),
                    'property ranking generated successfully',
                    [],
                    $this->warnings,
                    $metadata
                );
            }
        }
        catch (GeneralException $e)
        {
            return $this->sendResponse(
                [],
                'unsuccessful benchmark data generation',
                $e->getMessage(),
                [],
                [
                    'apiTitle' => $this->apiTitle,
                    'count'    => 0,
                ]);
        }
        catch (\Exception $e)
        {
            return $this->sendResponse(
                [],
                'unsuccessful benchmark data generation',
                $e->getMessage(),
                [],
                [
                    'apiTitle' => $this->apiTitle,
                    'count'    => 0,
                ]);
        }
    }
}
