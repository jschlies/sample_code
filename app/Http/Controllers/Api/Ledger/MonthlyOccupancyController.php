<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\Ledger\OccupancyRepository;
use App;

/**
 * Class MonthlyOccupancyController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 */
class MonthlyOccupancyController extends LedgerController
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
        parent::__construct($OccupancyRepo);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param $year
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws App\Waypoint\Exceptions\LedgerException
     */
    public function getMonthlyOccupancyForProperty($client_id, $property_id, $year)
    {
        $payload = $this->OccupancyRepository->getMonthlyOccupancyProperty($client_id, $property_id, $year);

        return $this->sendResponse(
            $payload,
            'occupancy for property generated successfully',
            [],
            [],
            [
                'year_average_occupancy_rate' => $this->OccupancyRepository->getYearAvgOccupany($payload),
                'year_to_date_percentage_point_change' => $this->OccupancyRepository->getYearToDatePercentagePointChange($payload)
            ]
        );
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @param $year
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getMonthlyOccupancyForPropertyGroup($client_id, $property_group_id, $year)
    {
        $payload = $this->OccupancyRepository->getMonthlyOccupancyPropertyGroup($client_id, $property_group_id, $year);

        return $this->sendResponse(
            $payload,
            'occupancy for property group generated successfully',
            [],
            [],
            [
                'year_average_occupancy_rate' => $this->OccupancyRepository->getYearAvgOccupany($payload),
                'year_to_date_percentage_point_change' => $this->OccupancyRepository->getYearToDatePercentagePointChange($payload)
            ]
        );
    }

}
