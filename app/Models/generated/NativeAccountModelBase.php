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
 * Class NativeAccount
 *
 * @method static NativeAccount find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static NativeAccount|Collection findOrFail($id, $columns = ['*']) desc
 */
class NativeAccountModelBase extends Model
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
        'id'                       => 'sometimes|integer',
        'native_coa_id'            => 'required|integer',
        'native_account_name'      => 'sometimes|max:255',
        'native_account_code'      => 'sometimes|max:255',
        'native_account_type_id'   => 'sometimes|integer',
        'parent_native_account_id' => 'sometimes|nullable|integer',
        'created_at'               => 'sometimes',
        'updated_at'               => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceLineItem",
        "advancedVarianceThreshold",
        "calculatedFieldVariable",
        "nativeAccountAmount",
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
        "nativeCoa",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "reportTemplateAccountGroup",
    ];

    public function construct_scaffold()
    {
        $this->setTable('native_accounts');
        $this->setFillable(
            [

                'native_coa_id',
                'native_account_name',
                'native_account_code',
                'native_account_type_id',
                'parent_native_account_id',
                'is_category',
                'is_recoverable',

            ]
        );
        $this->setCasts(
            [

                'id'                       => 'integer',
                'native_coa_id'            => 'integer',
                'native_account_name'      => 'string',
                'native_account_code'      => 'string',
                'native_account_type_id'   => 'integer',
                'parent_native_account_id' => 'integer',
                'is_category'              => 'boolean',
                'is_recoverable'           => 'boolean',

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
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function reportTemplateAccountGroups()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            ReportTemplateAccountGroup::class,
            'report_template_mappings',
            'native_account_id',
            'report_template_account_group_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItems()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'native_account_id',
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
            'native_account_id',
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
            'native_account_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountAmounts()
    {
        return $this->hasMany(
            NativeAccountAmount::class,
            'native_account_id',
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
            'native_account_id',
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
            $rules = array_merge(NativeAccount::$baseRules, NativeAccount::$rules);
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
        return NativeAccount::class;
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
