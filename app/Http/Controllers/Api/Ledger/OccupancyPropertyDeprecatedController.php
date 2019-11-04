<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\Occupancy;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\Ledger\OccupancyRepository;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Client;
use Exception;

/**
 * Class OccupancyPropertyDeprecatedController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 * @codeCoverageIgnore
 */
class OccupancyPropertyDeprecatedController extends LedgerController
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

        // set defaults
        $this->OccupancyRepository->period = self::YEAR_TO_DATE_ABBREV;
        $this->OccupancyRepository->area   = self::RENTABLE_SELECTION;
        $this->OccupancyRepository->report = self::ACTUAL;

    }

    /**
     * @param integer $property_id
     * @param $year
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index($property_id, $year)
    {
        try
        {
            /** @var Client $ClientObj */
            $ClientObj                                      = $this->OccupancyRepository->ClientObj = $this->getClientObject();
            $this->OccupancyRepository->LedgerControllerObj = $this;

            /** @var Property $Property */
            if ( ! $Property = Property::find($property_id))
            {
                throw new GeneralException('property_id invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }

            if ( ! $occupancy_payload = $this->OccupancyRepository->getOccupancyForSingleProperty($Property->property_id_old, $year, true))
            {
                throw new GeneralException('no property group occupancy available, possibly a group calc is running and the data is temporarily unavailable.');
            }

            $payload   = [
                'PERCENT_OCC'   => $occupancy_payload['PERCENT_OCC'],
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
            return $this->sendResponse([], 'unsuccessful benchmark data generation', $e->getMessage(), [], []);
        }
        catch (Exception $e)
        {
            return $this->sendResponse([], 'unsuccessful benchmark data generation', $e->getMessage(), [], []);
        }
    }

    /**
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getAsOfMonthOccupancyProperty($property_id)
    {
        return $this->getAsOfMonthOccupancy($property_id);
    }

    /**
     * @param integer $property_group_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getAsOfMonthOccupancyPropertyGroup($property_group_id)
    {
        return $this->getAsOfMonthOccupancy($property_group_id, true);
    }

    /**
     * @param integer $entity_id
     */
    private function getAsOfMonthOccupancy($entity_id, $is_group = false)
    {
        if ($is_group)
        {
            /** @var PropertyGroup $PropertyGroupObj */
            if ( ! $PropertyGroupObj = PropertyGroup::find($entity_id))
            {
                throw new GeneralException('property_id invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }
            $property_id_old_array                = $PropertyGroupObj->properties->pluck('property_id_old')->toArray();
            $this->OccupancyRepository->ClientObj = $PropertyGroupObj->client;
        }
        else
        {
            /** @var Property $PropertyObj */
            if ( ! $PropertyObj = Property::find($entity_id))
            {
                throw new GeneralException('property_id invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }
            $property_id_old_array                = [$PropertyObj->property_id_old];
            $this->OccupancyRepository->ClientObj = $PropertyObj->client;
        }

        if ( ! $occupancy_payload = $this->OccupancyRepository->getAsOfMonthOccupancyDetailsForProperties($property_id_old_array))
        {
            throw new GeneralException('no property group occupancy available, possibly a group calc is running and the data is temporarily unavailable.',
                                       self::HTTP_ERROR_RESPONSE_CODE);
        }

        $payload = [
            'PERCENT_OCC'   => $occupancy_payload['occupancy_percentage'],
            'RENTABLE_AREA' => $occupancy_payload['rentable_area'],
            'OCCUPIED_AREA' => $occupancy_payload['occupied_area'],
            'asOfDate'      => $this->OccupancyRepository->ClientObj->get_client_asof_date(),
        ];

        $OccupancyObjArr = collect(new Occupancy($payload));

        return $this->sendResponse(
            $OccupancyObjArr->toArray(),
            'as-of-month occupancy for property ' . ($is_group ? 'group' : '') . ' generated successfully'
        );
    }

}
