<?php

namespace App\Waypoint\Models;

/**
 * Class ReportTemplateMapping
 * @package App\Waypoint\Models
 */
class ReportTemplateMapping extends ReportTemplateMappingModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * ReportTemplateMapping constructor.
     * @param array $attributes
     * @throws \App\Waypoint\Exceptions\GeneralException
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
            "id"                               => $this->id,
            "native_account_id"                => $this->native_account_id,
            "report_template_account_group_id" => $this->report_template_account_group_id,

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountDetail()
    {
        return $this->belongsTo(
            NativeAccountDetail::class,
            'native_account_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function reportTemplateAccountGroupFull()
    {
        return $this->belongsTo(
            ReportTemplateAccountGroup::class,
            'report_template_account_group_id',
            'id'
        );
    }
}
