<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\PeerAveragePropertyGroupRanking;
use App\Waypoint\Repositories\Ledger\PeerAverageRankingRepository;
use App\Waypoint\Collection;
use function implode;

/**
 * Class PeerAveragePropertyRankingController
 * @package App\Waypoint\Http\Controllers\Ledger
 */
class PeerAveragePropertyRankingController extends LedgerController
{
    /** @var  PeerAverageRankingRepository */
    private $PeerAverageRankingRepository = null;

    protected $UserAllPropertyGroupObj = null;

    protected $property_id_old_arr = [];

    /** @var string */
    public $apiTitle = 'PeerAveragePropertyRanking';

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
     * PeerAveragePropertyRankingController constructor.
     * @param PeerAverageRankingRepository $PeerAverageRankingRepo
     */
    public function __construct(PeerAverageRankingRepository $PeerAverageRankingRepositoryObj)
    {
        parent::__construct($PeerAverageRankingRepositoryObj);
        $this->PeerAverageRankingRepository = $PeerAverageRankingRepositoryObj;
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
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
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

        $this->entityName = $this->PropertyObj->display_name;

        if ( ! $this->UserAllPropertyGroupObj = $this->getUserObject()->allPropertyGroup)
        {
            throw new GeneralException('property group missing', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        $this->property_id_old_arr  = $this->UserAllPropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
        $this->perPropertyOccupancy = $this->PeerAverageRankingRepository->getOccupancyForEachProperty($this->property_id_old_arr, $this->year);

        if ($this->checkForAvailableDataGivenPeriodAndYear($period, $year, $this->ClientObj))
        {
            $results = $this->perform_query();
            $this->package_data($results);
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
     * @param \Illuminate\Support\Collection $results
     * @return Collection
     */
    protected function package_data(\Illuminate\Support\Collection $results)
    {
        if ($results->count() > 0)
        {
            $rankCounter = 1;
            foreach ($results as $result)
            {
                $result = (array) $result;

                $this->occupancy = isset($this->perPropertyOccupancy[$result['property_id']]) ? $this->perPropertyOccupancy[$result['property_id']] : 0;

                if (($key = array_search($result['property_id'], $this->property_id_old_arr)) !== false)
                {
                    unset($this->property_id_old_arr[$key]);
                }

                $payloadModifications = [
                    'LedgerController'                 => $this,
                    'id'                               => $this->generateMD5([$result['property_id'], $result['amount']]),
                    'client_id'                        => $this->ClientObj->id,
                    'code'                             => $this->getReportTemplateAccountGroupAttribute('report_template_account_group_code'),
                    'report_template_account_group_id' => $this->getReportTemplateAccountGroupAttribute(),
                    'rank'                             => $rankCounter++,
                    'entityOccupancy'                  => ! $this->PeerAverageRankingRepository->renaming_occupancy_table ? $this->occupancy : 0,
                    'peerAvgOccupancy'                 => $this->getOccupancy($result),
                    'target_gross_amount'              => $result['targetAmount'] * $result['target_area'],
                    'rentable_target_area'             => (double) $result['rentable_target_area'],
                    'peerAvgArea'                      => (double) $result['AVG_PEER_RENTABLE_AREA'],
                ];

                $this->payload[] = new PeerAveragePropertyGroupRanking($payloadModifications + $result);
            }
        }
        if (count($this->property_id_old_arr) > 0)
        {
            foreach ($this->property_id_old_arr as $property_id)
            {
                $property_data   = [
                    'LedgerController'                 => $this,
                    'id'                               => $this->generateMD5([$property_id]),
                    'client_id'                        => $this->ClientObj->id,
                    'property_id'                      => $property_id,
                    'code'                             => $this->getReportTemplateAccountGroupAttribute('deprecated_waypoint_code'),
                    'report_template_account_group_id' => $this->report_template_account_group_id,
                    'year'                             => $this->year,
                    'type'                             => $this->report,
                    'period'                           => $this->period,
                    'area'                             => $this->area,
                    'amount'                           => null,
                    'rank'                             => 0,
                    'entityOccupancy'                  => ! $this->PeerAverageRankingRepository->renaming_occupancy_table ? $this->occupancy : 0,
                ];
                $this->payload[] = new PeerAveragePropertyGroupRanking($property_data);
            }
        }
        return $this->payload;
    }

    /**
     * @param $result
     * @return float|int|null
     */
    protected function getOccupancy($result)
    {
        if ($result['SUM_PEER_RENTABLE_AREA'] == 0)
        {
            return null;
        }
        return $result['SUM_PEER_OCCUPIED_AREA'] <= $result['SUM_PEER_RENTABLE_AREA'] ? ($result['SUM_PEER_OCCUPIED_AREA'] / $result['SUM_PEER_RENTABLE_AREA']) * 100 : 0;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    protected function getReportTemplateAccountGroupAttribute($attribute = 'id')
    {
        return $this->ReportTemplateObj
            ->reportTemplateAccountGroups
            ->find($this->report_template_account_group_id)
            ->first()
            ->{$attribute};
    }

    /**
     * @param $parameter_array
     * @return string
     */
    protected function generateMD5($parameter_array)
    {
        return md5($this->year . $this->report . $this->period . $this->area . $this->report_template_account_group_id . implode('', $parameter_array));
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws GeneralException
     */
    protected function perform_query()
    {
        $peer_table_name_orig = 'PEER_GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_RANK';
        $peer_table_name      = $this->getCorrectPeerTableBasedOnAvailabilityStatus($this->ClientObj, $peer_table_name_orig);
        list($deltaAmountField, $targetAmountField, $peerAvgAmountField) = $this->getPeerAvgAmountFields($this->area);

        return $results = $this->PeerAverageRankingRepository
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
            ->whereIn('TARGET_PROPERTY_ID', $this->property_id_old_arr)
            ->select(
                'TARGET_PROPERTY_NAME as name',
                "$deltaAmountField as amount",
                'ACCOUNT_CODE as deprecated_account_code',
                'TARGET_PROPERTY_ID as property_id',
                "$targetAmountField as targetAmount",
                "$peerAvgAmountField as peerAvgAmount",
                'SUM_PEER_OCCUPIED_AREA',
                'SUM_PEER_PROPERTY_AREA',
                'SUM_PEER_RENTABLE_AREA',
                'AVG_PEER_RENTABLE_AREA',
                $this->getPropertyAreaField() . ' as target_area',
                $this->getPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_target_area'
            )
            ->get();
    }

    /**
     * @param null $area
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField($area = null)
    {
        if ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'TARGET_' . ($area ? $area : $this->area) . '_AREA';
    }
}
