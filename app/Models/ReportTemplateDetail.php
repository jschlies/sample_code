<?php

namespace App\Waypoint\Models;

/**
 * Class ReportTemplateDetail
 * @package App\Waypoint\Models
 */
class ReportTemplateDetail extends ReportTemplate
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
            "report_template_description" => $this->report_template_description,
            "client_id"                   => $this->client_id,

            "reportTemplateAccountGroupsChildren" => $this->reportTemplateAccountGroupsChildren->toArray(),

            "is_boma_report_template"                     => $this->is_boma_report_template,
            "is_default_advance_variance_report_template" => $this->is_default_advance_variance_report_template,
            "is_default_analytics_report_template"        => $this->is_default_analytics_report_template,
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
}
