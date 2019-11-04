<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\PeerAverageProperty;
use App\Waypoint\Repositories\Ledger\PeerAverageRepository;
use App\Waypoint\Collection;

/**
 * Class PeerAveragePropertyController
 * @package App\Waypoint\Http\Controllers\Ledger
 *
 * NOTES:
 *      - currently not discounting any line items for invalid or incomplete data reasons
 *      - legacy checking and filtering methods left in for the time being, but will clean these out a later point
 */
class PeerAveragePropertyDeprecatedController extends LedgerController
{
    /** @var  PeerAverageRepository */
    private $PeerAverageRepository;

    /** @var string */
    public $apiTitle = 'PeerAverageProperty';

    /** @var string */
    public $apiDisplayName = 'Market Benchmark';

    /** @var string */
    public $unitsDisplayText = '$/sq ft';

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'              => 'Expense Name',
        'amount'            => 'Difference to Peers ($/sq ft)',
        'entityGrossAmount' => 'Entity Gross Amount',
    ];

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        self::EXPENSE_FORMATTING_RULES => [
            'B' => '0.00"%"',
            'C' => '"$"#,##0.00',
            'D' => '"$"#,##0.00',
        ],
    ];

    /** @var null|float */
    private $entity_occupancy = null;

    /**
     * PeerAveragePropertyController constructor.
     * @param PeerAverageRepository $PeerAverageRepository
     */
    public function __construct(PeerAverageRepository $PeerAverageRepository)
    {
        $this->PeerAverageRepository                      = $PeerAverageRepository;
        $this->entityType                                 = 'property';
        $this->PeerAverageRepository->LedgerControllerObj = $this;
        parent::__construct($PeerAverageRepository);
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

        $this->initInputForCombinedSpreadsheets(
            [
                'year'   => $year,
                'period' => $period,
                'area'   => $area,
                'report' => $report,
            ],
            $this->PeerAverageRepository
        );
        $this->entityName = $this->PropertyObj->display_name;
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        if ($this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj))
        {
            $this->perform_query();
            $this->package_data();
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
            // return json payload
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
     * @return \Illuminate\Support\Collection
     * @throws GeneralException
     */
    protected function perform_query()
    {
        list($deltaAmountField, $targetAmountField, $peerAvgAmountField) = $this->getPeerAvgAmountFields($this->area);

        $peer_table_name = $this->getCorrectPeerTableBasedOnAvailabilityStatus(
            $this->ClientObj,
            'PEER_GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_RANK'
        );

        $this->query_result = $this->PeerAverageRepository
            ->getPeerDatabaseConnection()
            ->table($peer_table_name)
            ->where(
                [
                    ['BENCHMARK_TYPE', $this->report],
                    ['FROM_YEAR', $this->year],
                    ['TARGET_PROPERTY_ID', $this->PropertyObj->property_id_old],
                ]
            )
            ->whereIn(
                'ACCOUNT_CODE',
                array_merge(
                    [$this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
                    $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes()
                ))
            ->select(
                'TARGET_PROPERTY_NAME as name',
                "$deltaAmountField as delta_amount",
                "$targetAmountField as target_amount",
                "$peerAvgAmountField as peer_avg_amount",
                'ACCOUNT_CODE as deprecated_code',
                'SUM_PEER_OCCUPIED_AREA',
                'SUM_PEER_PROPERTY_AREA',
                'AVG_PEER_RENTABLE_AREA',
                $this->getTargetAreaField() . ' as target_area',
                $this->getTargetAreaField(LedgerController::RENTABLE_SELECTION) . ' as rentable_target_area'
            )
            ->distinct()// due to duplicates in the benchmarking data
            ->get();
    }

    /**
     * @param \Illuminate\Support\Collection $results
     * @return Collection
     * @throws GeneralException
     */
    protected function package_data()
    {
        if ($this->query_result && $this->query_result->count() > 0)
        {
            foreach ($this->query_result as $result)
            {
                if ($this->isTarget($result))
                {
                    $this->getTargetPayloadSlice(
                        [
                            'amount'                           => $result->delta_amount,
                            'targetAmount'                     => $result->target_amount,
                            'peerAvgAmount'                    => $result->peer_avg_amount,
                            'peerAvgOccupancy'                 => $this->getPeerAverageOccupancy($result),
                            'entityGrossAmount'                => $this->getTargetGrossAmount($result),
                            'targetArea'                       => (double) $result->rentable_target_area,
                            'peerAvgArea'                      => (double) $result->AVG_PEER_RENTABLE_AREA,
                            'peerAvgGrossAmount'               => $result->peer_avg_amount * $result->AVG_PEER_RENTABLE_AREA,
                            'entityOccupancy'                  => $this->getEntityOccupancy(),
                            'report_template_account_group_id' => $this->getReportTemplateAccountGroupAttribute($result),
                        ]
                    );
                }

                if ($this->isChild($result) || $this->onlyParentResultPresent($this->query_result))
                {
                    $peer_account_data_arr = [
                        'amount'                           => $result->delta_amount,
                        'targetAmount'                     => $result->target_amount,
                        'entityOccupancy'                  => $this->getEntityOccupancy(),
                        'peerAvgAmount'                    => $result->peer_avg_amount,
                        'peerAvgOccupancy'                 => $this->getPeerAverageOccupancy($result),
                        'target_gross_amount'              => $this->getTargetGrossAmount($result),
                        'id'                               => $this->generateMD5($result),
                        'code'                             => $this->getNewCodeFromOldCode($result),
                        'name'                             => $this->getDisplayName($result),
                        'LedgerController'                 => $this,
                        'targetArea'                       => (double) $result->rentable_target_area,
                        'peerAvgArea'                      => (double) $result->AVG_PEER_RENTABLE_AREA,
                        'report_template_account_group_id' => $this->getReportTemplateAccountGroupAttribute($result),
                    ];
                    $this->payload[]       = new PeerAverageProperty($peer_account_data_arr);
                }
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
            ->where('deprecated_waypoint_code', $result->deprecated_code)
            ->first()
            ->{$attribute};
    }

    /**
     * @param $result
     * @return bool
     */
    private function isTarget($result): bool
    {
        return $result->deprecated_code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
    }

    /**
     * @param $result
     * @return float
     */
    private function getTargetGrossAmount($result): float
    {
        return $result->target_amount * $result->target_area;
    }

    /**
     * @return null|float
     * @throws GeneralException
     */
    private function getEntityOccupancy(): float
    {
        if ( ! $this->entity_occupancy)
        {
            $this->entity_occupancy = $this->getOccupancyForSingleProperty($this->ClientObj, $this->PropertyObj->property_id_old, $this->year);
        }
        return $this->PeerAverageRepository->renaming_occupancy_table ? null : $this->entity_occupancy;
    }

    /**
     * @param $result
     * @return string
     */
    private function getDisplayName($result): string
    {
        return $this->ReportTemplateObj->reportTemplateAccountGroups->where('deprecated_waypoint_code', $result->deprecated_code)->first()->display_name;
    }

    /**
     * @param $result
     * @return string
     */
    private function getNewCodeFromOldCode($result): string
    {
        return $this->ReportTemplateObj->reportTemplateAccountGroups->where('deprecated_waypoint_code', $result->deprecated_code)->first()->report_template_account_group_code;
    }

    /**
     * @param $results
     * @return bool
     */
    private function onlyParentResultPresent(\Illuminate\Support\Collection $results)
    {
        return $results->count() == $this->filterAllLineItemsExceptForTarget($results)->count();
    }

    /**
     * @param \Illuminate\Support\Collection $results
     * @return \Illuminate\Support\Collection
     */
    private function filterAllLineItemsExceptForTarget(\Illuminate\Support\Collection $results)
    {
        return $results->filter(
            function ($item)
            {
                return $item->deprecated_code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
            }
        );
    }

    /**
     * @param $result
     * @return string
     */
    private function generateMD5($result)
    {
        return md5($this->year . $this->report . $result->delta_amount);
    }

    /**
     * @param $result
     * @return float|int
     */
    private function getPeerAverageOccupancy($result)
    {
        return $result->SUM_PEER_OCCUPIED_AREA <= $result->SUM_PEER_PROPERTY_AREA && $result->SUM_PEER_PROPERTY_AREA != 0
            ? ($result->SUM_PEER_OCCUPIED_AREA / $result->SUM_PEER_PROPERTY_AREA) * 100
            : 0;
    }

    /**
     * @param $result
     * @return bool
     */
    private function isChild($result): bool
    {
        return in_array($result->deprecated_code, $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes());
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getTargetAreaField($area = null): string
    {
        if (
            ( ! $this->area || ! in_array($this->area, self::ACCEPTABLE_AREAS)) ||
            ($area && ! in_array($area, self::ACCEPTABLE_AREAS))
        )
        {
            throw new GeneralException('unusable report given');
        }
        return 'TARGET_' . ($area ? $area : $this->area) . '_AREA';
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPeerSumAreaField(): string
    {
        if ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'SUM_PEER_' . $this->area . '_AREA';
    }
}
