<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class Suite
 * @package App\Waypoint\Models
 */
class SuiteDetail extends Suite
{

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                     => $this->id,
            "property_id"            => $this->property_id,
            "suite_id_code"          => $this->suite_id_code,
            "suite_id_number"        => $this->suite_id_number,
            "name"                   => $this->name,
            "description"            => $this->description,
            "square_footage"         => $this->square_footage,
            "original_property_code" => $this->original_property_code,

            "leaseDetails"  => $this->leaseDetails->filter(
                function (LeaseDetail $LeaseDetailObj)
                {
                    return
                        Lease::check_model_date_range($LeaseDetailObj);
                }
            )->toArray(),
            "tenant_id_arr" => $this->tenantDetails->pluck('id')->toArray(),

            "tenantIndustryDetailArr" => $this->tenantDetails
                ->map(
                    function (TenantDetail $TenantDetailObj)
                    {
                        return $TenantDetailObj->tenant_industry_id ? $TenantDetailObj->tenantIndustryDetail : null;
                    }
                )
                /** Deal with nulls */
                ->filter(
                    function (TenantIndustryDetail $TenantIndustryDetailObj)
                    {
                        return $TenantIndustryDetailObj;
                    }
                ),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
