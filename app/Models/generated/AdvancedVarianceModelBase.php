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
 * Class AdvancedVariance
 *
 * @method static AdvancedVariance find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static AdvancedVariance|Collection findOrFail($id, $columns = ['*']) desc
 */
class AdvancedVarianceModelBase extends Model
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
        'id'                                => 'sometimes|integer',
        'advanced_variance_status'          => 'required|max:255',
        'threshold_mode'                    => 'required|max:255',
        'advanced_variance_start_date'      => 'required',
        'target_locked_date'                => 'required',
        'period_type'                       => 'required|max:255',
        'trigger_mode'                      => 'required|max:255',
        'property_id'                       => 'required|integer',
        'report_template_id'                => 'required|integer',
        'as_of_month'                       => 'required|integer',
        'as_of_year'                        => 'required|integer',
        'locked_date'                       => 'nullable|sometimes',
        'locker_user_id'                    => 'nullable|sometimes|integer',
        'num_flagged_via_policy'            => 'sometimes',
        'num_flagged_manually'              => 'sometimes',
        'num_flagged'                       => 'sometimes',
        'num_explained'                     => 'sometimes',
        'num_line_items'                    => 'sometimes',
        'num_resolved'                      => 'sometimes',
        's3_dump_md5'                       => 'sometimes|nullable|max:255',
        'last_s3_dump_name'                 => 'sometimes|nullable|max:255',
        'last_s3_dump_date'                 => 'sometimes',
        'last_s3_dump_name_report_template' => 'sometimes|nullable|max:255',
        'last_s3_dump_date_report_template' => 'sometimes',
        'created_at'                        => 'sometimes',
        'updated_at'                        => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceApproval",
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
        "user",
        "property",
        "reportTemplate",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "user",
    ];

    public function construct_scaffold()
    {
        $this->setTable('advanced_variances');
        $this->setFillable(
            [

                'advanced_variance_status',
                'threshold_mode',
                'advanced_variance_start_date',
                'target_locked_date',
                'period_type',
                'trigger_mode',
                'property_id',
                'report_template_id',
                'as_of_month',
                'as_of_year',
                'locked_date',
                'locker_user_id',
                'num_flagged_via_policy',
                'num_flagged_manually',
                'num_flagged',
                'num_explained',
                'num_line_items',
                'num_resolved',
                's3_dump_md5',
                'last_s3_dump_name',
                'last_s3_dump_date',
                'last_s3_dump_name_report_template',
                'last_s3_dump_date_report_template',

            ]
        );
        $this->setCasts(
            [

                'id'                                => 'integer',
                'advanced_variance_status'          => 'string',
                'threshold_mode'                    => 'string',
                'advanced_variance_start_date'      => 'datetime',
                'target_locked_date'                => 'datetime',
                'period_type'                       => 'string',
                'trigger_mode'                      => 'string',
                'property_id'                       => 'integer',
                'report_template_id'                => 'integer',
                'as_of_month'                       => 'integer',
                'as_of_year'                        => 'integer',
                'locked_date'                       => 'datetime',
                'locker_user_id'                    => 'integer',
                'num_flagged_via_policy'            => 'integer',
                'num_flagged_manually'              => 'integer',
                'num_flagged'                       => 'integer',
                'num_explained'                     => 'integer',
                'num_line_items'                    => 'integer',
                'num_resolved'                      => 'integer',
                's3_dump_md5'                       => 'string',
                'last_s3_dump_name'                 => 'string',
                'last_s3_dump_date'                 => 'datetime',
                'last_s3_dump_name_report_template' => 'string',
                'last_s3_dump_date_report_template' => 'datetime',

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
            'locker_user_id',
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
    public function users()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            User::class,
            'advanced_variance_approvals',
            'advanced_variance_id',
            'approving_user_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceApprovals()
    {
        return $this->hasMany(
            AdvancedVarianceApproval::class,
            'advanced_variance_id',
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
            'advanced_variance_id',
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
            $rules = array_merge(AdvancedVariance::$baseRules, AdvancedVariance::$rules);
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
        return AdvancedVariance::class;
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
