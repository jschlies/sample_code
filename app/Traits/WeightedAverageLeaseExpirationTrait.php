<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Models\LeaseDetail;
use App\Waypoint\Models\SuiteDetail;
use Carbon\Carbon;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\Ledger\OccupancyRepository;

/**
 * Class WeightedAverageLeaseExpirationTrait
 * @package App\Waypoint\Models
 *
 */
trait WeightedAverageLeaseExpirationTrait
{
    /**
     * @param $LeaseDetailObjArr
     * @return mixed
     */
    private function calculate_weighted_average_lease_expiration(Collection $LeaseDetailObjArr)
    {
        $total_square_footage = $LeaseDetailObjArr->map(
            function (LeaseDetail $LeaseDetailObj)
            {
                $lease_square_footage = $LeaseDetailObj->suiteDetails->map(
                    function (SuiteDetail $SuiteDetailObj)
                    {
                        return $SuiteDetailObj->square_footage ?: 0;
                    }
                )->sum();
                return $lease_square_footage;
            }
        )->sum();

        $weighted_average_lease_expiration = $LeaseDetailObjArr->map(
            function (LeaseDetail $LeaseDetailObj) use ($total_square_footage)
            {
                /**
                 * @todo talk to Peter - oddball case.
                 */
                if ( ! $LeaseDetailObj->lease_expiration_date)
                {
                    return 0;
                }
                $square_footage_of_leases_in_question = $LeaseDetailObj->suiteDetails->map(
                    function (SuiteDetail $SuiteDetailObj)
                    {
                        return $SuiteDetailObj->square_footage ?: 0;
                    }
                )->sum();
                if ($square_footage_of_leases_in_question == 0)
                {
                    return 0;
                }
                $area_ratio               = $total_square_footage ? $square_footage_of_leases_in_question / $total_square_footage : 0;
                $years_remaining_on_lease = Carbon::createFromTimeString($LeaseDetailObj->lease_expiration_date)->DiffInDays(Carbon::now()) / 365;

                return $area_ratio * $years_remaining_on_lease;
            }
        )->sum();

        // IF multifamily
        if (
            $LeaseDetailObjArr->count() > 0
            &&
            $LeaseDetailObjArr->first()->property->client->getConfigValue('ASSET_TYPE') == Client::MULTIFAMILY_ASSET_TYPE_TEXT
        )
        {
            // change from yearly to monthly value by multiplying by 12
            $weighted_average_lease_expiration = $weighted_average_lease_expiration * OccupancyRepository::MONTHS_IN_A_YEAR;
        }

        return $weighted_average_lease_expiration;
    }
}
