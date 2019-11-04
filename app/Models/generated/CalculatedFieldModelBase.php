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
 * Class CalculatedField
 *
 * @method static CalculatedField find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static CalculatedField|Collection findOrFail($id, $columns = ['*']) desc
 */
class CalculatedFieldModelBase extends Model
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
        'id'                               => 'sometimes|integer',
        'report_template_id'               => 'sometimes|integer',
        'name'                             => 'sometimes|max:255',
        'description'                      => 'sometimes|nullable|min:3|max:255',
        'sort_order'                       => 'sometimes|nullable|integer',
        'is_summary'                       => 'sometimes|nullable|boolean',
        'is_summary_tab_default_line_item' => 'sometimes|nullable|boolean',
        'created_at'                       => 'sometimes',
        'updated_at'                       => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceLineItem",
        "advancedVarianceThreshold",
        "calculatedFieldEquation",
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
        "reportTemplate",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('calculated_fields');
        $this->setFillable(
            [

                'report_template_id',
                'name',
                'description',
                'sort_order',
                'is_summary',
                'is_summary_tab_default_line_item',

            ]
        );
        $this->setCasts(
            [

                'id'                               => 'integer',
                'report_template_id'               => 'integer',
                'name'                             => 'string',
                'description'                      => 'string',
                'sort_order'                       => 'integer',
                'is_summary'                       => 'boolean',
                'is_summary_tab_default_line_item' => 'boolean',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function reportTemplate()
    {
        return $this->belongsTo(
            ReportTemplate::class,
            'report_template_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItems()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'calculated_field_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceThresholds()
    {
        return $this->hasMany(
            AdvancedVarianceThreshold::class,
            'calculated_field_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldEquations()
    {
        return $this->hasMany(
            CalculatedFieldEquation::class,
            'calculated_field_id',
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
            $rules = array_merge(CalculatedField::$baseRules, CalculatedField::$rules);
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
        return CalculatedField::class;
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
