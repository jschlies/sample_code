<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Repository as BaseRepository;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Connection;

/**
 * Class LedgerRepository
 */
class LedgerRepository extends BaseRepository
{
    /** @var null|Client */
    public $ClientObj = null;

    /** @var null|LedgerController */
    public $LedgerControllerObj = null;

    /** @var null|Property */
    public $PropertyObj = null;

    /** @var null|PropertyGroup */
    public $PropertyGroupObj = null;

    /** @var null|PropertyGroup */
    public $UserAllPropertyGroup = null;

    /** @var null|ReportTemplateAccountGroup */
    public $ReportTemplateAccountGroupObj = null;

    /** @var null|array */
    public $square_footage_lookup_arr = null;

    /** @var null|Connection */
    public $DatabaseConnectionObj = null;

    /** @var null|Connection */
    public $LedgerDatabaseConnectionObj = null;

    /** @var null|Connection */
    public $PeerDatabaseConnectionObj = null;

    /** @var null|Connection */
    public $StagingDatabaseConnectionObj = null;

    public $WaypointMetadataDatabaseConnection = null;

    /** @var null|DB */
    private $InformationSchemaDatabaseConnection = null;

    /** @var null */
    public $original_yoy_group_table = null;

    /** @var null */
    public $new_yoy_group_table = null;

    /** @var null */
    public $area = null;

    /** @var null */
    public $year = null;

    /** @var null */
    public $period = null;

    /** @var null */
    public $report = null;

    /** @var array */
    protected $incomplete_property_id_old_arr = [];

    /** @var null|integer */
    protected $incomplete_data_properties_count = null;

    public $renaming_occupancy_table = null;

    /** @var ReportTemplate null */
    public $ReportTemplateObj = null;
    /** @var ReportTemplateRepository null */
    public $ReportTemplateRepositoryObj = null;

    /** @var $ClientRepository App\Waypoint\Repositories\ClientRepository */
    protected $ClientRepository;

    /**
     * LedgerRepository constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        // TODO: ALEX - make sure this is pulling client specific default report template
        $this->ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);
    }

    /** @return mixed */
    public function model()
    {
        return Ledger::class;
    }

    const VERY_LOW_RANK = 1000000;

    /**
     * @return array|bool
     * @throws GeneralException
     */
    public function getPeerAverageNote()
    {
        if ( ! $this->PropertyObj)
        {
            throw new LedgerException('missing property object');
        }

        if ( ! $this->year)
        {
            throw new LedgerException('missing year');
        }

        $peer_note = $this->getWaypointMetadataDatabaseConnection()
                          ->table('PropertyMetadataByYear')
                          ->where(
                              [
                                  ['propertyID', $this->PropertyObj->property_id_old],
                                  ['year', $this->year],
                              ]
                          )
                          ->value('peerNotes');

        $peer_table = (new LedgerController($this))->getCorrectPeerTableBasedOnAvailabilityStatus(
            $this->ClientObj, 'PEER_GROUP_CALC_CLIENT_' . $this->ClientObj->client_id_old . '_YEARLY_RANK'
        );

        $result = $this->getPeerDatabaseConnection()
                       ->table($peer_table)
                       ->where(
                           [
                               ['TARGET_PROPERTY_ID', $this->PropertyObj->property_id_old],
                               ['FROM_YEAR', $this->year],
                               ['ACCOUNT_CODE', LedgerController::OPERATING_EXPENSES_DEFAULT_CODE],
                               ['BENCHMARK_TYPE', LedgerController::ACTUAL],
                           ]
                       )
                       ->select(
                           'PEER_PROPERTY_COUNT as peer_count',
                           'AVG_TARGET_PEER_DISTANCE as peer_avg_distance',
                           $this->getPeerAreaField() . ' as peer_area',
                           'AVG_PEER_RENTABLE_AREA as peer_avg_rentable_area',
                           'AVG_PEER_OCCUPIED_AREA as peer_avg_occupied_area'
                       )
                       ->first();

        if (empty($result))
        {
            return false;
        }

        return [
            'peer_note'          => $peer_note,
            'peer_count'         => $result->peer_count,
            'peer_avg_distance'  => $result->peer_avg_distance > 100 ? null : (float) $result->peer_avg_distance,
            'peer_area'          => (float) $result->peer_area,
            'peer_avg_occupancy' => empty($result->peer_avg_rentable_area) ? null : ($result->peer_avg_occupied_area / $result->peer_avg_rentable_area) * 100,
        ];
    }

