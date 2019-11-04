<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\PeerAveragePropertyGroupRanking;
use App\Waypoint\Repositories\Ledger\PeerAverageRankingRepository;

/**
 * Class PeerAveragePropertyGroupRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class PeerAveragePropertyGroupRankingDeprecatedController extends LedgerController
{
    /** @var  PeerAverageRankingRepository */
    private $PeerAveragePropertyGroupRankingRepository;

    /** @var string */
    public $apiTitle = 'PeerAveragePropertyGroupRanking';

    /** @var string */
    public $apiDisplayName = 'Market Benchmark Ranking';

    /** @var string */
    public $entityName = 'Property Ranking';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'rank'              => 'Rank',
        'name'              => 'Entity Name',
        'amount'            => 'Difference to Peers ($/sq ft)',
        'entityGrossAmount' => 'Entity Gross Amount',
        'peerGrossAmount'   => 'Peer Gross Amount',
    ];

    public $spreadsheetColumnFormattingRules = [
        self::RANKING_FORMATTING_RULES => [
            'C' => '"$"0.00',
            'D' => '"$"#,##0.00',
            'E' => '"$"#,##0.00',
        ],
    ];

    /**
     * PeerAveragePropertyGroupRankingController constructor.
     * @param PeerAverageRankingRepository $PeerAverageRankingRepo
     */
    public function __construct(PeerAverageRankingRepository $PeerAverageRankingRepo)
    {
        parent::__construct($PeerAverageRankingRepo);
        $this->PeerAveragePropertyGroupRankingRepository = $PeerAverageRankingRepo;
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

        $property_id_old_arr        = $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $targetAccountCode[]        = $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
        $this->entityName           = $this->PropertyGroupObj->is_all_property_group ? self::DEFAULT_PORTFOLIO_NAME : $this->PropertyGroupObj->name;
        $this->perPropertyOccupancy = $this->PeerAveragePropertyGroupRankingRepository->getOccupancyForEachProperty($property_id_old_arr, $this->year);

        list($deltaAmountField, $targetAmountField, $peerAvgAmountField) = $this->getPeerAvgAmountFields($this->area);

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($peer_table_name, $status) = $this->getCorrectPeerTableBasedOnAvailabilityStatus(
            $this->ClientObj, 'PEER_GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_RANK', true
        );

        if ($this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj, false))
        {
            $results = $this->PeerAveragePropertyGroupRankingRepository
                ->getPeerDatabaseConnection()
                ->table($peer_table_name)
                ->where(
                    [
                        ['ACCOUNT_CODE', $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
                        ['BENCHMARK_TYPE', $this->report],
                        ['FROM_YEAR', $this->year],
                        [$deltaAmountField, '<>', null],
                    ]
                )
                ->whereIn('TARGET_PROPERTY_ID', $property_id_old_arr)
                ->select(
                    'TARGET_PROPERTY_NAME as name',
                    "$deltaAmountField as amount",
                    "$peerAvgAmountField as peerAvgAmount",
                    "$targetAmountField as targetAmount",
                    'SUM_PEER_OCCUPIED_AREA',
                    'SUM_PEER_RENTABLE_AREA',
                    'AVG_PEER_RENTABLE_AREA as peer_avg_rentable_area',
                    'AVG_PEER_OCCUPIED_AREA as peer_avg_occupied_area',
                    'ACCOUNT_CODE as code',
                    'TARGET_PROPERTY_ID as property_id',
                    $this->getTargetAreaField() . ' as target_area',
                    $this->getTargetAreaField(LedgerController::RENTABLE_SELECTION) . ' as target_rentable_area'
                )
                ->orderBy('amount')
                ->get();

            if (count($results) > 0)
            {
                $rankCounter = 1;
                foreach ($results as $result)
                {
                    $result                     = (array) $result; // cast object as array
                    $result['rank']             = $rankCounter++;
                    $result['LedgerController'] = $this;
                    $this->occupancy            = isset($this->perPropertyOccupancy[$result['property_id']]) && ! $this->PeerAveragePropertyGroupRankingRepository->renaming_occupancy_table ? $this->perPropertyOccupancy[$result['property_id']] : 0;
                    if (($key = array_search($result['property_id'], $property_id_old_arr)) !== false)
                    {
                        unset($property_id_old_arr[$key]);
                    }

                    $payloadModifications = [
                        'client_id'            => $this->ClientObj->id,
                        'code'                 => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'id'                   => md5($this->PropertyGroupObj->id . $this->ReportTemplateAccountGroupObj->report_template_account_group_code . $result['amount']),
                        'entityOccupancy'      => $this->occupancy,
                        'peerAvgOccupancy'     => $result['peer_avg_rentable_area'] > 0 ? ($result['peer_avg_occupied_area'] / $result['peer_avg_rentable_area']) * 100 : 0,
                        'target_gross_amount'  => $result['targetAmount'] * $result['target_area'],
                        'rentable_target_area' => $result['target_rentable_area'],
                        'peerAvgArea'          => (double) $result['peer_avg_rentable_area'],
                    ];

                    $this->payload[] = new PeerAveragePropertyGroupRanking($payloadModifications + $result);
                }
            }

            if (count($property_id_old_arr) > 0)
            {
                foreach ($property_id_old_arr as $propertyId)
                {
                    $property        = [
                        'LedgerController' => $this,
                        'id'               => md5($propertyId . $this->year . $this->report . $this->period . $this->area . $this->ReportTemplateAccountGroupObj->report_template_account_group_code),
                        'client_id'        => $this->ClientObj->id,
                        'property_id'      => $propertyId,
                        'code'             => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
                        'year'             => $this->year,
                        'type'             => $this->report,
                        'period'           => $this->period,
                        'area'             => $this->area,
                        'amount'           => null,
                        'rank'             => 0,
                        'entityOccupancy'  => 0,
                    ];
                    $this->payload[] = new PeerAveragePropertyGroupRanking($property);
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
            return $this->sendResponse(
                $this->payload->toArray(),
                'benchmark data generated successfully',
                [],
                $this->warnings,
                $this->getMetadata()
            );
        }
    }

    /**
     * @param null $area
     * @return string
     * @throws GeneralException
     */
    protected function getTargetAreaField($area = null)
    {
        if ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'TARGET_' . ($area ? $area : $this->area) . '_AREA';
    }
}
