<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Model;

/**
 * Class LeaseSchedule
 *
 * @method static LeaseSchedule find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static LeaseSchedule|Collection findOrFail($id, $columns = ['*']) desc
 */
class LeaseScheduleModelBase extends Model
{
    /**
     * Generated
     */

    /**
     * PropertyModelBase constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                     => 'sometimes|integer',
        'lease_id'               => 'sometimes|nullable|integer',
        'least_id_staging'       => 'sometimes|max:255',
        'suite_id'               => 'sometimes|nullable|integer',
        'property_id'            => 'sometimes|nullable|integer',
        'rent_roll_id'           => 'required|integer',
        'property_name'          => 'sometimes|nullable|max:255',
        'property_code'          => 'sometimes|nullable|max:255',
        'as_of_date'             => 'sometimes',
        'original_property_code' => 'sometimes|max:255',
        'rent_unit_id'           => 'sometimes|nullable|max:255',
        'suite_id_code'          => 'sometimes|nullable|max:255',
        'lease_id_code'          => 'sometimes|nullable|max:255',
        'lease_name'             => 'sometimes|nullable|max:255',
        'lease_type'             => 'sometimes|max:255',
        'square_footage'         => 'sometimes|numeric|nullable',
        'lease_start_date'       => 'sometimes',
        'lease_expiration_date'  => 'sometimes',
        'lease_term'             => 'sometimes|nullable|numeric',
        'tenancy_year'           => 'sometimes|nullable|max:255',
        'monthly_rent'           => 'sometimes|nullable|numeric',
        'monthly_rent_area'      => 'sometimes|nullable|numeric',
        'annual_rent'            => 'sometimes|nullable|numeric',
        'annual_rent_area'       => 'sometimes|nullable|numeric',
        'annual_rec_area'        => 'sometimes|nullable|numeric',
        'annual_misc_area'       => 'sometimes|nullable|numeric',
        'security_deposit'       => 'sometimes|nullable|numeric',
        'letter_cr_amt'          => 'sometimes|nullable|numeric',
        'updated_datetime'       => 'sometimes|nullable',
        'raw_upload'             => 'sometimes',
        'created_at'             => 'sometimes',
        'updated_at'             => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [

    ];

    /**
     * @var array
     */
    public static $hasOne_arr = [

    ];

    /**
     * @var array
     */
    public static $belongsTo_arr = [
        "lease",
        "property",
        "suite",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('lease_schedules');
        $this->setFillable(
            [

                'lease_id',
                'least_id_staging',
                'suite_id',
                'property_id',
                'rent_roll_id',
                'property_name',
                'property_code',
                'as_of_date',
                'original_property_code',
                'rent_unit_id',
                'suite_id_code',
                'lease_id_code',
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

            ]
        );
        $this->setCasts(
            [

                'id'                     => 'integer',
                'lease_id'               => 'integer',
                'least_id_staging'       => 'string',
                'suite_id'               => 'integer',
                'property_id'            => 'integer',
                'rent_roll_id'           => 'integer',
                'property_name'          => 'string',
                'property_code'          => 'string',
                'as_of_date'             => 'datetime',
                'original_property_code' => 'string',
                'rent_unit_id'           => 'string',
                'suite_id_code'          => 'string',
                'lease_id_code'          => 'string',
                'lease_name'             => 'string',
                'lease_type'             => 'string',
                'square_footage'         => 'float',
                'lease_start_date'       => 'datetime',
                'lease_expiration_date'  => 'datetime',
                'lease_term'             => 'float',
                'tenancy_year'           => 'float',
                'monthly_rent'           => 'float',
                'monthly_rent_area'      => 'float',
                'annual_rent'            => 'float',
                'annual_rent_area'       => 'float',
                'annual_rec_area'        => 'float',
                'annual_misc_area'       => 'float',
                'security_deposit'       => 'float',
                'letter_cr_amt'          => 'float',
                'updated_datetime'       => 'datetime',
                'raw_upload'             => 'string',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function lease()
    {
        return $this->belongsTo(
            Lease::class,
            'lease_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function property()
    {
        return $this->belongsTo(
            Property::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function suite()
    {
        return $this->belongsTo(
            Suite::class,
            'suite_id',
            'id'
        );
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(LeaseSchedule::$baseRules, LeaseSchedule::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * This is needed to get Audits to work
     *
     * @return string
     */
    public function getMorphClass()
    {
        return LeaseSchedule::class;
    }

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * End Of Generated
     */
}
