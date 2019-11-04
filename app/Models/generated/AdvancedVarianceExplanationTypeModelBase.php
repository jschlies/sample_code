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
 * Class AdvancedVarianceExplanationType
 *
 * @method static AdvancedVarianceExplanationType find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static AdvancedVarianceExplanationType|Collection findOrFail($id, $columns = ['*']) desc
 */
class AdvancedVarianceExplanationTypeModelBase extends Model
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
        'id'          => 'sometimes|integer',
        'client_id'   => 'required|integer',
        'name'        => 'sometimes|nullable|max:255',
        'description' => 'sometimes|nullable|max:255',
        'color'       => 'sometimes|nullable|regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/',
        'sort_order'  => 'sometimes|nullable|integer',
        'is_summary'  => 'sometimes|nullable|boolean',
        'created_at'  => 'nullable|sometimes',
        'updated_at'  => 'nullable|sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceLineItem",
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
        "client",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('advanced_variance_explanation_types');
        $this->setFillable(
            [

                'client_id',
                'name',
                'description',
                'color',
                'sort_order',
                'is_summary',

            ]
        );
        $this->setCasts(
            [

                'id'          => 'integer',
                'client_id'   => 'integer',
                'name'        => 'string',
                'description' => 'string',
                'color'       => 'string',
                'sort_order'  => 'integer',
                'is_summary'  => 'boolean',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function client()
    {
        return $this->belongsTo(
            Client::class,
            'client_id',
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
            'advanced_variance_explanation_type_id',
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
            $rules = array_merge(AdvancedVarianceExplanationType::$baseRules, AdvancedVarianceExplanationType::$rules);
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
        return AdvancedVarianceExplanationType::class;
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
