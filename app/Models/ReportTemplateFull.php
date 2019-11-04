<?php

namespace App\Waypoint\Models;

/**
 * Class ReportTemplateFull
 * @package App\Waypoint\Models
 */
class ReportTemplateFull extends ReportTemplate
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                          => $this->id,
            "report_template_name"        => $this->report_template_name,
            "report_template_description" => $this->report_template_name,
            "client_id"                   => $this->client_id,

            "children"             => $this->reportTemplateAccountGroupsChildrenFull->toArray(),
            "calculatedFieldsFull" => $this->calculatedFieldsFull->toArray(),

            "is_boma_report_template"                     => $this->is_boma_report_template,
            "is_default_advance_variance_report_template" => $this->is_default_advance_variance_report_template,
            "is_default_analytics_report_template"        => $this->is_default_analytics_report_template,
            "is_data_calcs_enabled"                       => $this->is_data_calcs_enabled ? true : false,

            's3_dump_md5'                       => $this->s3_dump_md5,
            'last_s3_dump_name'                 => $this->last_s3_dump_name,
            'last_s3_dump_date'                 => $this->last_s3_dump_date,
            'last_s3_dump_name_report_template' => $this->last_s3_dump_name_report_template,
            'last_s3_dump_date_report_template' => $this->perhaps_format_date($this->last_s3_dump_date_report_template),

            'externally_synced' => $this->externally_synced,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
