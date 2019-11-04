<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Models\Ledger\Compare;
use App\Waypoint\Repositories\Ledger\CompareRepository;

/**
 * Class ComparePropertyGroupController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 */
class ComparePropertyGroupController extends LedgerController
{
    /** @var  CompareRepository */
    private $CompareRepositoryObj;

    protected $occupancy_payload_arr = null;

    /**
     * OperatingExpensesController constructor.
     * @param CompareRepository $CompareRepositoryObj
     */
    public function __construct(CompareRepository $CompareRepositoryObj)
    {
        $this->CompareRepositoryObj = $CompareRepositoryObj;
        parent::__construct($CompareRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param integer $year
     * @param integer $year
     * @param $period
     * @param $area
     * @param integer $report_template_account_group_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index(
        $client_id,
        $property_group_id,
        $report,
        $year,
        $period,
        $area,
        $report_template_account_group_id
    ) {

        $this->initializeClientObject();
        $this->isDefaultAnalyticsReportTemplate();

        $this->occupancy_payload_arr = $this->CompareRepositoryObj->getGroupAverageOccupancy(
            $this->PropertyGroupObj->properties->pluck('property_id_old')->toArray(),
            $this->year,
            true
        );

        $this->perform_query();
        $this->package_data();

        return $this->sendResponse(
            $this->payload,
            'compare benchmark data generated successfully',
            [],
            $this->warnings,
            [
                'count' => count($this->payload),
            ]);
    }

    protected function package_data()
    {
        if ( ! empty($this->query_result))
        {
            $this->query_result                               = (array) $this->query_result;
            $this->query_result['Entity']                     = $this->PropertyGroupObj;
            $this->query_result['ReportTemplateAccountGroup'] = $this->ReportTemplateAccountGroupObj;
            $this->query_result['totalGroupSqFt']             = $this->occupancy_payload_arr['RENTABLE_AREA'];
            $this->query_result['rentable_area']              = $this->occupancy_payload_arr['group_avg_rentable_sq_ft'];
            $this->query_result['occupied_area']              = $this->occupancy_payload_arr['group_avg_occupied_sq_ft'];
            $this->payload                                    = collect(new Compare($this->query_result))->toArray();
        }
        else
        {
            $this->warnings = 'no results found';
        }
    }

    protected function getLedgerTableName()
    {
        if ($this->ledger_table_name)
        {
            return $this->ledger_table_name;
        }

        $this->ledger_table_name = $this->getCorrectTableBasedOnAvailabilityStatus(
            $this->CompareRepositoryObj->ClientObj,
            'GROUP_CALC_CLIENT_' . $this->CompareRepositoryObj->ClientObj->client_id_old . '_YEARLY_FINAL'
        );
        return $this->ledger_table_name;
    }

    protected function perform_query()
    {
        $this->query_result = $this->CompareRepositoryObj->getGroupDatabaseConnection()
                                                         ->table($this->getLedgerTableName())
                                                         ->where(
                                                             [
                                                                 ['FROM_YEAR', $this->year],
                                                                 ['BENCHMARK_TYPE', $this->getBenchmarkType($this->report, $this->period)],
                                                                 ['REF_GROUP_ID', $this->PropertyGroupObj->id],
                                                                 ['ACCOUNT_CODE', $this->ReportTemplateAccountGroupObj->deprecated_waypoint_code],
                                                             ]
                                                         )
                                                         ->select(
                                                             $this->getGroupAmountField($this->area) . ' as amount',
                                                             'FROM_YEAR as year'
                                                         )
                                                         ->first();
    }
}
