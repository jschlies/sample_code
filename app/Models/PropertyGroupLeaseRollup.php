<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;

class PropertyGroupLeaseRollup extends PropertyGroup
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

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
        $TenantDetailObjArr =
            $this->properties
                ->map(

                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->tenantDetails;
                    }
                )
                ->flatten()
                ->unique('id');
        /**
         * this needs to stay in close sync with PropertyLeaseRollup
         */
        return [
            "id"                                         => $this->id,
            "name"                                       => $this->name,

            /** activeLease */
            "activeLeaseDetails"                         => $this->getActiveLeaseDetailObjArr()->toArray(),
            "active_num_leases"                          => $this->getActiveLeaseDetailObjArr()->count(),
            "active_square_footage"                      => $this->getActiveLeaseDetailObjArr()->sum('square_footage'),
            "active_monthly_rent_per_square_foot"        => $this->square_footage ? $this->getActiveLeaseDetailObjArr()->sum('monthly_rent') / $this->square_footage : null,
            "active_annual_rent_per_square_foot"         => $this->square_footage ? $this->getActiveLeaseDetailObjArr()->sum('monthly_rent') * 12 / $this->square_footage : null,
            "active_annual_rent"                         => $this->getActiveLeaseDetailObjArr()->sum('monthly_rent') * 12,
            "active_monthly_rent"                        => $this->getActiveLeaseDetailObjArr()->sum('monthly_rent'),
            "active_occupancy_rate"                      => $this->square_footage
                ?
                ($this->getActiveLeaseDetailObjArr()->sum('square_footage') * 100 / $this->square_footage)
                :
                null,

            /** activeUniqueLease */
            "activeUniqueLeaseDetails"                   => $this->getActiveUniqueLeaseDetailObjArr()->toArray(),
            "active_unique_num_leases"                   => $this->getActiveUniqueLeaseDetailObjArr()->count(),
            "active_unique_square_footage"               => $this->getActiveUniqueLeaseDetailObjArr()->sum('square_footage'),
            "active_unique_monthly_rent_per_square_foot" => $this->square_footage ? $this->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent') / $this->square_footage : null,
            "active_unique_annual_rent_per_square_foot"  => $this->square_footage ? $this->getActiveUniqueLeaseDetailObjArr()
                                                                                         ->sum('monthly_rent') * 12 / $this->square_footage : null,
            "active_unique_annual_rent"                  => $this->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent') * 12,
            "active_unique_monthly_rent"                 => $this->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent'),
            "active_unique_occupancy_rate"               => $this->square_footage
                ?
                ($this->getActiveUniqueLeaseDetailObjArr()->sum('square_footage') * 100 / $this->square_footage)
                :
                null,

            'tenantIndustriesDetails' =>
                $TenantDetailObjArr
                    ->map(
                        function (TenantDetail $TenantDetailObj)
                        {
                            return $TenantDetailObj->tenantIndustryDetail;
                        }
                    )->unique()
                    ->toArray(),

            'tenantDetails' => $TenantDetailObjArr,

            'weighted_average_lease_expiration' => $this->calculate_weighted_average_lease_expiration($this->getActiveUniqueLeaseDetailObjArr()),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
