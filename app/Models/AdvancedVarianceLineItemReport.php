<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceLineItemReport
 * @package App\Waypoint\Models
 */
class AdvancedVarianceLineItemReport extends AdvancedVarianceLineItemDetail
{
    /** @var integer */
    public $depth = 99;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [

            "Report Template Account Group Code" => $this->line_item_code,
            "Report Template Account Group"      =>
                $this->report_template_account_group_id
                    ?
                    str_pad(
                        $this->line_item_name,
                        strlen($this->line_item_name) + ($this->depth * 4),
                        "    ",
                        STR_PAD_LEFT
                    )
                    :
                    '',

            "Native Account Code" => $this->native_account_id ? $this->line_item_code : '',
            "Native Account"      => $this->native_account_id ? $this->line_item_name : '',

            "YTD Budgeted"         => $this->native_account_id ? $this->ytd_budgeted : '',
            "YTD Actual"           => $this->native_account_id ? $this->ytd_actual : '',
            "YTD Variance"         => $this->native_account_id ? $this->ytd_variance : '',
            "YTD Percent Variance" => $this->native_account_id && $this->isYTDBudgetZeroAndActualNonZero() ? '' : $this->ytd_percent_variance,

            "Total YTD Budgeted"         => $this->report_template_account_group_id ? $this->total_ytd_budgeted : '',
            "Total YTD Actual"           => $this->report_template_account_group_id ? $this->total_ytd_actual : '',
            'Total YTD Variance'         => $this->report_template_account_group_id ? $this->total_ytd_variance : '',
            'Total YTD Percent Variance' => $this->report_template_account_group_id ? $this->total_ytd_percent_variance : '',

            "Advanced Variance Line Item Status" => $this->get_advanced_variance_line_item_status(),

            "Explanation"             => $this->explanation,
            "Explanation Update Date" => $this->explanation_update_date ? $this->perhaps_format_date($this->explanation_update_date) : '',
            "Explainer"               => $this->explainer_id ? $this->explainerUser->firstname . ' ' . $this->explainerUser->lastname : '',

            "Resolver"      => $this->resolver_user_id ? $this->resolverUser->firstname . ' ' . $this->resolverUser->lastname : '',
            "Resolved Date" => $this->resolver_user_id ? $this->perhaps_format_date($this->resolved_date) : '',

            "num_flagged_via_policy" => $this->report_template_account_group_id ? $this->num_flagged_via_policy : '',
            "num_flagged_manually"   => $this->report_template_account_group_id ? $this->num_flagged_manually : '',
            "num_explained"          => $this->report_template_account_group_id ? $this->num_explained : '',

            "Flagged via Policy"    => $this->flagged_via_policy,
            "Flagged Manually"      => $this->flagged_manually,
            "Flagger User"          => $this->flagger_user_id ? $this->flaggerUser->firstname . ' ' . $this->flaggerUser->lastname : '',
            "Flagged Manually Date" => $this->perhaps_format_date($this->flagged_manually_date),
        ];
    }
}
