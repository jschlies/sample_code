<?php

namespace App\Waypoint\Models;

/**
 * Class ReportTemplateAccountGroupFull
 * @package App\Waypoint\Models
 */
class ReportTemplateAccountGroupFull extends ReportTemplateAccountGroup
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                                      => $this->id,
            "report_template_account_group_name"      => $this->report_template_account_group_name,
            "report_template_account_group_code"      => $this->report_template_account_group_code,
            "nativeAccountType"                       => $this->nativeAccountTypeDetail,
            'native_account_type_id'                  => $this->native_account_type_id,
            "display_name"                            => $this->display_name,
            "report_template_id"                      => $this->report_template_id,
            "description"                             => $this->description,
            "is_category"                             => $this->is_category,
            "is_major_category"                       => $this->is_major_category,
            "is_waypoint_specific"                    => $this->is_waypoint_specific,
            'parent_report_template_account_group_id' => $this->parent_report_template_account_group_id,
            'usage_type'                              => $this->usage_type,
            'sorting'                                 => $this->sorting,
            'version_num'                             => $this->version_num,
            'deprecated_waypoint_code'                => $this->deprecated_waypoint_code,
            'coefficient_actual'                      => $this->coefficient_actual,
            'coefficient_budgeted'                    => $this->coefficient_budgeted,
            'boma_account_header_1_code_old'          => $this->boma_account_header_1_code_old,
            'boma_account_header_1_name_old'          => $this->boma_account_header_1_name_old,
            'boma_account_header_2_code_old'          => $this->boma_account_header_2_code_old,
            'boma_account_header_2_name_old'          => $this->boma_account_header_2_name_old,
            'boma_account_header_3_code_old'          => $this->boma_account_header_3_code_old,
            'boma_account_header_3_name_old'          => $this->boma_account_header_3_name_old,
            'boma_account_header_4_code_old'          => $this->boma_account_header_4_code_old,
            'boma_account_header_4_name_old'          => $this->boma_account_header_4_name_old,
            'boma_account_header_5_code_old'          => $this->boma_account_header_5_code_old,
            'boma_account_header_5_name_old'          => $this->boma_account_header_5_name_old,
            'boma_account_header_6_code_old'          => $this->boma_account_header_6_code_old,
            'boma_account_header_6_name_old'          => $this->boma_account_header_6_name_old,
            'children'                                => $this->reportTemplateAccountGroupChildrenFull->toArray(),
            'reportTemplateMappingsFull'              => $this->reportTemplateMappingsFull->toArray(),

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
