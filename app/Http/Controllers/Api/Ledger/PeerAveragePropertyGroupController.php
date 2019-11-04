<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\PeerAveragePropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\Ledger\PeerAverageRepository;

/**
 * Class PeerAveragePropertyGroupController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 *
 * NOTES:
 *      - currently not discounting any line items for invalid or incomplete data reasons
 *      - legacy checking and filtering methods left in for the time being, but will clean these out a later point
 */
class PeerAveragePropertyGroupController extends LedgerController
{
    /** @var  PeerAverageRepository */
    private $PeerAverageRepository;

    /** @var string */
    public $apiTitle = 'PeerAveragePropertyGroup';

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

    /** @var null|ReportTemplateAccountGroup */
    public $ChildReportTemplateAccountGroupObj = null;

    /**
     * PeerAverageController constructor.
     * @param PeerAverageRepository $PeerAverageRepo
     */
    public function __construct(PeerAverageRepository $PeerAverageRepo)
    {
        parent::__construct($PeerAverageRepo);
        $this->PeerAverageRepository = $PeerAverageRepo;
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
                'year'   => $year,
                'period' => $period,
                'area'   => $area,
                'report' => $report,
            ],
            $this->PeerAverageRepository
        );
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        if (empty($this->ClientObj->client_id_old))
        {
            throw new GeneralException('old property id missing', self::HTTP_ERROR_RESPONSE_CODE);
        }

        $this->PeerAverageRepository->LedgerControllerObj = $this;
        $this->entityType                                 = 'group';
        $this->entityName                                 = $this->PropertyGroupObj->is_all_property_group ? self::DEFAULT_PORTFOLIO_NAME : $this->PropertyGroupObj->name;
        list($deltaAmountField, $targetAmountField, $peerAvgAmountField) = $this->getPeerGroupAvgAmountFields($this->area);
        $this->occupancy          = $this->PeerAverageRepository->getGroupAverageOccupancy(
            $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray(),
            $this->year
        );
        $this->targetPayloadSlice = $this->setDefaultTargetDetails();
        $peer_count               = $this->getTotalPeerCountForGroup();

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($peer_table, $status) = $this->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj, 'TARGET_GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_FINAL', true
        );

        if ($this->checkForAvailableDataGivenPeriodAndYear($this->period, $this->year, $this->ClientObj, false))
        {
            // this query collapses peers into one row for each line item, providing all the averages necessary
            // if individual peer values are needed, ungroup the grouping based on ACCOUNT_CODE

            $results = collect_waypoint(
                $this->PeerAverageRepository
                    ->getPeerDatabaseConnection()
                    ->table($peer_table)
                    ->where(
                        [
                            ['FROM_YEAR', $this->year],
                            ['BENCHMARK_TYPE', $report],
                            ['REF_GROUP_ID', $this->PropertyGroupObj->id],
                        ]
                    )
                    ->whereNotNull($deltaAmountField)
                    ->whereIn(
                        'ACCOUNT_CODE',
                        array_merge(
                            [$this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
                            $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes(),
                            $this->ReportTemplateAccountGroupObj->getGrandChildrenDeprecatedCoaCodes()
                        )
                    )
                    ->select(
                        "$deltaAmountField as delta_amount",
                        "$targetAmountField as target_amount",
                        "$peerAvgAmountField as peer_avg_amount",
                        'GROUP_AVG_PEER_PERCENT_OCCUPIED as peer_avg_occupancy',
                        "ACCOUNT_CODE as deprecated_code",
                        "REF_GROUP_ID as property_group_id",
                        "ACCOUNT_NAME_UPPER as name",
                        $this->getPropertyAreaField() . ' as target_area',
                        $this->getPropertyAreaField(LedgerController::RENTABLE_SELECTION) . ' as target_rentable_area',
                        'GROUP_SUM_PEER_RENTABLE_AREA as peer_sum_rentable_area'
                    )
                    ->distinct()// due to duplicates in the benchmarking data
                    ->groupBy('ACCOUNT_CODE')
                    ->get()
            );

            if ($results->count() > 0)
            {
                foreach ($results as $collapsed_peers_row)
                {
                    if ($this->isTarget($collapsed_peers_row))
                    {
                        $this->getTargetPayloadSlice(
                            [
                                'amount'                           => (double) $collapsed_peers_row->delta_amount,
                                'targetAmount'                     => (double) $collapsed_peers_row->target_amount,
                                'peerAvgAmount'                    => (double) $collapsed_peers_row->peer_avg_amount,
                                'peerAvgOccupancy'                 => (double) $collapsed_peers_row->peer_avg_occupancy * 100,
                                'entityGrossAmount'                => (double) $collapsed_peers_row->target_amount * (double) $collapsed_peers_row->target_area,
                                'targetArea'                       => (double) $collapsed_peers_row->target_rentable_area,
                                'peerAvgArea'                      => (double) ($collapsed_peers_row->peer_sum_rentable_area / $peer_count),
                                'peerAvgGrossAmount'               => (double) $collapsed_peers_row->peer_avg_amount * ($collapsed_peers_row->peer_sum_rentable_area / $peer_count),
                                'report_template_account_group_id' => $this->getReportTemplateAccountGroupIdFromDeprecatedCode($collapsed_peers_row->deprecated_code),
                            ]
                        );
                    }
                    if ($this->isChild($collapsed_peers_row) || $this->onlyParentResultPresent($results))
                    {

                        $payload_item_data_arr = [
                            'LedgerController'                 => $this,
                            'deprecated_code'                  => $collapsed_peers_row->deprecated_code,
                            'entityOccupancy'                  => $this->occupancy,
                            'peerAvgOccupancy'                 => (double) $collapsed_peers_row->peer_avg_occupancy * 100,
                            'target_gross_amount'              => (double) $collapsed_peers_row->target_amount * (double) $collapsed_peers_row->target_area,
                            'id'                               => $this->generateMD5($collapsed_peers_row),
                            'amount'                           => (double) $collapsed_peers_row->delta_amount,
                            'peerAvgAmount'                    => (double) $collapsed_peers_row->peer_avg_amount,
                            'targetAmount'                     => (double) $collapsed_peers_row->target_amount,
                            'name'                             => $collapsed_peers_row->name,
                            'code'                             => $this->getNewCodeFromDeprecatedCode($collapsed_peers_row->deprecated_code),
                            'targetArea'                       => (double) $collapsed_peers_row->target_rentable_area,
                            'peerAvgArea'                      => (double) $collapsed_peers_row->peer_sum_rentable_area / $peer_count,
                            'report_template_account_group_id' => $this->getReportTemplateAccountGroupIdFromDeprecatedCode($collapsed_peers_row->deprecated_code),
                        ];

                        $this->payload[] = new PeerAveragePropertyGroup($payload_item_data_arr);
                    }
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
     * @param $deprecated_waypoint_code
     * @return string
     */
    protected function getNewCodeFromDeprecatedCode($deprecated_waypoint_code): string
    {
        return $this->ReportTemplateObj
            ->reportTemplateAccountGroups
            ->where('deprecated_waypoint_code', $deprecated_waypoint_code)
            ->first()
            ->report_template_account_group_code;
    }

    /**
     * @return array
     * @throws GeneralException
     */
    private function setDefaultTargetDetails(): array
    {
        return [
            'apiTitle'        => $this->apiTitle,
            'name'            => $this->ReportTemplateAccountGroupObj->display_name,
            'code'            => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'fromDate'        => $this->getFromDate($this->year, $this->period),
            'toDate'          => $this->getToDate($this->year, $this->period),
            'period'          => $this->period,
            'entityName'      => $this->entityName,
            'totalBarUnits'   => 'expense',
            'units'           => $this->units,
            'entityType'      => $this->entityType,
            'entityOccupancy' => $this->occupancy,
            'amount'          => 0,
        ];
    }

    /**
     * @return string
     * @throws GeneralException
     */
    public function getRankField($area = null): string
    {
        if ( ! $this->PeerAverageRepository->usableArea())
        {
            throw new GeneralException('unusable area');
        }
        return 'RANK_TARGET_PEER_AMOUNT_' . self::AREA_LOOKUP[$this->area] . '_DOUBLE';
    }

    /**
     * @param $result
     * @return bool
     */
    private function isTarget($result)
    {
        return $result->deprecated_code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
    }

    /**
     * @param $result
     * @return bool
     */
    private function isChild($result)
    {
        return in_array($result->deprecated_code, $this->ReportTemplateAccountGroupObj->getChildrenDeprecatedReportTemplateAccountGroupsCodes());
    }

    /**
     * @param $results
     * @return bool
     */
    private function onlyParentResultPresent(Collection $results)
    {
        return $results->count() == $this->filterAllLineItemsExceptForTarget($results)->count();
    }

    private function filterAllLineItemsExceptForTarget(Collection $results)
    {
        return $results->filter(function ($item)
        {
            return $item->deprecated_code == $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code;
        });
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField($area = null): string
    {
        if ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('unusable report given');
        }
        return 'TARGET_' . ($area ? $area : $this->area) . '_AREA';
    }

    /**
     * @param $result
     * @return string
     */
    private function generateMD5($result)
    {
        return md5($result->property_group_id . $this->area . $this->year . $this->report . $result->name);
    }
}
