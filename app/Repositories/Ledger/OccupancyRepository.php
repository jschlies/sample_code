<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\Ledger\Occupancy;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use Carbon\Carbon;
use DB;
use Exception;

/**
 * Class OccupancyRepository
 */
class OccupancyRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    CONST MONTHS_IN_A_YEAR = 12;

    public function getAsOfMonthOccupancyDetailsForProperties($property_id_old_array)
    {
        list($rentable_area, $occupied_area) = $this->getAsOfMonthSquareFootageForProperties($property_id_old_array);

        return [
            'rentable_area'        => $rentable_area,
            'occupied_area'        => $occupied_area,
            'occupancy_percentage' => $rentable_area == 0 ? 0 : ($occupied_area / $rentable_area) * 100,
        ];
    }

    public function getAsOfMonthSquareFootageForProperties($property_id_old_array)
    {
        if (empty($this->ClientObj))
        {
            throw new GeneralException('missing client object');
        }

        $results = $this->getLedgerDatabaseConnection()
                        ->table('OCCUPANCY_PERCENT')
                        ->whereIn('FK_PROPERTY_ID', $property_id_old_array)
                        ->select(DB::raw("SUM(`RENTABLE_AREA`) as rentable_area, SUM(`OCCUPIED_AREA`) as occupied_area"))
                        ->get();

        if ($results->count() == 0)
        {
            throw new GeneralException('could not find square footage for this property');
        }

        return [
            (float) $results->first()->rentable_area,
            (float) $results->first()->occupied_area,
        ];
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @param $year
     * @return array
     */
    public function getMonthlyOccupancyPropertyGroup($client_id, $property_group_id, $year): array
    {
        if ( ! $this->ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new LedgerException('cannot find client from this client id', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        if ( ! $this->PropertyGroupObj = App::make(PropertyGroupRepository::class)->find($property_group_id))
        {
            throw new LedgerException('cannot find property group details from this property_group_id', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        $payload = [];
        $results = $this->getLedgerDatabaseConnection()
                        ->table('OCCUPANCY_MONTH')
                        ->select([
                                     'FK_PROPERTY_ID',
                                     'FROM_YEAR',
                                     'FROM_MONTH',
                                     'RENTABLE_AREA',
                                     'OCCUPIED_AREA',
                                 ])
                        ->whereIn('FK_PROPERTY_ID', $this->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray())
                        ->where('FROM_YEAR', '=', $year)
                        ->get();

        if ($results->count() == 0)
        {
            return [];
        }

        for ($i = 1; $i <= self::MONTHS_IN_A_YEAR; $i++)
        {
            $monthly_property_group_result = $results->filter(function ($result) use ($i)
            {
                return date_parse($result->FROM_MONTH)['month'] == $i;
            });

            if (empty($monthly_property_group_result))
            {
                continue;
            }

            // zero out months beyond the as-of-date, as there can be projected data
            if ($this->isBeyondAsOfDate(new Carbon("$year-$i-1")))
            {
                $rentable_area_sum = $occupied_area_sum = 0;
            }
            else
            {
                $rentable_area_sum = $monthly_property_group_result->pluck('RENTABLE_AREA')->sum();
                $occupied_area_sum = $monthly_property_group_result->pluck('OCCUPIED_AREA')->sum();
            }

            $payload[] = [
                'propertyGroupId' => $this->PropertyGroupObj->id,
                'FROM_YEAR'       => (int) $year,
                'FROM_MONTH'      => $monthly_property_group_result->first()->FROM_MONTH,
                'RENTABLE_AREA'   => $rentable_area_sum,
                'OCCUPIED_AREA'   => $occupied_area_sum,
                'OCCUPANCY_RATE'  => $this->calculateOccupancyRate($rentable_area_sum, $occupied_area_sum),
            ];
        }

        $this->sortMonthAscending($payload);

        return $payload;
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $year
     * @return array
     */
    public function getMonthlyOccupancyProperty($client_id, $property_id, $year)
    {
        if ( ! $this->ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new LedgerException('cannot find client from this client id', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        if ( ! $this->PropertyObj = App::make(PropertyRepository::class)->find($property_id))
        {
            throw new LedgerException('cannot find property from this property_group_id', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        $payload = [];
        $results = $this->getLedgerDatabaseConnection()
                        ->table('OCCUPANCY_MONTH')
                        ->select([
                                     'FK_PROPERTY_ID',
                                     'FROM_YEAR',
                                     'FROM_MONTH',
                                     'RENTABLE_AREA',
                                     'OCCUPIED_AREA',
                                 ])
                        ->where('FK_PROPERTY_ID', '=', $this->PropertyObj->property_id_old)
                        ->where('FROM_YEAR', '=', $year)
                        ->get()
                        ->toArray();

        if (count($results) == 0)
        {
            return [];
        }

        if (count($results) > self::MONTHS_IN_A_YEAR)
        {
            throw new LedgerException('duplicates present', LedgerController::HTTP_ERROR_RESPONSE_CODE);
        }

        foreach ($results as $result)
        {
            $result = (array) $result;

            // zero out months beyond the as-of-date, as there can be projected data
            if ($this->isBeyondAsOfDate(new Carbon("$year-" . $result['FROM_MONTH'] . '-1')))
            {
                $result['RENTABLE_AREA'] = $result['OCCUPIED_AREA'] = $result['OCCUPANCY_RATE'] = 0;
            }
            else
            {
                if (is_string($result['RENTABLE_AREA']) || is_string($result['OCCUPIED_AREA']))
                {
                    $result['RENTABLE_AREA'] = (float) $result['RENTABLE_AREA'];
                    $result['OCCUPIED_AREA'] = (float) $result['OCCUPIED_AREA'];
                }

                $result['OCCUPANCY_RATE'] = $this->calculateOccupancyRate($result['RENTABLE_AREA'], $result['OCCUPIED_AREA']);
            }

            $payload[] = $result;
        }

        $this->sortMonthAscending($payload);
        return $payload;
    }

    /**
     * @param Carbon $date
     * @return bool
     */
    private function isBeyondAsOfDate(Carbon $date): bool
    {
        try
        {
            $as_of_date = $this->get_client_asof_date($this->ClientObj->id);
        }
        catch (Exception $e)
        {
            self::$model_as_of_date = Carbon::now();
        }
        return $date->greaterThan($as_of_date);
    }

    /**
     * @param $rentable_area
     * @param $occupied_area
     * @return float
     */
    private function calculateOccupancyRate($rentable_area, $occupied_area): float
    {
        return $rentable_area <= 0 ? 0 : ($occupied_area / $rentable_area) * 100;
    }

    /**
     * @param $monthly_occupancy_data_arr
     * @return float
     */
    public function getYearAvgOccupany($monthly_occupancy_data_arr): float
    {
        return count($monthly_occupancy_data_arr) == 0
            ?
            0
            :
            (float) array_sum(array_pluck($monthly_occupancy_data_arr, 'OCCUPANCY_RATE')) / count($monthly_occupancy_data_arr);
    }

    /**
     * @param $monthly_occupancy_data_arr
     * @return float
     */
    public function getYearToDatePercentagePointChange($monthly_occupancy_data_arr): float
    {
        return (float) array_last($monthly_occupancy_data_arr)['OCCUPANCY_RATE'] - array_first($monthly_occupancy_data_arr)['OCCUPANCY_RATE'];
    }

    /**
     * @param $list
     */
    private function sortMonthAscending($list)
    {
        usort(
            $list,
            function ($a, $b)
            {
                $monthA = date_parse($a['FROM_MONTH']);
                $monthB = date_parse($b['FROM_MONTH']);
                return $monthA['month'] - $monthB['month'];
            }
        );
    }

    /**
     * @return mixed
     **/
    public function model()
    {
        return Occupancy::class;
    }
}
