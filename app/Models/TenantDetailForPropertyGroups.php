<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;

/**
 * Class TenantDetail
 * @package App\Waypoint\Models
 */
class TenantDetailForPropertyGroups extends Tenant
{
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /**
     * The property id list of the property group in question
     */
    public static $properties_id_arr = [];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * TenantDetail constructor.
     * @param array $attributes
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        /**
         * per Laura, the TenantDetail is built using TenantActiveLease, not TenantActiveLeaseUnique
         */
        $UserRepository = App::make(UserRepository::class);
        if ($UserRepository->getLoggedInUser())
        {
            $user_accessable_property_id_arr = $UserRepository->getLoggedInUser()->getAccessiblePropertyIdArr();
        }
        else
        {
            throw new App\Waypoint\Exceptions\GeneralException('No logged in user found, Is this being called via artisan');
        }

        $TenantActiveLeaseObjArr                =
            $this->leaseDetails
                ->filter(
                    function (LeaseDetail $LeaseDetailObj) use ($user_accessable_property_id_arr)
                    {
                        return in_array($LeaseDetailObj->property_id, $user_accessable_property_id_arr);
                    }
                )
                ->unique('id');
        $TenantActiveLeaseInPropertyGroupObjArr =
            $TenantActiveLeaseObjArr
                ->filter(function (LeaseDetail $LeaseDetailObj)
                {
                    return in_array($LeaseDetailObj->property_id, self::$properties_id_arr);
                });

        $tenant_total_square_footage_across_user_portfolio = $TenantActiveLeaseObjArr->sum('square_footage');
        $tenant_total_square_footage_across_property_group     = $TenantActiveLeaseInPropertyGroupObjArr->sum('square_footage');

        $tenant_total_monthly_rent_across_user_portfolio = $TenantActiveLeaseObjArr->sum('monthly_rent');
        $tenant_active_leases_count                      = $TenantActiveLeaseObjArr->count();
        $user_portfolio_total_square_footage             = $this->client->properties
            ->whereIn('id', $user_accessable_property_id_arr)
            ->sum('square_footage');

        $avg_annual_in_place_rent = $tenant_active_leases_count
            ? $tenant_total_monthly_rent_across_user_portfolio * 12 / $tenant_active_leases_count
            : 0;

        $weighted_average_lease_expiration = $this->calculate_weighted_average_lease_expiration($TenantActiveLeaseObjArr);

        $return_me = [
            "id"          => $this->id,
            "name"        => $this->name,
            "description" => $this->description,

            "lease_id_arr"                => $TenantActiveLeaseObjArr->pluck('id'),
            "active_lease_id_arr"         => $TenantActiveLeaseObjArr->pluck('id'),
            'active_monthly_base_rent'    => $tenant_total_monthly_rent_across_user_portfolio,
            'active_total_square_footage' => $tenant_total_square_footage_across_property_group,

            'percent_occupied_sq_ft_of_portfolio_sq_ft' =>
                $user_portfolio_total_square_footage
                    ?
                    100 * $tenant_total_square_footage_across_user_portfolio / $user_portfolio_total_square_footage
                    :
                    null,

            'average_tenant_in_place_monthly_rent' => $tenant_active_leases_count && $tenant_total_square_footage_across_user_portfolio
                ?
                $TenantActiveLeaseObjArr->map(
                    function (LeaseDetail $LeaseDetailObj) use ($tenant_total_square_footage_across_user_portfolio)
                    {
                        return $tenant_total_square_footage_across_user_portfolio ? ($LeaseDetailObj->monthly_rent * $LeaseDetailObj->square_footage) / $tenant_total_square_footage_across_user_portfolio : 0;
                    }
                )->sum()
                :
                null,

            'avg_annual_in_place_rent' => $avg_annual_in_place_rent,

            'avg_annual_in_place_rent_per_sqft' => $tenant_total_square_footage_across_user_portfolio
                ? $avg_annual_in_place_rent / $tenant_total_square_footage_across_user_portfolio
                : 0,

            "suite_id_arr"           => $this->suiteDetails->whereIn('property_id', $user_accessable_property_id_arr)->pluck('id'),
            "tenant_industry_id"     => $this->tenant_industry_id,
            "tenantIndustryDetail"   => $this->tenantIndustryDetail ? $this->tenantIndustryDetail->toArray() : null,
            "industry"               => $this->tenantIndustryDetail ? $this->tenantIndustryDetail->name : null,
            "tenantAttributeDetails" => $this->tenantAttributeDetails->toArray(),

            "total_suite_square_footage" => $this->suiteDetails->whereIn('property_id', $user_accessable_property_id_arr)->sum('square_footage'),
            "total_lease_square_footage" => $tenant_total_square_footage_across_user_portfolio,
            'total_annual_in_place_rent' => $tenant_total_monthly_rent_across_user_portfolio * 12,

            'property_id_arr' => $TenantActiveLeaseObjArr->pluck('property_id'),

            'weighted_average_lease_expiration'             => $weighted_average_lease_expiration,
            'average_in_place_leases_square_footage'        =>
                $tenant_total_square_footage_across_user_portfolio
                    ?
                    $tenant_total_monthly_rent_across_user_portfolio / $tenant_total_square_footage_across_user_portfolio
                    :
                    0,
            'average_annual_in_place_leases_square_footage' =>
                $tenant_total_square_footage_across_user_portfolio
                    ?
                    $tenant_total_monthly_rent_across_user_portfolio * 12 / $tenant_total_square_footage_across_user_portfolio
                    :
                    0,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];

        return $return_me;
    }
}
