<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\Compare;
use App\Waypoint\Repositories\Ledger\CompareRepository;
use DB;

/**
 * Class ComparePropertyDeprecatedController
 * @package App\Waypoint\Http\Controllers\Ledger
 * @codeCoverageIgnore
 */
class ComparePropertyDeprecatedController extends LedgerController
{
    /** @var  CompareRepository */
    private $CompareRepositoryObj;

    protected $occupancy_payload_arr = [];

    protected $query_result = null;

    /**
     * ComparePropertyController constructor.
     * @param CompareRepository $CompareRepositoryObj
     */
    public function __construct(CompareRepository $CompareRepositoryObj)
    {
        parent::__construct($CompareRepositoryObj);
        $this->CompareRepositoryObj = $CompareRepositoryObj;
    }

    /**
     * @param integer $property_id
     * @param integer $report
     * @param integer $year
     * @param integer $period
     * @param integer $area
     * @param $report_template_account_group_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index(
        $property_id,
        $report,
        $year,
        $period,
        $area,
        $report_template_account_group_id
    ) {
        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        $this->BomaReportTemplateObj = $this->CompareRepositoryObj->ClientObj->bomaReportTemplate;

        if ( ! $this->occupancy_payload_arr = $this->CompareRepositoryObj->getOccupancyForSingleProperty($this->PropertyObj->property_id_old, $year, true))
        {
            throw new LedgerException('could not find property occupancy');
        }

        $this->perform_query();
        $this->package_data();

        return $this->sendResponse(
            $this->payload,
            'compare benchmark data generated successfully',
            [],
            $this->warnings,
            [
                'count' => count($this->payload),
            ]
        );
    }

    protected function package_data()
    {
        if ( ! empty($this->query_result))
        {
            $this->query_result                               = (array) $this->query_result;
            $this->query_result['Entity']                     = $this->PropertyObj;
            $this->query_result['ReportTemplateAccountGroup'] = $this->ReportTemplateAccountGroupObj;
            $this->query_result['rentable_area']              = $this->occupancy_payload_arr['RENTABLE_AREA'];
            $this->query_result['occupied_area']              = $this->occupancy_payload_arr['OCCUPIED_AREA'];
            $this->payload                                    = collect(new Compare($this->query_result))->toArray();
        }
        else
        {
            $this->warnings = 'no results found';
        }
    }

    protected function perform_query()
    {
        $this->query_result = DB::connection('mysql_WAYPOINT_LEDGER_' . $this->CompareRepositoryObj->ClientObj->client_id_old)
                                ->table('BENCHMARK_LEVELS')
                                ->where(
                                    [
                                        ['BENCHMARK_LEVELS.FROM_YEAR', $this->year],
                                        ['BENCHMARK_LEVELS.BENCHMARK_TYPE', $this->getBenchmarkType($this->report, $this->period)],
                                        ['CLIENT_BENCHMARKS.FK_PROPERTY_ID', $this->PropertyObj->property_id_old],
                                        ['CLIENT_BENCHMARKS.ACCOUNT_CODE', $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
                                    ]
                                )
                                ->join('CLIENT_BENCHMARKS', 'BENCHMARK_LEVELS.BENCHMARK_ID', '=', 'CLIENT_BENCHMARKS.FK_BENCHMARK_ID')
                                ->select(
                                    "CLIENT_BENCHMARKS." . $this->getAmountFieldFromArea($this->area) . " as amount",
                                    "CLIENT_BENCHMARKS.FK_PROPERTY_ID as property_id"
                                )
                                ->first();
    }
}