    private function getPeerAreaField()
    {
        if ( ! $this->usableArea())
        {
            throw new LedgerException('unusable area given');
        }

        return 'AVG_PEER_' . $this->area . '_AREA';
    }

    protected function getWaypointMetadataDatabaseConnection()
    {
        if ( ! $this->WaypointMetadataDatabaseConnection)
        {
            if ( ! $this->WaypointMetadataDatabaseConnection = DB::connection('mysql_WAYPOINT_METADATA'))
            {
                throw new LedgerException('could not connection to waypoint metadata database');
            }
        }
        return $this->WaypointMetadataDatabaseConnection;
    }

    /**
     * @return \Illuminate\Database\Connection|null
     */
    protected function setInformationSchemaDatabaseConnection()
    {
        if ( ! $this->InformationSchemaDatabaseConnection = DB::connection('mysql_BENCHMARK_INFORMATION_SCHEMA'))
        {
            throw new LedgerException('could not connection to information schema database');
        }
        return $this->InformationSchemaDatabaseConnection;
    }

    /**
     * @return \Illuminate\Database\Connection|null
     */
    public function getLedgerDatabaseConnection()
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ($this->LedgerDatabaseConnectionObj)
        {
            return $this->LedgerDatabaseConnectionObj;
        }
        return $this->setLedgerDatabaseConnection();
    }

    /**
     * @return \Illuminate\Database\Connection|null
     */
    protected function setLedgerDatabaseConnection()
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ( ! $this->LedgerDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_LEDGER_' . $this->ClientObj->client_id_old))
        {
            throw new LedgerException('cannot make connection to ledger database');
        }
        return $this->LedgerDatabaseConnectionObj;
    }

    /**
     * @return Connection
     */
    public function getGroupDatabaseConnection()
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ($this->DatabaseConnectionObj)
        {
            return $this->DatabaseConnectionObj;
        }
        return $this->setGroupDatabaseConnection();
    }

    /**
     * @return DB|Connection|null
     */
    public function getInformationSchemaDatabaseConnection()
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ($this->InformationSchemaDatabaseConnection)
        {
            return $this->InformationSchemaDatabaseConnection;
        }
        return $this->setInformationSchemaDatabaseConnection();
    }

    /**
     * @return \Illuminate\Database\Connection|null
     */
    protected function setGroupDatabaseConnection()
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ( ! $this->DatabaseConnectionObj = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $this->ClientObj->client_id_old))
        {
            throw new LedgerException('cannot make connection to group database');
        }
        return $this->DatabaseConnectionObj;
    }

    /**
     * @return Connection
     */
    public function getPeerDatabaseConnection(bool $enableQueryLog = null)
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }
        if ($this->PeerDatabaseConnectionObj)
        {
            return $this->PeerDatabaseConnectionObj;
        }
        return $this->setPeerDatabaseConnection($enableQueryLog);
    }

    /**
     * @return \Illuminate\Database\Connection|null
     */
    protected function setPeerDatabaseConnection(bool $enableQueryLog = null)
    {
        if ( ! $this->ClientObj)
        {
            throw new LedgerException('unusable client object');
        }

        try
        {
            $this->PeerDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_PEER_AVERAGE_' . $this->ClientObj->client_id_old);
            if ($enableQueryLog)
            {
                $this->PeerDatabaseConnectionObj->enableQueryLog();
            }
            $this->PeerDatabaseConnectionObj->getPdo();
        }
        catch (Exception $e)
        {
            throw new LedgerException('could not find peer database');
        }
        return $this->PeerDatabaseConnectionObj;
    }

    /**
     * @param $table_name
     * @return bool
     */
    protected function tableExists($table_name)
    {
        $results = $this->getInformationSchemaDatabaseConnection()
                        ->table('TABLES')
                        ->where('TABLE_NAME', $table_name)
                        ->select('TABLE_NAME')
                        ->first();

        return count($results) > 0;
    }

    /**
     * @return bool
     */
    public function usablePeriod()
    {
        return ! empty($this->period) && in_array($this->period, LedgerController::ACCEPTABLE_PERIODS);
    }

    /**
     * @return bool
     */
    public function usableArea($area = null)
    {
        if ( ! is_null($area))
        {
            return ! empty($area) && in_array($area, LedgerController::ACCEPTABLE_AREAS);
        }
        return ! empty($this->area) && in_array($this->area, LedgerController::ACCEPTABLE_AREAS);
    }

    /**
     * @return bool
     */
    public function usableReport()
    {
        return in_array($this->report, LedgerController::ACCEPTABLE_REPORTS, true);
    }

    /* ---------------------------------------------------
     |  Occupancy
     |  --------------------------------------------------
     |  - getGroupAverageOccupancy()
     |  - getOccupancyForSingleProperty()
     |  - getOccupancyForEachProperty()
     |  - getOccupancyFromSquareFootage()
     */

    /**
     * @param [] $property_id_old_arr
     * @param $year
     * @return float|int|array
     */
    public function getGroupAverageOccupancy($property_id_old_arr, $year, $include_square_footage = false)
    {
        if (empty($this->ClientObj))
        {
            throw new LedgerException('missing client object');
        }

        if ( ! $this->usablePeriod())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableArea())
        {
            throw new LedgerException('unsuable area given');
        }

        if ( ! $this->usableReport())
        {
            throw new LedgerException('unsuable report given');
        }

        if ( ! $occupancy_table_name = $this->LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj,
            'OCCUPANCY_MONTH_CALCULATED_' . $this->ClientObj->client_id_old
        )
        )
        {
            return false;
        }

        $result = $this->getGroupDatabaseConnection()
                       ->table($occupancy_table_name)
                       ->whereIn('AREA_FROM_PYTHON_ID', $this->getCompositeKey($property_id_old_arr, $year))
                       ->select(
                           DB::raw('SUM(RENTABLE_AREA) as rentable_area_sum'),
                           DB::raw('SUM(OCCUPIED_AREA) as occupied_area_sum')
                       )
                       ->first();

        if ( ! empty($result))
        {
            if ($include_square_footage)
            {
                return [
                    'RENTABLE_AREA'            => (float) $result->rentable_area_sum,
                    'OCCUPIED_AREA'            => (float) $result->occupied_area_sum,
                    'group_avg_rentable_sq_ft' => $result->rentable_area_sum / count($property_id_old_arr),
                    'group_avg_occupied_sq_ft' => $result->occupied_area_sum / count($property_id_old_arr),
                    'occupancyPercentage'      => ! empty((float) $result->rentable_area_sum) ? ((float) $result->occupied_area_sum / (float) $result->rentable_area_sum) * 100 : 0,
                ];
            }
            return ! empty((float) $result->rentable_area_sum) ? ((float) $result->occupied_area_sum / (float) $result->rentable_area_sum) * 100 : 0;
        }
        else
        {
            throw new LedgerException('no occupancy available for this group');
        }
    }

    public function getGroupAverageSquareFootage($property_id_old_arr, $year)
    {
        if (empty($property_id_old_arr))
        {
            throw new LedgerException('property id list empty');
        }

        if (empty($this->ClientObj))
        {
            throw new LedgerException('missing client object');
        }

        if ( ! $this->usablePeriod())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableArea())
        {
            throw new LedgerException('unsuable area given');
        }

        if ( ! $this->usableReport())
        {
            throw new LedgerException('unsuable report given');
        }

        if ( ! $occupancy_table_name = $this->LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj,
            'OCCUPANCY_MONTH_CALCULATED_' . $this->ClientObj->client_id_old
        )
        )
        {
            return false;
        }

        $result = $this->getGroupDatabaseConnection()
                       ->table($occupancy_table_name)
                       ->whereIn('AREA_FROM_PYTHON_ID', $this->getCompositeKey($property_id_old_arr, $year))
                       ->select(
                           DB::raw('SUM(' . $this->area . '_AREA) as area_sum')
                       )
                       ->first();

        if ( ! empty($result))
        {
            return $result->area_sum / count($property_id_old_arr);
        }
        else
        {
            throw new LedgerException('no square footage available for this group');
        }
    }

    public function getGroupSumSquareFootage($property_id_old_arr, $year, $area = null)
    {
        if (empty($property_id_old_arr))
        {
            throw new LedgerException('property id list empty');
        }

        if (empty($this->ClientObj))
        {
            throw new LedgerException('missing client object');
        }

        if ( ! $this->usablePeriod())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableArea())
        {
            throw new LedgerException('unsuable area given');
        }

        if ( ! $this->usableReport())
        {
            throw new LedgerException('unsuable report given');
        }

        if ( ! $occupancy_table_name = $this->LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj,
            'OCCUPANCY_MONTH_CALCULATED_' . $this->ClientObj->client_id_old
        )
        )
        {
            return false;
        }

        $result = $this->getGroupDatabaseConnection()
                       ->table($occupancy_table_name)
                       ->whereIn('AREA_FROM_PYTHON_ID', $this->getCompositeKey($property_id_old_arr, $year))
                       ->select(
                           DB::raw('SUM(' . ($area ? $area : $this->area) . '_AREA) as area_sum')
                       )
                       ->first();

        if ( ! empty($result))
        {
            return $result->area_sum;
        }
        else
        {
            throw new LedgerException('no square footage available for this group');
        }
    }

    /**
     * @param integer $property_id_old
     * @param $year
     * @param bool $including_square_footage
     * @return float|mixed
     * @throws GeneralException
     */
    public function getOccupancyForSingleProperty($property_id_old, $year, $including_square_footage = false)
    {
        if (empty($this->ClientObj))
        {
            throw new LedgerException('missing client object');
        }

        if ($including_square_footage)
        {
            return current($this->getOccupancyForEachProperty([$property_id_old], $year, $including_square_footage));
        }
        return (double) current($this->getOccupancyForEachProperty([$property_id_old], $year));
    }

    /**
     * @param array $property_id_old_arr
     * @param integer $year
     * @param bool $including_square_footage
     * @return array
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getOccupancyForEachProperty($property_id_old_arr, $year, $including_square_footage = false)
    {
        if (empty($this->ClientObj))
        {
            throw new LedgerException('missing client object');
        }

        if ( ! $this->usablePeriod())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableArea())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableReport())
        {
            throw new LedgerException('unsuable report given');
        }

        $payload = [];

        /**
         * new controller???? This logic should be pushed to a repository or more
         * likely Model
         */
        if ( ! $occupancy_table_name = $this->LedgerControllerObj->getCorrectTableBasedOnAvailabilityStatus(
            $this->ClientObj, 'OCCUPANCY_MONTH_CALCULATED_' . $this->ClientObj->client_id_old
        ))
        {
            $this->renaming_occupancy_table = true;

            $as_of_date = $this->LedgerControllerObj->get_client_asof_date($this->ClientObj->id);
            foreach ($property_id_old_arr as $property_id_old)
            {
                $payload[$property_id_old] = [
                    'PERCENT_OCC'   => null,
                    'RENTABLE_AREA' => null,
                    'OCCUPIED_AREA' => null,
                    'ADJUSTED_AREA' => null,
                    'asOfDate'      => $as_of_date,
                ];
            }

            return $payload;
        }

        $results = $this->getGroupDatabaseConnection()
                        ->table($occupancy_table_name)
                        ->whereIn('AREA_FROM_PYTHON_ID', $this->getCompositeKey($property_id_old_arr, $year))
                        ->select(
                            'FK_PROPERTY_ID as property_id_old',
                            'RENTABLE_AREA as rentable_area',
                            'OCCUPIED_AREA as occupied_area',
                            'ADJUSTED_AREA as adjusted_area'
                        )
                        ->groupBy('AREA_FROM_PYTHON_ID')
                        ->get();

        if ($results->count() > 0)
        {
            $as_of_date = $this->LedgerControllerObj->get_client_asof_date($this->ClientObj->id);
            foreach ($results as $result)
            {
                if ($including_square_footage)
                {
                    $payload[$result->property_id_old] = [
                        'PERCENT_OCC'   => ! empty((float) $result->rentable_area) ? ((float) $result->occupied_area / (float) $result->rentable_area) * 100 : 0,
                        'RENTABLE_AREA' => (float) $result->rentable_area,
                        'OCCUPIED_AREA' => (float) $result->occupied_area,
                        'ADJUSTED_AREA' => (float) $result->adjusted_area,
                        'asOfDate'      => $as_of_date,
                    ];
                }
                else
                {
                    $payload[$result->property_id_old] = ! empty((float) $result->rentable_area) ? ((float) $result->occupied_area / (float) $result->rentable_area) * 100 : 0;
                }
            }
        }
        return $payload;
    }

    /**
     * @param array $property_id_old_arr
     * @param integer $year
     * @return array
     * @throws GeneralException
     */
    private function getCompositeKey($property_id_old_arr, $year)
    {
        $composite_primary_key_arr = [];
        $composite_key_data_type   = $this->getInformationSchemaDatabaseConnection()
                                          ->table('columns')
                                          ->where(
                                              [
                                                  ['table_name', 'OCCUPANCY_MONTH_CALCULATED_' . $this->ClientObj->client_id_old],
                                                  ['column_name', 'AREA_FROM_PYTHON_ID'],
                                              ]
                                          )
                                          ->value('data_type');

        if ($composite_key_data_type == 'varchar')
        {
            foreach ($property_id_old_arr as $property_id_old)
            {
                $composite_primary_key_arr[] = $property_id_old . $this->getBenchmarkType() . $year;
            }
        }
        elseif ($composite_key_data_type == 'bigint')
        {
            $benchmark_type_int = $this->getGroupDatabaseConnection()
                                       ->table('BENCHMARK_TYPE_MAPPING')
                                       ->where('BENCHMARK_TYPE', $this->getBenchmarkType())
                                       ->value('BENCHMARK_TYPE_INT');

            foreach ($property_id_old_arr as $property_id_old)
            {
                $composite_primary_key_arr[] = (int) $property_id_old . $benchmark_type_int . $year;
            }

        }
        else
        {
            throw new LedgerException('unusable data type given for occupancy composite key');
        }

        return $composite_primary_key_arr;
    }

    /**
     * @param $rentableSqFt
     * @param $occupiedSqFt
     * @return float|int
     */
    protected function getOccupancyFromSquareFootage($rentableSqFt, $occupiedSqFt)
    {
        $rentableSqFt = (float) $rentableSqFt;
        $occupiedSqFt = (float) $occupiedSqFt;
        return empty($rentableSqFt) ? 0 : ($occupiedSqFt / $rentableSqFt) * 100;
    }

    // END: occupancy -------------------------

    /**
     * @return string
     */
    protected function getBenchmarkType()
    {
        if ( ! $this->usablePeriod())
        {
            throw new LedgerException('unsuable period given');
        }

        if ( ! $this->usableReport())
        {
            throw new LedgerException('unsuable report given');
        }

        // variance benchmark type
        if (is_null($this->report))
        {
            return LedgerController::VARIANCE_BENCHMARK_TYPE_LOOKUP[$this->period];
        }

        // all other benchmark types
        return $this->period == LedgerController::CALENDAR_YEAR_ABBREV ? $this->report : $this->report . '_' . LedgerController::PERIOD_LOOKUP[$this->period];
    }

    /**
     * @param $results
     * @param ReportTemplateAccountGroup|null $ReportTemplateAccountGroupObj
     * @return mixed
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    protected function filterIncompleteDataResults($results, ReportTemplateAccountGroup $ReportTemplateAccountGroupObj = null)
    {
        /**
         * @todo please doc
         */
        if ( ! $this->PropertyGroupObj && $this->UserAllPropertyGroup)
        {
            $this->PropertyGroupObj = $this->UserAllPropertyGroup;
        }
        if ( ! $ReportTemplateAccountGroupObj && ! $this->ReportTemplateAccountGroupObj)
        {
            throw new LedgerException('aux coa coa line item code missing');
        }
        elseif ( ! $ReportTemplateAccountGroupObj && $this->ReportTemplateAccountGroupObj)
        {
            $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupObj;
        }

        if ( ! $this->square_footage_lookup_arr)
        {
            $property_id_old_arr             = $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
            $this->square_footage_lookup_arr = $this->getOccupancyForEachProperty($property_id_old_arr, $this->year, true);
        }

        // filter results with rentable property area of zero or null
        $results = $results->filter(
            function ($result)
            {
                if ($this->renaming_occupancy_table)
                {
                    return true;
                }
                return isset($this->square_footage_lookup_arr[$result->property_id]) && ! is_null(
                        $this->square_footage_lookup_arr[$result->property_id][$this->area . '_AREA']
                    ) && (float) $this->square_footage_lookup_arr[$result->property_id][$this->area . '_AREA'] != 0;
            }
        );

        // filter results with header code color of zero
        $results = $results_with_no_color_zero_headers = $results->filter(
            function ($result)
            {
                return $result->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                true)->deprecated_waypoint_code && $result->color == 0 ? false : true;
            }
        );

        /**
         * if header line item is the target then skip over these conditions
         *
         * @todo this section is slow - See HER-3300
         */
        if ($ReportTemplateAccountGroupObj->deprecated_waypoint_code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                true)->deprecated_waypoint_code)
        {
            // filter results with missing headers
            $results = $results->filter(
                function ($result) use ($results_with_no_color_zero_headers, $ReportTemplateAccountGroupObj)
                {
                    // if lower order line item result
                    if ($result->code == $ReportTemplateAccountGroupObj->deprecated_waypoint_code)
                    {
                        // get header if exists
                        $header = $results_with_no_color_zero_headers->filter(
                            function ($item) use ($result)
                            {
                                return $item->property_id == $result->property_id && $item->code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                                            true)->deprecated_waypoint_code;
                            }
                        );
                        return $header->count() != 0;
                    }
                    return true;
                }
            );

            // remove entries of the header code which are only present to compare against for these filter conditions
            $results = $results->filter(
                function ($result) use ($ReportTemplateAccountGroupObj)
                {
                    return $result->code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                    true)->deprecated_waypoint_code;
                }
            );
        }

        return $results;
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField(): string
    {
        if ( ! $this->area && in_array($this->area, LedgerController::ACCEPTABLE_AREAS))
        {
            throw new LedgerException('unusable report given');
        }
        return $this->area . '_AREA';
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyGroupSumAreaField(): string
    {
        if ( ! $this->area && in_array($this->area, LedgerController::ACCEPTABLE_AREAS))
        {
            throw new LedgerException('unusable report given');
        }
        return 'GROUP_SUM_' . $this->area . '_AREA';
    }

    /**
     * @param integer $client_id
     * @return Carbon
     * @throws GeneralException
     */
    public function get_client_asof_date($client_id)
    {
        if ( ! $Client = Client::find($client_id))
        {
            throw new GeneralException('Invalid Client ID', App\Waypoint\Http\Controllers\Api\Ledger\LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }
        if ( ! empty($this->client_as_of_date))
        {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->client_as_of_date;
        }

        $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);
        $result             = $DatabaseConnection
            ->table('TARGET_ASOF_MONTH')
            ->select(
                'TARGET_ASOF_MONTH.FROM_YEAR as FROM_YEAR',
                'TARGET_ASOF_MONTH.COVERED_YEAR as COVERED_YEAR',
                'TARGET_ASOF_MONTH.MOY as MOY',
                'TARGET_ASOF_MONTH.YEARMONTH as YEARMONTH',
                'TARGET_ASOF_MONTH.FROM_MONTH as FROM_MONTH'
            )
            ->first();
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Carbon::create($result->FROM_YEAR, $result->MOY, 1, 0, 0, 0)->modify('last day of this month');
    }

}
