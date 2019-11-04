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
 * Class EcmProject
 *
 * @method static EcmProject find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static EcmProject|Collection findOrFail($id, $columns = ['*']) desc
 */
class EcmProjectModelBase extends Model
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
        'id'                              => 'sometimes|integer',
        'property_id'                     => 'sometimes|integer',
        'name'                            => 'sometimes|max:255',
        'description'                     => 'sometimes|nullable|min:3|max:255',
        'project_category'                => 'sometimes|max:255',
        'project_status'                  => 'sometimes|max:255',
        'costs'                           => 'sometimes|numeric',
        'estimated_incentive'             => 'sometimes|numeric',
        'estimated_annual_savings'        => 'sometimes|numeric',
        'estimated_annual_energy_savings' => 'sometimes|numeric',
        'energy_units'                    => 'sometimes',
        'project_summary'                 => 'sometimes',
        'estimated_start_date'            => 'sometimes',
        'estimated_completion_date'       => 'sometimes',
        'created_at'                      => 'sometimes',
        'updated_at'                      => 'sometimes',
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
        "property",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('ecm_projects');
        $this->setFillable(
            [

                'property_id',
                'name',
                'description',
                'project_category',
                'project_status',
                'costs',
                'estimated_incentive',
                'estimated_annual_savings',
                'estimated_annual_energy_savings',
                'energy_units',
                'project_summary',
                'estimated_start_date',
                'estimated_completion_date',

            ]
        );
        $this->setCasts(
            [

                'id'                              => 'integer',
                'property_id'                     => 'integer',
                'name'                            => 'string',
                'description'                     => 'string',
                'project_category'                => 'string',
                'project_status'                  => 'string',
                'costs'                           => 'float',
                'estimated_incentive'             => 'float',
                'estimated_annual_savings'        => 'float',
                'estimated_annual_energy_savings' => 'float',
                'energy_units'                    => 'string',
                'project_summary'                 => 'string',
                'estimated_start_date'            => 'string',
                'estimated_completion_date'       => 'string',

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
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(EcmProject::$baseRules, EcmProject::$rules);
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
        return EcmProject::class;
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
