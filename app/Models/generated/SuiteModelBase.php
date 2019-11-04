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
 * Class Suite
 *
 * @method static Suite find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Suite|Collection findOrFail($id, $columns = ['*']) desc
 */
class SuiteModelBase extends Model
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
        'property_id'            => 'required|integer',
        'suite_id_code'          => 'sometimes|max:255',
        'suite_id_number'        => 'sometimes|max:255',
        'name'                   => 'sometimes|max:255',
        'description'            => 'sometimes|max:255',
        'square_footage'         => 'sometimes|nullable|numeric',
        'original_property_code' => 'sometimes|max:255',
        'created_at'             => 'sometimes',
        'updated_at'             => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "leaseSchedule",
        "suiteLease",
        "suiteTenant",
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
        "lease",
        "tenant",
    ];

    public function construct_scaffold()
    {
        $this->setTable('suites');
        $this->setFillable(
            [

                'property_id',
                'suite_id_code',
                'suite_id_number',
                'name',
                'description',
                'square_footage',
                'original_property_code',

            ]
        );
        $this->setCasts(
            [

                'id'                     => 'integer',
                'property_id'            => 'integer',
                'suite_id_code'          => 'string',
                'suite_id_number'        => 'string',
                'name'                   => 'string',
                'description'            => 'string',
                'square_footage'         => 'float',
                'original_property_code' => 'string',

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
    public function leases()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Lease::class,
            'suite_leases',
            'suite_id',
            'lease_id'
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
            'suite_tenants',
            'suite_id',
            'tenant_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseSchedules()
    {
        return $this->hasMany(
            LeaseSchedule::class,
            'suite_id',
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
            'suite_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function suiteTenants()
    {
        return $this->hasMany(
            SuiteTenant::class,
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
            $rules = array_merge(Suite::$baseRules, Suite::$rules);
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
        return Suite::class;
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
