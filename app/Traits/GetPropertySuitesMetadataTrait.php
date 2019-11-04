<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\SuiteDetail;
use App\Waypoint\Models\User;
use Carbon\Carbon;

trait GetPropertySuitesMetadataTrait
{
    /**
     * it is presumed that WeightedAverageLeaseExpirationTrait has been pulled in in thwe calling controller
     */

    /**
     * @param $PropertyObj
     * @param null|Carbon $lease_as_of_date
     * @param null|Carbon $lease_from_date
     * @param null|Carbon $lease_to_date
     * @return array
     */
    public function get_property_suites_metadata(Property $PropertyObj, $lease_as_of_date = null, $lease_from_date = null, $lease_to_date = null): array
    {
        $weighted_average_lease_expiration =
            $this->calculate_weighted_average_lease_expiration(
                $PropertyObj->getActiveUniqueLeaseDetailObjArr()
            );

        $metadata = [

            /** activeLease */
            "active_num_leases"     => $PropertyObj->getActiveLeaseDetailObjArr()->count(),
            "active_square_footage" => $PropertyObj->getActiveLeaseDetailObjArr()->sum('square_footage'),

            "active_monthly_rent_per_square_foot" =>
                $PropertyObj->square_footage
                    ?
                    $PropertyObj->getActiveLeaseDetailObjArr()->sum('monthly_rent') / $PropertyObj->square_footage
                    :
                    null,
            "active_annual_rent_per_square_foot"  =>
                $PropertyObj->square_footage
                    ?
                    $PropertyObj->getActiveLeaseDetailObjArr()->sum('monthly_rent') * 12 / $PropertyObj->square_footage
                    :
                    null,

            "active_annual_rent"  => $PropertyObj->getActiveLeaseDetailObjArr()->sum('monthly_rent') * 12,
            "active_monthly_rent" => $PropertyObj->getActiveLeaseDetailObjArr()->sum('monthly_rent'),

            "active_occupancy_rate"        =>
                $PropertyObj->square_footage
                    ? ($PropertyObj->getActiveLeaseDetailObjArr()->sum('square_footage') * 100 / $PropertyObj->square_footage)
                    :
                    null,

            /** activeUniqueLease */
            "active_unique_num_leases"     => $PropertyObj->getActiveUniqueLeaseDetailObjArr()->count(),
            "active_unique_square_footage" => $PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('square_footage'),

            "active_unique_monthly_rent_per_square_foot" =>
                $PropertyObj->square_footage
                    ?
                    $PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent') / $PropertyObj->square_footage
                    :
                    null,
            "active_unique_annual_rent_per_square_foot"  =>
                $PropertyObj->square_footage
                    ?
                    $PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent') * 12 / $PropertyObj->square_footage
                    :
                    null,
            "active_unique_annual_rent"                  => $PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent') * 12,
            "active_unique_monthly_rent"                 => $PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('monthly_rent'),
            "active_unique_occupancy_rate"               =>
                $PropertyObj->square_footage
                    ?
                    ($PropertyObj->getActiveUniqueLeaseDetailObjArr()->sum('square_footage') * 100 / $PropertyObj->square_footage)
                    :
                    null,
            'weighted_average_lease_expiration'          => $weighted_average_lease_expiration,
        ];

        if ($lease_as_of_date)
        {
            $metadata['lease_as_of_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_as_of_date);
        }
        if ($lease_from_date)
        {
            $metadata['lease_from_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_from_date);
        }
        if ($lease_to_date)
        {
            $metadata['lease_to_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_to_date);
        }
        return $metadata;
    }

    /**
     * @param $PortfolioPropertyObjArr
     * @param null|Carbon $lease_as_of_date
     * @param null|Carbon $lease_from_date
     * @param null|Carbon $lease_to_date
     * @return array
     */
    private function get_property_arr_suites_metadata($PortfolioPropertyObjArr, $lease_as_of_date = null, $lease_from_date = null, $lease_to_date = null): array
    {
        /**
         * @var SuiteDetail $SuiteDetailObj
         * @var Collection $ActiveLeaseObjArr
         */
        $ActiveLeaseDetailObjArr = $PortfolioPropertyObjArr->map(
            function ($PropertyObj)
            {
                /** @var Property $PropertyObj */
                return $PropertyObj->getActiveLeaseDetailObjArr();
            }
        )->flatten();

        /** @var Collection $PortfolioActiveUniqueLeaseDetailObjArr */
        $PortfolioActiveUniqueLeaseDetailObjArr = collect_waypoint(
            $PortfolioPropertyObjArr->map(
                function ($PropertyObj)
                {
                    return $PropertyObj->getActiveUniqueLeaseDetailObjArr();
                }
            )->flatten()
        );

        $weighted_average_lease_expiration = $this->calculate_weighted_average_lease_expiration($PortfolioActiveUniqueLeaseDetailObjArr);

        $metadata = [
            /** activeLease */
            "active_num_leases"                          => $ActiveLeaseDetailObjArr->count(),
            "active_square_footage"                      => $ActiveLeaseDetailObjArr->sum('square_footage'),
            "active_monthly_rent"                        => $ActiveLeaseDetailObjArr->sum('monthly_rent'),
            "active_monthly_rent_per_square_foot"        =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    $ActiveLeaseDetailObjArr->sum('monthly_rent') / $PortfolioPropertyObjArr->sum('square_footage')
                    :
                    0,
            "active_annual_rent"                         => $ActiveLeaseDetailObjArr->sum('monthly_rent') * 12,
            "active_annual_rent_per_square_foot"         =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    $ActiveLeaseDetailObjArr->sum('monthly_rent') * 12 / $PortfolioPropertyObjArr->sum('square_footage')
                    :
                    null,
            "active_occupancy_rate"                      =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    $ActiveLeaseDetailObjArr->sum('square_footage') * 100 / $PortfolioPropertyObjArr->sum('square_footage')
                    :
                    0,

            /** activeUniqueLease */
            "active_unique_num_leases"                   => $PortfolioActiveUniqueLeaseDetailObjArr->count(),
            "active_unique_square_footage"               => $PortfolioActiveUniqueLeaseDetailObjArr->sum('square_footage'),
            "active_unique_monthly_rent"                 => $PortfolioActiveUniqueLeaseDetailObjArr->sum('monthly_rent'),
            "active_unique_monthly_rent_per_square_foot" =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    $PortfolioActiveUniqueLeaseDetailObjArr->sum('monthly_rent') / $PortfolioPropertyObjArr->sum('square_footage')
                    :
                    0,
            "active_unique_annual_rent"                  => $PortfolioActiveUniqueLeaseDetailObjArr->sum('monthly_rent') * 12,
            "active_unique_annual_rent_per_square_foot"  =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    $PortfolioActiveUniqueLeaseDetailObjArr->sum('monthly_rent') * 12 / $PortfolioPropertyObjArr->sum('square_footage')
                    :
                    null,
            "active_unique_occupancy_rate"               =>
                $PortfolioPropertyObjArr->sum('square_footage')
                    ?
                    ($PortfolioActiveUniqueLeaseDetailObjArr->sum('square_footage') * 100 / $PortfolioPropertyObjArr->sum('square_footage'))
                    :
                    0,

            'lease_as_of_date' =>
                $PortfolioPropertyObjArr->first()
                    ?
                    ModelDateFormatterTrait::perhaps_format_date(Lease::get_model_as_of_date($PortfolioPropertyObjArr->first()->client_id))
                    :
                    ModelDateFormatterTrait::perhaps_format_date(Lease::get_model_as_of_date()),

            'weighted_average_lease_expiration' => $weighted_average_lease_expiration,
        ];

        if ($lease_as_of_date)
        {
            $metadata['lease_as_of_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_as_of_date);
        }
        if ($lease_from_date)
        {
            $metadata['lease_from_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_from_date);
        }
        if ($lease_to_date)
        {
            $metadata['lease_to_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_to_date);
        }
        return $metadata;
    }

    /**
     * @param Collection $PortfolioPropertyObjArr
     * @param null|Carbon $lease_as_of_date
     * @param null|Carbon $lease_from_date
     * @param null|Carbon $lease_to_date
     * @return array
     */
    private function get_tenant_arr_metadata(Collection $PortfolioPropertyObjArr, $lease_as_of_date = null, $lease_from_date = null, $lease_to_date = null): array
    {
        /** @var User $CurrentLoggedInUserObj */
        $CurrentLoggedInUserObj = $this->getCurrentLoggedInUserObj();
        /** just in case */
        $PortfolioPropertyObjArr =
            $PortfolioPropertyObjArr
                ->whereIn(
                    'id',
                    $CurrentLoggedInUserObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()
                );

        $TenantDetailObjArr = $PortfolioPropertyObjArr->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->getActiveUniqueLeaseDetailObjArr()->map(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return $LeaseDetailObj->tenantDetails;
                    }
                );
            }
        )->flatten()->unique('id');;

        /**
         * get all tenant types of all tenants across $PropertyObjArr
         */
        $PortfolioTenantIndustriesObjArr =
            $PortfolioPropertyObjArr
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->getActiveUniqueLeaseDetailObjArr()->map(
                            function (LeaseDetail $LeaseDetailObj)
                            {
                                if ($LeaseDetailObj->tenantDetails->first())
                                {
                                    return $LeaseDetailObj->tenantDetails->first()->tenantIndustryDetail;
                                }
                                else
                                {
                                    return null;
                                }
                            }
                        );
                    }
                )
                ->flatten()
                /** deal with nulls */
                ->filter(
                    function ($TenantIndustryDetailObj)
                    {
                        return $TenantIndustryDetailObj;
                    }
                )
                /** deal w/ dups */
                ->unique('id');

        /**
         * get hash of all active leases in $PortfolioPropertyObj Arr hashed by $TenantIndustriesObj->name
         */
        $PortfolioIndustryHashActiveUniqueLeaseDetailObjArr = new Collection();
        foreach ($PortfolioTenantIndustriesObjArr as $TenantIndustriesDetailObj)
        {
            $PortfolioIndustryHashActiveUniqueLeaseDetailObjArr[$TenantIndustriesDetailObj->name] =
                $PortfolioPropertyObjArr->map(
                    function (Property $PropertyObj) use ($TenantIndustriesDetailObj)
                    {
                        return $PropertyObj->getActiveUniqueLeaseDetailObjArr()->filter(
                            function (LeaseDetail $LeaseDetailObj) use ($TenantIndustriesDetailObj)
                            {
                                if (
                                    $LeaseDetailObj->tenantDetails->count() &&
                                    $LeaseDetailObj->tenantDetails->first()->tenant_industry_id == $TenantIndustriesDetailObj->id)
                                {
                                    return Lease::check_model_date_range($LeaseDetailObj);
                                }
                                return false;
                            }
                        );
                    }
                )->flatten();
        }

        /**
         * get hash of all leases active uniqueleases in $PropertyObjArr
         */
        $PortfolioActiveUniqueLeaseDetailObjArr = $PortfolioPropertyObjArr->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->getActiveUniqueLeaseDetailObjArr();
            }
        )->flatten();

