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
 * Class CalculatedFieldEquation
 *
 * @method static CalculatedFieldEquation find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static CalculatedFieldEquation|Collection findOrFail($id, $columns = ['*']) desc
 */
class CalculatedFieldEquationModelBase extends Model
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
        'id'                      => 'sometimes|integer',
        'calculated_field_id'     => 'sometimes|integer',
        'equation_string'         => 'sometimes|max:255',
        'equation_string_parsed'  => 'sometimes|max:1023',
        'display_equation_string' => 'sometimes|max:1023',
        'name'                    => 'sometimes|nullable|max:255',
        'description'             => 'sometimes|nullable|min:3|max:255',
        'created_at'              => 'sometimes',
        'updated_at'              => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "calculatedFieldEquationProperty",
        "calculatedFieldVariable",
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
        "calculatedField",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "property",
    ];

    public function construct_scaffold()
    {
        $this->setTable('calculated_field_equations');
        $this->setFillable(
            [

                'calculated_field_id',
                'equation_string',
                'equation_string_parsed',
                'display_equation_string',
                'name',
                'description',

            ]
        );
        $this->setCasts(
            [

                'id'                      => 'integer',
                'calculated_field_id'     => 'integer',
                'equation_string'         => 'string',
                'equation_string_parsed'  => 'string',
                'display_equation_string' => 'string',
                'name'                    => 'string',
                'description'             => 'string',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function calculatedField()
    {
        return $this->belongsTo(
            CalculatedField::class,
            'calculated_field_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function properties()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Property::class,
            'calculated_field_equation_properties',
            'calculated_field_equation_id',
            'property_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldEquationProperties()
    {
        return $this->hasMany(
            CalculatedFieldEquationProperty::class,
            'calculated_field_equation_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldVariables()
    {
        return $this->hasMany(
            CalculatedFieldVariable::class,
            'calculated_field_equation_id',
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
            $rules = array_merge(CalculatedFieldEquation::$baseRules, CalculatedFieldEquation::$rules);
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
        return CalculatedFieldEquation::class;
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
