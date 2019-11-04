<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class Lease
 * @package App\Waypoint\Models
 */
class LeaseDetail extends Lease
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * NativeCoa constructor.
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
         * remember that there's a connector table between tenants and leases
         */
        $TenantDetailObj = $this->tenantDetails->count() ? $this->tenantDetails->first() : null;

        if ( ! $TenantDetailObj)
        {
            $TenantIndustryDetailObj = null;
        }
        elseif ( ! $TenantDetailObj->tenant_industry_id)
        {
            $TenantIndustryDetailObj = null;
        }
        else
        {
            $TenantIndustryDetailObj = $TenantDetailObj->tenantIndustryDetail;
        }

        return [

            "id"                    => $this->id,
            "property_id"           => $this->property_id,
            "lease_id_code"         => $this->lease_id_code,
            "least_id_staging"      => $this->least_id_staging,
            "lease_name"            => $this->lease_name,
            "lease_type"            => $this->lease_type,
            "square_footage"        => $this->square_footage,
            "description"           => $this->description,
            "lease_start_date"      => $this->perhaps_format_date($this->lease_start_date),
            "lease_expiration_date" => $this->perhaps_format_date($this->lease_expiration_date),

            "lease_term"        => empty($this->lease_term) ? null : $this->lease_term,
            "tenancy_year"      => $this->tenancy_year,
            "monthly_rent"      => $this->monthly_rent,
            "monthly_rent_area" => $this->monthly_rent_area,
            "annual_rent"       => $this->monthly_rent * 12,
            "annual_rent_area"  => $this->annual_rent_area,
            "annual_misc_area"  => $this->annual_misc_area,
            "security_deposit"  => $this->security_deposit,
            "letter_cr_amt"     => $this->letter_cr_amt,

            "monthly_rent_per_square_foot" => ($this->square_footage && $this->monthly_rent) ? ($this->monthly_rent / $this->square_footage) : null,
            "annual_rent_per_square_foot"  => ($this->square_footage && $this->monthly_rent) ? ($this->monthly_rent * 12 / $this->square_footage) : null,

            "suite_id_arr" => $this->suiteDetails->count() ? $this->suiteDetails->pluck('id')->toArray() : [],

            "tenant_id"            => $TenantDetailObj ? $TenantDetailObj->id : null,
            "tenantIndustryDetail" => $TenantIndustryDetailObj ? $TenantIndustryDetailObj->toArray() : null,

            "total_suite_square_footage" => $this->suiteDetails->sum('square_footage'),
            "lease_square_footage"       => $this->square_footage,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
