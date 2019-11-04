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
 * Class ReportTemplateMapping
 *
 * @method static ReportTemplateMapping find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static ReportTemplateMapping|Collection findOrFail($id, $columns = ['*']) desc
 */
class ReportTemplateMappingModelBase extends Model
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
        'native_account_id'                => 'required|integer',
        'report_template_account_group_id' => 'required|integer',
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
        "nativeAccount",
        "reportTemplateAccountGroup",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('report_template_mappings');
        $this->setFillable(
            [

                'native_account_id',
                'report_template_account_group_id',
                'sort_order',
                'is_summary',
                'is_summary_tab_default_line_item',

            ]
        );
        $this->setCasts(
            [

                'id'                               => 'integer',
                'native_account_id'                => 'integer',
                'report_template_account_group_id' => 'integer',
                'sort_order'                       => 'integer',
                'is_summary'                       => 'boolean',
                'is_summary_tab_default_line_item' => 'boolean',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccount()
    {
        return $this->belongsTo(
            NativeAccount::class,
            'native_account_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function reportTemplateAccountGroup()
    {
        return $this->belongsTo(
            ReportTemplateAccountGroup::class,
            'report_template_account_group_id',
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
            $rules = array_merge(ReportTemplateMapping::$baseRules, ReportTemplateMapping::$rules);
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
        return ReportTemplateMapping::class;
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
