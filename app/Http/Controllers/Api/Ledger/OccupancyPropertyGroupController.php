<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\Ledger\Occupancy;
use App\Waypoint\Repositories\Ledger\OccupancyRepository;

/**
 * Class OccupancyPropertyGroupController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 */
class OccupancyPropertyGroupController extends LedgerController
{
    /** @var  OccupancyRepository */
    private $OccupancyRepository;

    /**
     * OperatingExpensesPropertyController constructor.
     * @param OccupancyRepository $OccupancyRepo
     */
    public function __construct(OccupancyRepository $OccupancyRepo)
    {
        $this->OccupancyRepository = $OccupancyRepo;

        // Presumption about what the period/area/report should be
        $this->OccupancyRepository->period = self::YEAR_TO_DATE_ABBREV;
        $this->OccupancyRepository->area   = self::RENTABLE_SELECTION;
        $this->OccupancyRepository->report = self::ACTUAL;

        parent::__construct($OccupancyRepo);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param $year
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function index($client_id, $property_group_id, $year)
    {
        try
        {
            /** @var Client $ClientObj */
            $ClientObj = $this->OccupancyRepository->ClientObj = $this->getClientObject();

            $this->OccupancyRepository->LedgerControllerObj = $this;

            if ( ! $this->OccupancyRepository->PropertyGroupObj = PropertyGroup::find($property_group_id))
            {
                throw new GeneralException('property_group_id is invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }

            $property_id_old_arr = $this->OccupancyRepository->PropertyGroupObj->getAllProperties()->pluck('property_id_old')->toArray();
            if ( ! $occupancy_payload = $this->OccupancyRepository->getGroupAverageOccupancy($property_id_old_arr, $year, true))
            {
                throw new GeneralException('no property group occupancy available, possibly group calc is running and data is temporarily unavailable');
            }

            $payload   = [
                'PERCENT_OCC'   => $occupancy_payload['occupancyPercentage'],
                'RENTABLE_AREA' => $occupancy_payload['RENTABLE_AREA'],
                'OCCUPIED_AREA' => $occupancy_payload['OCCUPIED_AREA'],
                'asOfDate'      => $ClientObj->get_client_asof_date(),
            ];
            $Occupancy = collect(new Occupancy($payload));

            return $this->sendResponse(
                $Occupancy->toArray(),
                'occupancy for property generated successfully',
                [], [], []

            );
        }
        catch (GeneralException $e)
        {
            return $this->sendResponse(
                [],
                'unsuccessful benchmark data generation',
                $e->getMessage(),
                [],
                [
                    'apiTitle'    => 'PropertyGroupOccupancy',
                    'displayName' => 'Property Group Occupancy',
                    'count'       => 0,
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
                    'apiTitle'    => 'PropertyGroupOccupancy',
                    'displayName' => 'Property Group Occupancy',
                    'count'       => 0,
                ]
            );
        }
    }
}