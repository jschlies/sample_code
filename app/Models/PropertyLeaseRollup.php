<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;

/**
 * Class PropertyDetail
 * @package App\Waypoint\Models
 */
class PropertyLeaseRollup extends Property
{
    use WeightedAverageLeaseExpirationTrait;

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        return [];
    }

    public static $rules = [
        /**
         * @todo tighten this up
         */
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */

    public function toArray(): array
    {
        /**
         * this needs to stay in close sync with PropertyGroupLeaseRollup
         */
        $TenantDetailObjArr = collect_waypoint(
            $this->suiteDetails
                ->map(
                    function (SuiteDetail $SuiteDetailObj)
                    {
                        return $SuiteDetailObj->tenantDetails;
                    }
                )->flatten()
        );

        $ActiveLeaseDetailObjArr       = $this->getActiveLeaseDetailObjArr();
        $ActiveUniqueLeaseDetailObjArr = $this->getActiveUniqueLeaseDetailObjArr();

        $weighted_average_lease_expiration = $this->calculate_weighted_average_lease_expiration($ActiveUniqueLeaseDetailObjArr);

        $tenant_details_arr = $TenantDetailObjArr->toArray();

        return [
            "id"                                         => $this->id,
            "property_name"                              => $this->name,
            "square_footage"                             => $this->square_footage,
            "suite_id_arr"                               => $this->suiteDetails->sortBy('id')->pluck('id')->toArray(),

            /** activeLease */
            "activeLeaseDetails"                         => $ActiveLeaseDetailObjArr->toArray(),
            "active_num_leases"                          => $ActiveLeaseDetailObjArr->count(),
            "active_square_footage"                      => $ActiveLeaseDetailObjArr->sum('square_footage'),
            "active_monthly_rent_per_square_foot"        => $this->square_footage ? $ActiveLeaseDetailObjArr->sum('monthly_rent') / $this->square_footage : null,
            "active_annual_rent_per_square_foot"         => $this->square_footage ? $ActiveLeaseDetailObjArr->sum('monthly_rent') * 12 / $this->square_footage : null,
            "active_annual_rent"                         => $ActiveLeaseDetailObjArr->sum('monthly_rent') * 12,
            "active_monthly_rent"                        => $ActiveLeaseDetailObjArr->sum('monthly_rent'),
            "active_occupancy_rate"                      => $this->square_footage ? ($ActiveLeaseDetailObjArr
                                                                                         ->sum('square_footage') * 100 / $this->square_footage) : null,

            /** activeUniqueLease */
            "activeUniqueLeaseDetails"                   => $ActiveUniqueLeaseDetailObjArr->toArray(),
            "active_unique_num_leases"                   => $ActiveUniqueLeaseDetailObjArr->count(),
            "active_unique_square_footage"               => $ActiveUniqueLeaseDetailObjArr->sum('square_footage'),
            "active_unique_monthly_rent_per_square_foot" => $this->square_footage ? $ActiveUniqueLeaseDetailObjArr->sum('monthly_rent') / $this->square_footage : null,
            "active_unique_annual_rent_per_square_foot"  => $this->square_footage ? $ActiveUniqueLeaseDetailObjArr
                                                                                        ->sum('monthly_rent') * 12 / $this->square_footage : null,
            "active_unique_annual_rent"                  => $ActiveUniqueLeaseDetailObjArr->sum('monthly_rent') * 12,
            "active_unique_monthly_rent"                 => $ActiveUniqueLeaseDetailObjArr->sum('monthly_rent'),
            "active_unique_occupancy_rate"               => $this->square_footage ? ($ActiveUniqueLeaseDetailObjArr
                                                                                         ->sum('square_footage') * 100 / $this->square_footage) : null,
            'tenantIndustriesDetails'                    =>
                $TenantDetailObjArr
                    ->map(
                        function (TenantDetail $TenantDetailObj)
                        {
                            return $TenantDetailObj->tenantIndustryDetail;
                        }
                    )
                    ->unique('id')
                    ->toArray(),

            'tenantDetails' => $tenant_details_arr,

            'weighted_average_lease_expiration' => $weighted_average_lease_expiration,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
