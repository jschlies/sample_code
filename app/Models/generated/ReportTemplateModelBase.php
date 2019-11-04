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
 * Class ReportTemplate
 *
 * @method static ReportTemplate find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static ReportTemplate|Collection findOrFail($id, $columns = ['*']) desc
 */
class ReportTemplateModelBase extends Model
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
        'id'                                          => 'sometimes|integer',
        'report_template_name'                        => 'sometimes|max:255',
        'is_boma_report_template'                     => 'sometimes|boolean',
        'is_default_analytics_report_template'        => 'sometimes|boolean',
        'is_default_advance_variance_report_template' => 'sometimes|boolean',
        'is_data_calcs_enabled'                       => 'sometimes|boolean',
        'report_template_description'                 => 'sometimes|nullable|min:3|max:255',
        'client_id'                                   => 'required|integer',
        's3_dump_md5'                                 => 'sometimes|nullable|max:255',
        'last_s3_dump_name'                           => 'sometimes|nullable|max:255',
        'last_s3_dump_date'                           => 'sometimes',
        'externally_synced'                           => 'sometimes|boolean',
        'created_at'                                  => 'sometimes',
        'updated_at'                                  => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVariance",
        "calculatedField",
        "reportTemplateAccountGroup",
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
        $this->setTable('report_templates');
        $this->setFillable(
            [

                'report_template_name',
                'is_boma_report_template',
                'is_default_analytics_report_template',
                'is_default_advance_variance_report_template',
                'is_data_calcs_enabled',
                'report_template_description',
                'client_id',
                's3_dump_md5',
                'last_s3_dump_name',
                'externally_synced',

            ]
        );
        $this->setCasts(
            [

                'id'                                          => 'integer',
                'report_template_name'                        => 'string',
                'is_boma_report_template'                     => 'boolean',
                'is_default_analytics_report_template'        => 'boolean',
                'is_default_advance_variance_report_template' => 'boolean',
                'is_data_calcs_enabled'                       => 'boolean',
                'report_template_description'                 => 'string',
                'client_id'                                   => 'integer',
                's3_dump_md5'                                 => 'string',
                'last_s3_dump_name'                           => 'string',
                'last_s3_dump_date'                           => 'datetime',
                'externally_synced'                           => 'boolean',

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
    public function advancedVariances()
    {
        return $this->hasMany(
            AdvancedVariance::class,
            'report_template_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFields()
    {
        return $this->hasMany(
            CalculatedField::class,
            'report_template_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateAccountGroups()
    {
        return $this->hasMany(
            ReportTemplateAccountGroup::class,
            'report_template_id',
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
            $rules = array_merge(ReportTemplate::$baseRules, ReportTemplate::$rules);
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
        return ReportTemplate::class;
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