        $portfolio_total_square_footage = $PortfolioActiveUniqueLeaseDetailObjArr->map(
            function (Lease $LeaseObj)
            {
                return $LeaseObj->square_footage;
            }
        )->sum();

        $metadata['portfolio_num_tenants'] = $TenantDetailObjArr->count();

        $metadata['portfolio_annual_in_place_monthly_rent'] =
            $PortfolioActiveUniqueLeaseDetailObjArr->sum('monthly_rent') * 12;

        $metadata['portfolio_avg_annual_in_place_rent'] =
            $PortfolioActiveUniqueLeaseDetailObjArr->count()
                ?
                $metadata['portfolio_annual_in_place_monthly_rent'] / $PortfolioActiveUniqueLeaseDetailObjArr->count()
                :
                0;

        $metadata['portfolio_tenant_average_square_footage'] =
            $metadata['portfolio_num_tenants']
                ?
                $PortfolioActiveUniqueLeaseDetailObjArr->sum('square_footage') / $metadata['portfolio_num_tenants']
                :
                0;

        /** Fix me */
        $metadata['portfolio_average_in_place_per_square_footage'] =
            $portfolio_total_square_footage
                ?
                $metadata['portfolio_annual_in_place_monthly_rent'] / $portfolio_total_square_footage
                :
                0;

