<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceLineItem
 * @package App\Waypoint\Models
 */
class AdvancedVarianceLineItemWorkflow extends AdvancedVarianceLineItem
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                               => $this->id,
            "advanced_variance_id"             => $this->advanced_variance_id,
            "native_account_id"                => $this->native_account_id,
            "report_template_account_group_id" => $this->report_template_account_group_id,
            "calculated_field_id"              => $this->calculated_field_id,
            "line_item_name"                   => $this->line_item_name,

            "flagged_via_policy"    => $this->flagged_via_policy,
            "flagged_manually"      => $this->flagged_manually,
            "flagged_manually_date" => $this->perhaps_format_date($this->flagged_manually_date),
            "flagger_user_id"       => $this->flagger_user_id,

            "num_flagged"            => $this->num_flagged,
            "num_flagged_via_policy" => $this->num_flagged_via_policy,
            "num_flagged_manually"   => $this->num_flagged_manually,
            "num_explained"          => $this->num_explained,
            "num_resolved"           => $this->num_resolved,

            "resolver_user_id" => $this->resolver_user_id,
            "resolved_date"    => $this->perhaps_format_date($this->resolved_date),

            "explanation_update_date"               => $this->perhaps_format_date($this->explanation_update_date),
            "explanation"                           => $this->explanation,
            "explainer_id"                          => $this->explainer_id,
            "advanced_variance_line_item_status"    => $this->get_advanced_variance_line_item_status(),
            "advanced_variance_explanation_type_id" => $this->advanced_variance_explanation_type_id,
            "explanation_type_date"                 => $this->perhaps_format_date($this->explanation_type_date),
            "explanation_type_user_id"              => $this->explanation_type_user_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}