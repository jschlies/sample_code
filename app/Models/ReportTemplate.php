<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ReportTemplate
 * @package App\Waypoint\Models
 */
class ReportTemplate extends ReportTemplateModelBase
{
    const BOMA_REPORT_TEMPLATE_NAME = 'BOMA Chart of Accounts';
    const BOMA_ROOT_CODE_ID         = 1;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'report_template_name'        => 'required|min:3|string|max:255',
        'report_template_description' => 'sometimes|nullable|min:3|string|max:255',
    ];
    /**
     * @var array
     *
     * set default values here
     */
    protected $attributes = [
        'externally_synced' => false,
    ];

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
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                                          => $this->id,
            "report_template_name"                        => $this->report_template_name,
            "report_template_description"                 => $this->report_template_description,
            "client_id"                                   => $this->client_id,
            "is_boma_report_template"                     => $this->is_boma_report_template,
            "is_default_advance_variance_report_template" => $this->is_default_advance_variance_report_template ? true : false,
            "is_default_analytics_report_template"        => $this->is_default_analytics_report_template ? true : false,
            "is_data_calcs_enabled"                       => $this->is_data_calcs_enabled ? true : false,

            's3_dump_md5'       => $this->s3_dump_md5,
            'last_s3_dump_name' => $this->last_s3_dump_name,
            'last_s3_dump_date' => $this->last_s3_dump_date,

            'externally_synced' => $this->externally_synced,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @param null $rules
     * @param null $object_id
     * @return array|null
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateAccountGroupsFull()
    {
        return $this->hasMany(
            ReportTemplateAccountGroupFull::class,
            'report_template_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateAccountGroupsChildrenFull()
    {
        $relation = $this->hasMany(
            ReportTemplateAccountGroupFull::class,
            'report_template_id',
            'id'
        );
        $relation->getQuery()
                 ->whereNull('parent_report_template_account_group_id');
        return $relation;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateAccountGroupsChildren()
    {
        $relation = $this->hasMany(
            ReportTemplateAccountGroup::class,
            'report_template_id',
            'id'
        );
        $relation->getQuery()
                 ->whereNull('parent_report_template_account_group_id');
        return $relation;
    }

    /**
     * @return Collection|array
     */
    public function getAllNativeAccounts()
    {
        $return_me = new Collection();
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        foreach ($this->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
        {
            /** @var ReportTemplateMapping $ReportTemplateMappingObj */
            foreach ($ReportTemplateAccountGroupObj->reportTemplateMappings as $ReportTemplateMappingObj)
            {
                $return_me[] = $ReportTemplateMappingObj->nativeAccount;
            }
        }
        return $return_me;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldsFull()
    {
        return $this->hasMany(
            CalculatedFieldFull::class,
            'report_template_id',
            'id'
        );
    }
}
