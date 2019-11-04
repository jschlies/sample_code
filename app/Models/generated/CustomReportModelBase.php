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
 * Class CustomReport
 *
 * @method static CustomReport find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static CustomReport|Collection findOrFail($id, $columns = ['*']) desc
 */
class CustomReportModelBase extends Model
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
        'property_id'           => 'sometimes|integer|nullable',
        'property_group_id'     => 'sometimes|integer|nullable',
        'custom_report_type_id' => 'sometimes|integer',
        'period'                => 'sometimes|string',
        'year'                  => 'sometimes|integer',
        'download_url'          => 'sometimes|string',
        'file_type'             => 'sometimes|string',
        'created_at'            => 'sometimes',
        'updated_at'            => 'sometimes',
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
        "customReportType",
        "propertyGroup",
        "property",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('custom_reports');
        $this->setFillable(
            [

                'property_id',
                'property_group_id',
                'custom_report_type_id',
                'period',
                'year',
                'download_url',
                'file_type',

            ]
        );
        $this->setCasts(
            [

                'id'                    => 'integer',
                'property_id'           => 'integer',
                'property_group_id'     => 'integer',
                'custom_report_type_id' => 'integer',
                'period'                => 'string',
                'year'                  => 'integer',
                'download_url'          => 'string',
                'file_type'             => 'string',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function customReportType()
    {
        return $this->belongsTo(
            CustomReportType::class,
            'custom_report_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function propertyGroup()
    {
        return $this->belongsTo(
            PropertyGroup::class,
            'property_group_id',
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
            $rules = array_merge(CustomReport::$baseRules, CustomReport::$rules);
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
        return CustomReport::class;
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
