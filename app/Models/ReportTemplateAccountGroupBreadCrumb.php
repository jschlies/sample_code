<?php

namespace App\Waypoint\Models;

/**
 * Class BomaCoaLineItemBreadCrumb
 * @package App\Waypoint\Models
 */
class ReportTemplateAccountGroupBreadCrumb extends ReportTemplateAccountGroup
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                             => $this->id,
            "code"                           => $this->report_template_account_group_code,
            "deprecated_code"                => $this->deprecated_waypoint_code,
            "name"                           => $this->report_template_account_group_name,
            'native_account_type_id'         => $this->native_account_type_id,
            "display_name"                   => $this->display_name,
            "description"                    => $this->description,
            "usage_type"                     => $this->usage_type,
            "sorting"                        => $this->sorting,
            "version_num"                    => $this->version_num,
            "is_waypoint_specific"           => $this->is_waypoint_specific,
            "is_category"                    => $this->is_category,
            "deprecated_waypoint_code"       => $this->deprecated_waypoint_code,
            "parent_boma_coa_line_item_id"   => $this->parent_boma_coa_line_item_id,
            "is_major_category"              => $this->is_major_category,
            "boma_account_header_1_code_old" => $this->boma_account_header_1_code_old,
            "boma_account_header_2_code_old" => $this->boma_account_header_2_code_old,
            "boma_account_header_3_code_old" => $this->boma_account_header_3_code_old,
            "boma_account_header_4_code_old" => $this->boma_account_header_4_code_old,
            "boma_account_header_5_code_old" => $this->boma_account_header_5_code_old,
            "boma_account_header_6_code_old" => $this->boma_account_header_6_code_old,
            "boma_account_header_1_name_old" => $this->boma_account_header_1_name_old,
            "boma_account_header_2_name_old" => $this->boma_account_header_2_name_old,
            "boma_account_header_3_name_old" => $this->boma_account_header_3_name_old,
            "boma_account_header_4_name_old" => $this->boma_account_header_4_name_old,
            "boma_account_header_5_name_old" => $this->boma_account_header_5_name_old,
            "boma_account_header_6_name_old" => $this->boma_account_header_6_name_old,
            "num_child_line_items"           => $this->reportTemplateAccountGroupChildren->count(),
            "children"                       => $this->getChildren(),
            "lineage"                        => $this->getLineage(),

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => get_class($this),
        ];
    }
}
