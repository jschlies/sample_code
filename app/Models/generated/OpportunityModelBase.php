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
 * Class Opportunity
 *
 * @method static Opportunity find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Opportunity|Collection findOrFail($id, $columns = ['*']) desc
 */
class OpportunityModelBase extends Model
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
        'id'                   => 'sometimes|integer',
        'name'                 => 'sometimes|max:255',
        'property_id'          => 'sometimes|integer',
        'client_category_id'   => 'sometimes|integer',
        'assigned_to_user_id'  => 'sometimes|integer',
        'created_by_user_id'   => 'sometimes|integer',
        'description'          => 'sometimes|nullable|min:3|max:1024',
        'opportunity_status'   => 'sometimes|max:255',
        'opportunity_priority' => 'sometimes|max:255',
        'expense_amount'       => 'sometimes|numeric',
        'estimated_incentive'  => 'sometimes|numeric',
        'created_at'           => 'sometimes',
        'updated_at'           => 'sometimes',
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
        "user",
        "clientCategory",
        "user",
        "property",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('opportunities');
        $this->setFillable(
            [

                'name',
                'property_id',
                'client_category_id',
                'assigned_to_user_id',
                'created_by_user_id',
                'description',
                'opportunity_status',
                'opportunity_priority',
                'expense_amount',
                'estimated_incentive',

            ]
        );
        $this->setCasts(
            [

                'id'                   => 'integer',
                'name'                 => 'string',
                'property_id'          => 'integer',
                'client_category_id'   => 'integer',
                'assigned_to_user_id'  => 'integer',
                'created_by_user_id'   => 'integer',
                'description'          => 'string',
                'opportunity_status'   => 'string',
                'opportunity_priority' => 'string',
                'expense_amount'       => 'float',
                'estimated_incentive'  => 'float',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function clientCategory()
    {
        return $this->belongsTo(
            ClientCategory::class,
            'client_category_id',
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
            $rules = array_merge(Opportunity::$baseRules, Opportunity::$rules);
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
        return Opportunity::class;
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