        $metadata['portfolio_weighted_average_lease_expiration'] = $this->calculate_weighted_average_lease_expiration($PortfolioActiveUniqueLeaseDetailObjArr);

        $metadata['portfolio_total_square_footage'] = $portfolio_total_square_footage;

        /******************************************************/

        $metadata['industries'] = [];
        foreach ($PortfolioTenantIndustriesObjArr as $TenantIndustryDetailObj)
        {
            $industry_total_square_footage = $PortfolioPropertyObjArr->map(
                function (Property $PropertyObj)
                {
                    return $PropertyObj->suiteDetails->pluck('square_footage')->sum();
                }
            )->sum();
            /**
             * $PortfolioIndustryHashActiveLeaseDetailObjArr is a hash of collections by industry
             */
            $PortfolioIndustryActiveUniqueLeaseDetailObjArr = $PortfolioIndustryHashActiveUniqueLeaseDetailObjArr[$TenantIndustryDetailObj->name];

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_num_tenants'] =
                $PortfolioIndustryActiveUniqueLeaseDetailObjArr
                    ->map(
                        function (LeaseDetail $LeaseDetailObj)
                        {
                            return $LeaseDetailObj->tenantDetails->count() ? $LeaseDetailObj->tenantDetails->first() : null;
                        }
                    )
                    /** deal with nulls */
                    ->filter(
                        function ($TenantDetailObj)
                        {
                            return $TenantDetailObj;
                        }
                    )
                    /** deal w/ dups */
                    ->unique('id')
                    ->count();

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_tenant_average_square_footage'] =
                $metadata['industries'][$TenantIndustryDetailObj->name]['industry_num_tenants']
                    ?
                    $PortfolioIndustryActiveUniqueLeaseDetailObjArr->sum('square_footage') / $metadata['industries'][$TenantIndustryDetailObj->name]['industry_num_tenants']
                    :
                    0;

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_annual_in_place_monthly_rent'] =
                $PortfolioIndustryActiveUniqueLeaseDetailObjArr
                    ->map(
                        function (LeaseDetail $LeaseDetailObj)
                        {
                            if ($LeaseDetailObj->monthly_rent)
                            {
                                return $LeaseDetailObj->monthly_rent * 12;
                            }
                            return 0;
                        }
                    )->sum();

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_average_annual_in_place_per_square_footage'] =
                $industry_total_square_footage
                    ?
                    $metadata['portfolio_annual_in_place_monthly_rent'] / $industry_total_square_footage
                    :
                    0;

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_in_place_monthly_rent'] =
                $PortfolioIndustryActiveUniqueLeaseDetailObjArr
                    ->map(
                        function (LeaseDetail $LeaseDetailObj)
                        {
                            if ($LeaseDetailObj->monthly_rent)
                            {
                                return $LeaseDetailObj->monthly_rent;
                            }
                            return 0;
                        }
                    )->sum();

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_average_in_place_per_square_footage'] =
                $industry_total_square_footage
                    ?
                    $metadata['industries'][$TenantIndustryDetailObj->name]['industry_in_place_monthly_rent'] / $industry_total_square_footage
                    :
                    0;

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_weighted_average_lease_expiration'] =
                $this->calculate_weighted_average_lease_expiration($PortfolioIndustryActiveUniqueLeaseDetailObjArr);

            $metadata['industries'][$TenantIndustryDetailObj->name]['industry_total_square_footage'] = $industry_total_square_footage;
        }

        /********************************************/

        if ($lease_as_of_date)
        {
            $metadata['lease_as_of_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_as_of_date);
        }
        if ($lease_from_date)
        {
            $metadata['lease_from_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_from_date);
        }
        if ($lease_to_date)
        {
            $metadata['lease_to_date'] = ModelDateFormatterTrait::perhaps_format_date($lease_to_date);
        }
        return $metadata;
    }
}
