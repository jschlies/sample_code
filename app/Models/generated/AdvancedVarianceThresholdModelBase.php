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
 * Class AdvancedVarianceThreshold
 *
 * @method static AdvancedVarianceThreshold find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static AdvancedVarianceThreshold|Collection findOrFail($id, $columns = ['*']) desc
 */
class AdvancedVarianceThresholdModelBase extends Model
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
        'id'                                                               => 'sometimes|integer',
        'client_id'                                                        => 'required|integer',
        'property_id'                                                      => 'sometimes|nullable|integer',
        'native_account_id'                                                => 'sometimes|nullable|integer',
        'native_account_type_id'                                           => 'sometimes|nullable|integer',
        'report_template_account_group_id'                                 => 'sometimes|nullable|integer',
        'calculated_field_id'                                              => 'sometimes|nullable|integer',
        'native_account_overage_threshold_amount'                          => 'sometimes|numeric',
        'native_account_overage_threshold_amount_too_good'                 => 'sometimes|numeric|nullable',
        'native_account_overage_threshold_percent'                         => 'sometimes|numeric',
        'native_account_overage_threshold_percent_too_good'                => 'sometimes|numeric|nullable',
        'native_account_overage_threshold_operator'                        => 'sometimes|max:255',
        'report_template_account_group_overage_threshold_amount'           => 'sometimes|numeric',
        'report_template_account_group_overage_threshold_amount_too_good'  => 'sometimes|numeric|nullable',
        'report_template_account_group_overage_threshold_percent'          => 'sometimes|numeric',
        'report_template_account_group_overage_threshold_percent_too_good' => 'sometimes|numeric|nullable',
        'report_template_account_group_overage_threshold_operator'         => 'sometimes|max:255',
        'calculated_field_overage_threshold_amount'                        => 'sometimes|numeric',
        'calculated_field_overage_threshold_amount_too_good'               => 'sometimes|numeric|nullable',
        'calculated_field_overage_threshold_percent'                       => 'sometimes|numeric',
        'calculated_field_overage_threshold_percent_too_good'              => 'sometimes|numeric|nullable',
        'calculated_field_overage_threshold_operator'                      => 'sometimes|max:255',
        'created_at'                                                       => 'nullable|sometimes',
        'updated_at'                                                       => 'nullable|sometimes',
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
        "calculatedField",
        "client",
        "nativeAccount",
        "nativeAccountType",
        "property",
        "reportTemplateAccountGroup",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('advanced_variance_thresholds');
        $this->setFillable(
            [

                'client_id',
                'property_id',
                'native_account_id',
                'native_account_type_id',
                'report_template_account_group_id',
                'calculated_field_id',
                'native_account_overage_threshold_amount',
                'native_account_overage_threshold_amount_too_good',
                'native_account_overage_threshold_percent',
                'native_account_overage_threshold_percent_too_good',
                'native_account_overage_threshold_operator',
                'report_template_account_group_overage_threshold_amount',
                'report_template_account_group_overage_threshold_amount_too_good',
                'report_template_account_group_overage_threshold_percent',
                'report_template_account_group_overage_threshold_percent_too_good',
                'report_template_account_group_overage_threshold_operator',
                'calculated_field_overage_threshold_amount',
                'calculated_field_overage_threshold_amount_too_good',
                'calculated_field_overage_threshold_percent',
                'calculated_field_overage_threshold_percent_too_good',
                'calculated_field_overage_threshold_operator',

            ]
        );
        $this->setCasts(
            [

                'id'                                                               => 'integer',
                'client_id'                                                        => 'integer',
                'property_id'                                                      => 'integer',
                'native_account_id'                                                => 'integer',
                'native_account_type_id'                                           => 'integer',
                'report_template_account_group_id'                                 => 'integer',
                'calculated_field_id'                                              => 'integer',
                'native_account_overage_threshold_amount'                          => 'float',
                'native_account_overage_threshold_amount_too_good'                 => 'float',
                'native_account_overage_threshold_percent'                         => 'float',
                'native_account_overage_threshold_percent_too_good'                => 'float',
                'native_account_overage_threshold_operator'                        => 'string',
                'report_template_account_group_overage_threshold_amount'           => 'float',
                'report_template_account_group_overage_threshold_amount_too_good'  => 'float',
                'report_template_account_group_overage_threshold_percent'          => 'float',
                'report_template_account_group_overage_threshold_percent_too_good' => 'float',
                'report_template_account_group_overage_threshold_operator'         => 'string',
                'calculated_field_overage_threshold_amount'                        => 'float',
                'calculated_field_overage_threshold_amount_too_good'               => 'float',
                'calculated_field_overage_threshold_percent'                       => 'float',
                'calculated_field_overage_threshold_percent_too_good'              => 'float',
                'calculated_field_overage_threshold_operator'                      => 'string',

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
    public function property()
    {
        return $this->belongsTo(
            Property::class,
            'property_id',
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
            $rules = array_merge(AdvancedVarianceThreshold::$baseRules, AdvancedVarianceThreshold::$rules);
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
        return AdvancedVarianceThreshold::class;
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
