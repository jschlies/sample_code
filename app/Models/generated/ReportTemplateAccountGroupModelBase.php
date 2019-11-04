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
 * Class ReportTemplateAccountGroup
 *
 * @method static ReportTemplateAccountGroup find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static ReportTemplateAccountGroup|Collection findOrFail($id, $columns = ['*']) desc
 */
class ReportTemplateAccountGroupModelBase extends Model
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
        'id'                                      => 'sometimes|integer',
        'report_template_id'                      => 'required|integer',
        'native_account_type_id'                  => 'required|integer',
        'parent_report_template_account_group_id' => 'required|integer',
        'report_template_account_group_code'      => 'sometimes|min:2|max:255',
        'report_template_account_group_name'      => 'sometimes|min:2|max:255',
        'display_name'                            => 'sometimes|nullable|min:3|max:255',
        'report_template_group_name'              => 'sometimes|max:255',
        'report_template_group_description'       => 'sometimes|nullable|min:3|max:255',
        'usage_type'                              => 'sometimes|max:255',
        'sorting'                                 => 'sometimes|max:255',
        'version_num'                             => 'sometimes|max:255',
        'deprecated_waypoint_code'                => 'sometimes|max:255',
        'boma_account_header_1_code_old'          => 'sometimes|max:255',
        'boma_account_header_1_name_old'          => 'sometimes|max:255',
        'boma_account_header_2_code_old'          => 'sometimes|max:255',
        'boma_account_header_2_name_old'          => 'sometimes|max:255',
        'boma_account_header_3_code_old'          => 'sometimes|max:255',
        'boma_account_header_3_name_old'          => 'sometimes|max:255',
        'boma_account_header_4_code_old'          => 'sometimes|max:255',
        'boma_account_header_4_name_old'          => 'sometimes|max:255',
        'boma_account_header_5_code_old'          => 'sometimes|max:255',
        'boma_account_header_5_name_old'          => 'sometimes|max:255',
        'boma_account_header_6_code_old'          => 'sometimes|max:255',
        'boma_account_header_6_name_old'          => 'sometimes|max:255',
        'sort_order'                              => 'sometimes|nullable|integer',
        'is_summary'                              => 'sometimes|nullable|boolean',
        'is_summary_tab_default_line_item'        => 'sometimes|nullable|boolean',
        'created_at'                              => 'sometimes',
        'updated_at'                              => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceLineItem",
        "advancedVarianceThreshold",
        "calculatedFieldVariable",
        "reportTemplateMapping",
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
        "reportTemplate",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "nativeAccount",
    ];

    public function construct_scaffold()
    {
        $this->setTable('report_template_account_groups');
        $this->setFillable(
            [

                'report_template_id',
                'native_account_type_id',
                'parent_report_template_account_group_id',
                'is_category',
                'is_major_category',
                'is_waypoint_specific',
                'report_template_account_group_code',
                'report_template_account_group_name',
                'display_name',
                'report_template_group_name',
                'report_template_group_description',
                'usage_type',
                'sorting',
                'version_num',
                'deprecated_waypoint_code',
                'boma_account_header_1_code_old',
                'boma_account_header_1_name_old',
                'boma_account_header_2_code_old',
                'boma_account_header_2_name_old',
                'boma_account_header_3_code_old',
                'boma_account_header_3_name_old',
                'boma_account_header_4_code_old',
                'boma_account_header_4_name_old',
                'boma_account_header_5_code_old',
                'boma_account_header_5_name_old',
                'boma_account_header_6_code_old',
                'boma_account_header_6_name_old',
                'sort_order',
                'is_summary',
                'is_summary_tab_default_line_item',

            ]
        );
        $this->setCasts(
            [

                'id'                                      => 'integer',
                'report_template_id'                      => 'integer',
                'native_account_type_id'                  => 'integer',
                'parent_report_template_account_group_id' => 'integer',
                'is_category'                             => 'boolean',
                'is_major_category'                       => 'boolean',
                'is_waypoint_specific'                    => 'boolean',
                'report_template_account_group_code'      => 'string',
                'report_template_account_group_name'      => 'string',
                'display_name'                            => 'string',
                'report_template_group_name'              => 'string',
                'report_template_group_description'       => 'string',
                'usage_type'                              => 'string',
                'sorting'                                 => 'string',
                'version_num'                             => 'string',
                'deprecated_waypoint_code'                => 'string',
                'boma_account_header_1_code_old'          => 'string',
                'boma_account_header_1_name_old'          => 'string',
                'boma_account_header_2_code_old'          => 'string',
                'boma_account_header_2_name_old'          => 'string',
                'boma_account_header_3_code_old'          => 'string',
                'boma_account_header_3_name_old'          => 'string',
                'boma_account_header_4_code_old'          => 'string',
                'boma_account_header_4_name_old'          => 'string',
                'boma_account_header_5_code_old'          => 'string',
                'boma_account_header_5_name_old'          => 'string',
                'boma_account_header_6_code_old'          => 'string',
                'boma_account_header_6_name_old'          => 'string',
                'sort_order'                              => 'integer',
                'is_summary'                              => 'boolean',
                'is_summary_tab_default_line_item'        => 'boolean',

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
    public function reportTemplate()
    {
        return $this->belongsTo(
            ReportTemplate::class,
            'report_template_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function nativeAccounts()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            NativeAccount::class,
            'report_template_mappings',
            'report_template_account_group_id',
            'native_account_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItems()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'report_template_account_group_id',
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
            'report_template_account_group_id',
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
            'report_template_account_group_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateMappings()
    {
        return $this->hasMany(
            ReportTemplateMapping::class,
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
            $rules = array_merge(ReportTemplateAccountGroup::$baseRules, ReportTemplateAccountGroup::$rules);
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
        return ReportTemplateAccountGroup::class;
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
