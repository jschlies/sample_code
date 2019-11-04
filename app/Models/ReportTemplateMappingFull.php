<?php

namespace App\Waypoint\Models;

/**
 * Class ReportTemplateMappingFull
 * @package App\Waypoint\Models
 */
class ReportTemplateMappingFull extends ReportTemplateMapping
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            'id'                               => $this->id,
            'native_account_id'                => $this->native_account_id,
            'report_template_account_group_id' => $this->report_template_account_group_id,
            'nativeAccountDetail'              => $this->nativeAccountDetail->toArray(),

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}