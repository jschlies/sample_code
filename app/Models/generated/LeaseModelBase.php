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
 * Class Lease
 *
 * @method static Lease find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Lease|Collection findOrFail($id, $columns = ['*']) desc
 */
class LeaseModelBase extends Model
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
        'id'                    => 'sometimes|integer',
        'property_id'           => 'required|integer',
        'lease_id_code'         => 'sometimes|max:255',
        'least_id_staging'      => 'sometimes|max:255',
        'lease_name'            => 'sometimes|max:255',
        'lease_type'            => 'sometimes|max:255',
        'square_footage'        => 'sometimes|nullable|numeric',
        'description'           => 'sometimes|nullable|max:255',
        'lease_start_date'      => 'sometimes',
        'lease_expiration_date' => 'sometimes|nullable|',
        'lease_term'            => 'sometimes|nullable|numeric',
        'tenancy_year'          => 'sometimes|nullable|max:255',
        'monthly_rent'          => 'sometimes|nullable|numeric',
        'monthly_rent_area'     => 'sometimes|nullable',
        'annual_rent'           => 'sometimes|nullable|numeric',
        'annual_rent_area'      => 'sometimes|nullable',
        'annual_rec_area'       => 'sometimes|nullable',
        'annual_misc_area'      => 'sometimes|nullable',
        'security_deposit'      => 'sometimes|nullable',
        'letter_cr_amt'         => 'sometimes|nullable',
        'created_at'            => 'sometimes',
        'updated_at'            => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "leaseSchedule",
        "leaseTenant",
        "suiteLease",
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
        "property",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "tenant",
        "suite",
    ];

    public function construct_scaffold()
    {
        $this->setTable('leases');
        $this->setFillable(
            [

                'property_id',
                'lease_id_code',
                'least_id_staging',
                'lease_name',
                'lease_type',
                'square_footage',
                'description',
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

            ]
        );
        $this->setCasts(
            [

                'id'                    => 'integer',
                'property_id'           => 'integer',
                'lease_id_code'         => 'string',
                'least_id_staging'      => 'string',
                'lease_name'            => 'string',
                'lease_type'            => 'string',
                'square_footage'        => 'integer',
                'description'           => 'string',
                'lease_start_date'      => 'datetime',
                'lease_expiration_date' => 'datetime',
                'lease_term'            => 'integer',
                'tenancy_year'          => 'integer',
                'monthly_rent'          => 'float',
                'monthly_rent_area'     => 'float',
                'annual_rent'           => 'float',
                'annual_rent_area'      => 'float',
                'annual_rec_area'       => 'float',
                'annual_misc_area'      => 'float',
                'security_deposit'      => 'float',
                'letter_cr_amt'         => 'float',

            ]
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
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function tenants()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Tenant::class,
            'lease_tenants',
            'lease_id',
            'tenant_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function suites()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Suite::class,
            'suite_leases',
            'lease_id',
            'suite_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseSchedules()
    {
        return $this->hasMany(
            LeaseSchedule::class,
            'lease_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseTenants()
    {
        return $this->hasMany(
            LeaseTenant::class,
            'lease_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function suiteLeases()
    {
        return $this->hasMany(
            SuiteLease::class,
            'lease_id',
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
            $rules = array_merge(Lease::$baseRules, Lease::$rules);
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
        return Lease::class;
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
