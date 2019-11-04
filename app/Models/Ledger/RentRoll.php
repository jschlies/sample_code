<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

class RentRoll extends Ledger
{
    public $fillable = [
        'rent_roll_id',
        'property_name',
        'property_code',
        'as_of_date',
        'original_property_code',
        'tenant_industry',
        'rent_unit_id',
        'suite_id_code',
        'suite_name',
        'lease_id_code',
        'least_id_staging',
        'lease_name',
        'lease_type',
        'square_footage',
        'lease_start_date',
        'lease_expiration_date',
        'lease_term',
        'tenancy_year',
        'monthly_rent',
        'monthly_rent_area',
        'annual_rent',
        'annual_rent_area',
        'annual_rec_area',
        'annual_misc_area',
        'security_deposit',
        'letter_cr_amt',
        'updated_datetime',
        'raw_upload',
    ];

    protected $casts = [
        'rent_roll_id'           => 'int',
        'property_name'          => 'string',
        'property_code'          => 'string',
        'as_of_date'             => 'datetime',
        'original_property_code' => 'string',

        'rent_unit_id'          => 'int',
        'suite_id_code'         => 'string',
        'suite_name'            => 'string',
        'lease_id_code'         => 'string',
        'least_id_staging'      => 'string',
        'lease_name'            => 'string',
        'lease_type'            => 'string',
        'square_footage'        => 'float',
        'lease_start_date'      => 'datetime',
        'lease_expiration_date' => 'datetime',
        'lease_term'            => 'string',
        'tenancy_year'          => 'float',
        'monthly_rent'          => 'float',
        'monthly_rent_area'     => 'float',
        'annual_rent'           => 'float',
        'annual_rent_area'      => 'float',
        'security_deposit'      => 'float',
        'annual_rec_area'       => 'float',
        'annual_misc_area'      => 'float',
        'letter_cr_amt'         => 'float',
        'updated_datetime'      => 'datetime',
        'raw_upload'            => 'string',
    ];

    public static $rules = [

        'rent_roll_id'          => 'required|integer',
        'property_name'         => 'sometimes|string|min:1',
        'property_code'         => 'required|string|min:1',
        'as_of_date'            => 'sometimes|string|nullable|min:1',
        'lease_start_date'      => 'sometimes|string|nullable|min:1',
        'lease_expiration_date' => 'sometimes|string|nullable|min:1',
        'updated_datetime'      => 'sometimes|string|nullable|min:10',

        'rent_unit_id'     => 'required|integer',
        'suite_id_code'    => 'required|string|min:1',
        'suite_name'       => 'required|string|min:1',
        'lease_id_code'    => 'sometimes|string|min:1',
        'least_id_staging' => 'sometimes|string|nullable|min:1',
        'lease_name'       => 'sometimes|string|nullable|min:1',
    ];

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array $models
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}
