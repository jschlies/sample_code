<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class LeaseSchedule
 * @package App\Waypoint\Models
 */
class LeaseSchedule extends LeaseScheduleModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'as_of_date'            => 'date',
        'lease_start_date'      => 'date|nullable',
        'lease_expiration_date' => 'date|nullable',
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
        return [
            "id"                     => $this->id,
            "lease_id"               => $this->lease_id,
            "least_id_staging"       => $this->least_id_staging,
            "property_id"            => $this->property_id,
            "rent_roll_id"           => $this->rent_roll_id,
            "property_name"          => $this->property_name,
            "property_code"          => $this->property_code,
            "as_of_date"             => $this->perhaps_format_date($this->as_of_date),
            "original_property_code" => $this->original_property_code,
            "rent_unit_id"           => $this->rent_unit_id,
            "suite_id_code"          => $this->suite_id_code,
            "lease_id_code"          => $this->lease_id_code,
            "lease_name"             => $this->lease_name,
            "lease_type"             => $this->lease_type,
            "square_footage"         => $this->square_footage,
            "lease_start_date"       => $this->perhaps_format_date($this->lease_start_date),
            "lease_expiration_date"  => $this->perhaps_format_date($this->lease_expiration_date),
            "lease_term"             => $this->lease_term,
            "tenancy_year"           => $this->tenancy_year,
            "monthly_rent"           => $this->monthly_rent,
            "monthly_rent_area"      => $this->monthly_rent_area,
            "annual_rent"            => $this->annual_rent,
            "annual_rent_area"       => $this->annual_rent_area,
            "annual_rec_area"        => $this->annual_rec_area,
            "annual_misc_area"       => $this->annual_misc_area,
            "security_deposit"       => $this->security_deposit,
            "letter_cr_amt"          => $this->letter_cr_amt,
            "updated_datetime"       => $this->perhaps_format_date($this->updated_datetime),
            "raw_upload"             => $this->raw_upload,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
