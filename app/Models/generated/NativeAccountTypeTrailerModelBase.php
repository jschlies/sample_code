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
 * Class NativeAccountTypeTrailer
 *
 * @method static NativeAccountTypeTrailer find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static NativeAccountTypeTrailer|Collection findOrFail($id, $columns = ['*']) desc
 */
class NativeAccountTypeTrailerModelBase extends Model
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
        'id'                            => 'sometimes|integer',
        'native_coa_id'                 => 'sometimes|integer',
        'native_account_type_id'        => 'sometimes|integer',
        'property_id'                   => 'sometimes|nullable|integer',
        'actual_coefficient'            => 'sometimes|numeric',
        'budgeted_coefficient'          => 'sometimes|numeric',
        'advanced_variance_coefficient' => 'sometimes|numeric',
        'created_at'                    => 'sometimes',
        'updated_at'                    => 'sometimes',
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
        "nativeAccountType",
        "nativeCoa",
        "property",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('native_account_type_trailers');
        $this->setFillable(
            [

                'native_coa_id',
                'native_account_type_id',
                'property_id',
                'actual_coefficient',
                'budgeted_coefficient',
                'advanced_variance_coefficient',

            ]
        );
        $this->setCasts(
            [

                'id'                            => 'integer',
                'native_coa_id'                 => 'integer',
                'native_account_type_id'        => 'integer',
                'property_id'                   => 'integer',
                'actual_coefficient'            => 'float',
                'budgeted_coefficient'          => 'float',
                'advanced_variance_coefficient' => 'float',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountType()
    {
        return $this->belongsTo(
            NativeAccountType::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeCoa()
    {
        return $this->belongsTo(
            NativeCoa::class,
            'native_coa_id',
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
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(NativeAccountTypeTrailer::$baseRules, NativeAccountTypeTrailer::$rules);
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
        return NativeAccountTypeTrailer::class;
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
