<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceLineItemDetail
 * @package App\Waypoint\Models
 */
class AdvancedVarianceLineItemDetail extends AdvancedVarianceLineItem
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
            "id"                   => $this->id,
            "advanced_variance_id" => $this->advanced_variance_id,
            "property_id"          => $this->advancedVariance->property_id,

            "native_account_id" => $this->native_account_id,

            "report_template_account_group_id" => $this->report_template_account_group_id,

            "calculated_field_id"                 => $this->calculated_field_id,
            "calculation_result_info"             => $this->calculation_result_info,
            "calculation_name"                    => $this->calculation_name,
            "calculation_description"             => $this->calculation_description,
            "calculation_equation_string"         => $this->calculation_equation_string,
            "calculation_display_equation_string" => $this->calculation_display_equation_string,

            "native_account_type_name" => $this->get_native_account_type_name() ?: null,
            "native_account_type_id"   => $this->get_native_account_type() ? $this->get_native_account_type()->id : null,

            "calculated_field_overage_threshold_amount"                        => $this->calculated_field_overage_threshold_amount,
            "calculated_field_overage_threshold_amount_too_good"               => $this->calculated_field_overage_threshold_amount_too_good,
            "calculated_field_overage_threshold_percent"                       => $this->calculated_field_overage_threshold_percent,
            "calculated_field_overage_threshold_percent_too_good"              => $this->calculated_field_overage_threshold_percent_too_good,
            "calculated_field_overage_threshold_operator"                      => $this->calculated_field_overage_threshold_operator,
            "native_account_overage_threshold_amount"                          => $this->native_account_overage_threshold_amount,
            "native_account_overage_threshold_amount_too_good"                 => $this->native_account_overage_threshold_amount_too_good,
            "native_account_overage_threshold_percent"                         => $this->native_account_overage_threshold_percent,
            "native_account_overage_threshold_percent_too_good"                => $this->native_account_overage_threshold_percent_too_good,
            "native_account_overage_threshold_operator"                        => $this->native_account_overage_threshold_operator,
            "report_template_account_group_overage_threshold_amount"           => $this->report_template_account_group_overage_threshold_amount,
            "report_template_account_group_overage_threshold_amount_too_good"  => $this->report_template_account_group_overage_threshold_amount_too_good,
            "report_template_account_group_overage_threshold_percent"          => $this->report_template_account_group_overage_threshold_percent,
            "report_template_account_group_overage_threshold_percent_too_good" => $this->report_template_account_group_overage_threshold_percent_too_good,
            "report_template_account_group_overage_threshold_operator"         => $this->report_template_account_group_overage_threshold_operator,

            "monthly_budgeted"         => $this->monthly_budgeted,
            "monthly_actual"           => $this->monthly_actual,
            "monthly_variance"         => $this->monthly_variance,
            "monthly_percent_variance" => $this->isMonthlyBudgetZeroAndActualNonZero() ? '––%' : $this->monthly_percent_variance,

            "qtd_budgeted"         => $this->qtd_budgeted,
            "qtd_actual"           => $this->qtd_actual,
            "qtd_variance"         => $this->qtd_variance,
            "qtd_percent_variance" => $this->isQTDBudgetZeroAndActualNonZero() ? '––%' : $this->qtd_percent_variance,

            "qtr_monthly_month_1_budgeted"         => $this->qtr_monthly_month_1_budgeted,
            "qtr_monthly_month_1_actual"           => $this->qtr_monthly_month_1_actual,
            'qtr_monthly_month_1_variance'         => $this->qtr_monthly_month_1_variance,
            'qtr_monthly_month_1_percent_variance' => $this->qtr_monthly_month_1_percent_variance,

            "qtr_monthly_month_2_budgeted"         => $this->qtr_monthly_month_2_budgeted,
            "qtr_monthly_month_2_actual"           => $this->qtr_monthly_month_2_actual,
            'qtr_monthly_month_2_variance'         => $this->qtr_monthly_month_2_variance,
            'qtr_monthly_month_2_percent_variance' => $this->qtr_monthly_month_2_percent_variance,

            "qtr_monthly_month_3_budgeted"         => $this->qtr_monthly_month_3_budgeted,
            "qtr_monthly_month_3_actual"           => $this->qtr_monthly_month_3_actual,
            'qtr_monthly_month_3_variance'         => $this->qtr_monthly_month_3_variance,
            'qtr_monthly_month_3_percent_variance' => $this->qtr_monthly_month_3_percent_variance,

            'qtr_qtd_month_1_actual'           => $this->qtr_qtd_month_1_actual,
            'qtr_qtd_month_1_budgeted'         => $this->qtr_qtd_month_1_budgeted,
            'qtr_qtd_month_1_variance'         => $this->qtr_qtd_month_1_variance,
            'qtr_qtd_month_1_percent_variance' => $this->qtr_qtd_month_1_percent_variance,

            'qtr_qtd_month_2_actual'           => $this->qtr_qtd_month_2_actual,
            'qtr_qtd_month_2_budgeted'         => $this->qtr_qtd_month_2_budgeted,
            'qtr_qtd_month_2_variance'         => $this->qtr_qtd_month_2_variance,
            'qtr_qtd_month_2_percent_variance' => $this->qtr_qtd_month_2_percent_variance,

            'qtr_qtd_month_3_actual'           => $this->qtr_qtd_month_3_actual,
            'qtr_qtd_month_3_budgeted'         => $this->qtr_qtd_month_3_budgeted,
            'qtr_qtd_month_3_variance'         => $this->qtr_qtd_month_3_variance,
            'qtr_qtd_month_3_percent_variance' => $this->qtr_qtd_month_3_percent_variance,

            'qtr_ytd_month_1_actual'           => $this->qtr_ytd_month_1_actual,
            'qtr_ytd_month_1_budgeted'         => $this->qtr_ytd_month_1_budgeted,
            'qtr_ytd_month_1_variance'         => $this->qtr_ytd_month_1_variance,
            'qtr_ytd_month_1_percent_variance' => $this->qtr_ytd_month_1_percent_variance,

            'qtr_ytd_month_2_actual'           => $this->qtr_ytd_month_2_actual,
            'qtr_ytd_month_2_budgeted'         => $this->qtr_ytd_month_2_budgeted,
            'qtr_ytd_month_2_variance'         => $this->qtr_ytd_month_2_variance,
            'qtr_ytd_month_2_percent_variance' => $this->qtr_ytd_month_2_percent_variance,

            'qtr_ytd_month_3_actual'           => $this->qtr_ytd_month_3_actual,
            'qtr_ytd_month_3_budgeted'         => $this->qtr_ytd_month_3_budgeted,
            'qtr_ytd_month_3_variance'         => $this->qtr_ytd_month_3_variance,
            'qtr_ytd_month_3_percent_variance' => $this->qtr_ytd_month_3_percent_variance,

            'forecast_budgeted'         => $this->forecast_budgeted,
            'forecast_actual'           => $this->forecast_actual,
            'forecast_variance'         => $this->forecast_variance,
            'forecast_percent_variance' => $this->forecast_percent_variance,

            "ytd_budgeted"         => $this->ytd_budgeted,
            "ytd_actual"           => $this->ytd_actual,
            "ytd_variance"         => $this->ytd_variance,
            "ytd_percent_variance" => $this->isYTDBudgetZeroAndActualNonZero() ? '––%' : $this->ytd_percent_variance,

            "total_monthly_budgeted"         => $this->total_monthly_budgeted,
            "total_monthly_actual"           => $this->total_monthly_actual,
            'total_monthly_variance'         => $this->total_monthly_variance,
            'total_monthly_percent_variance' => $this->total_monthly_percent_variance,

            "total_qtd_budgeted"         => $this->total_qtd_budgeted,
            "total_qtd_actual"           => $this->total_qtd_actual,
            'total_qtd_variance'         => $this->total_qtd_variance,
            'total_qtd_percent_variance' => $this->total_qtd_percent_variance,

            "total_qtr_monthly_month_1_budgeted"         => $this->total_qtr_monthly_month_1_budgeted,
            "total_qtr_monthly_month_1_actual"           => $this->total_qtr_monthly_month_1_actual,
            'total_qtr_monthly_month_1_variance'         => $this->total_qtr_monthly_month_1_variance,
            'total_qtr_monthly_month_1_percent_variance' => $this->total_qtr_monthly_month_1_percent_variance,

            "total_qtr_monthly_month_2_budgeted"         => $this->total_qtr_monthly_month_2_budgeted,
            "total_qtr_monthly_month_2_actual"           => $this->total_qtr_monthly_month_2_actual,
            'total_qtr_monthly_month_2_variance'         => $this->total_qtr_monthly_month_2_variance,
            'total_qtr_monthly_month_2_percent_variance' => $this->total_qtr_monthly_month_2_percent_variance,

            "total_qtr_monthly_month_3_budgeted"         => $this->total_qtr_monthly_month_3_budgeted,
            "total_qtr_monthly_month_3_actual"           => $this->total_qtr_monthly_month_3_actual,
            'total_qtr_monthly_month_3_variance'         => $this->total_qtr_monthly_month_3_variance,
            'total_qtr_monthly_month_3_percent_variance' => $this->total_qtr_monthly_month_3_percent_variance,

            'total_qtr_qtd_month_1_actual'           => $this->total_qtr_qtd_month_1_actual,
            'total_qtr_qtd_month_1_budgeted'         => $this->total_qtr_qtd_month_1_budgeted,
            'total_qtr_qtd_month_1_variance'         => $this->total_qtr_qtd_month_1_variance,
            'total_qtr_qtd_month_1_percent_variance' => $this->total_qtr_qtd_month_1_percent_variance,

            'total_qtr_qtd_month_2_actual'           => $this->total_qtr_qtd_month_2_actual,
            'total_qtr_qtd_month_2_budgeted'         => $this->total_qtr_qtd_month_2_budgeted,
            'total_qtr_qtd_month_2_variance'         => $this->total_qtr_qtd_month_2_variance,
            'total_qtr_qtd_month_2_percent_variance' => $this->total_qtr_qtd_month_2_percent_variance,

            'total_qtr_qtd_month_3_actual'           => $this->total_qtr_qtd_month_3_actual,
            'total_qtr_qtd_month_3_budgeted'         => $this->total_qtr_qtd_month_3_budgeted,
            'total_qtr_qtd_month_3_variance'         => $this->total_qtr_qtd_month_3_variance,
            'total_qtr_qtd_month_3_percent_variance' => $this->total_qtr_qtd_month_3_percent_variance,

            'total_qtr_ytd_month_1_actual'           => $this->total_qtr_ytd_month_1_actual,
            'total_qtr_ytd_month_1_budgeted'         => $this->total_qtr_ytd_month_1_budgeted,
            'total_qtr_ytd_month_1_variance'         => $this->total_qtr_ytd_month_1_variance,
            'total_qtr_ytd_month_1_percent_variance' => $this->total_qtr_ytd_month_1_percent_variance,

            'total_qtr_ytd_month_2_actual'           => $this->total_qtr_ytd_month_2_actual,
            'total_qtr_ytd_month_2_budgeted'         => $this->total_qtr_ytd_month_2_budgeted,
            'total_qtr_ytd_month_2_variance'         => $this->total_qtr_ytd_month_2_variance,
            'total_qtr_ytd_month_2_percent_variance' => $this->total_qtr_ytd_month_2_percent_variance,

            'total_qtr_ytd_month_3_actual'           => $this->total_qtr_ytd_month_3_actual,
            'total_qtr_ytd_month_3_budgeted'         => $this->total_qtr_ytd_month_3_budgeted,
            'total_qtr_ytd_month_3_variance'         => $this->total_qtr_ytd_month_3_variance,
            'total_qtr_ytd_month_3_percent_variance' => $this->total_qtr_ytd_month_3_percent_variance,

            'total_qtr_forecast_month_1_budgeted'         => $this->total_qtr_forecast_month_1_budgeted,
            'total_qtr_forecast_month_1_actual'           => $this->total_qtr_forecast_month_1_actual,
            'total_qtr_forecast_month_1_variance'         => $this->total_qtr_forecast_month_1_variance,
            'total_qtr_forecast_month_1_percent_variance' => $this->total_qtr_forecast_month_1_percent_variance,
            'total_qtr_forecast_month_2_budgeted'         => $this->total_qtr_forecast_month_2_budgeted,
            'total_qtr_forecast_month_2_actual'           => $this->total_qtr_forecast_month_2_actual,
            'total_qtr_forecast_month_2_variance'         => $this->total_qtr_forecast_month_2_variance,
            'total_qtr_forecast_month_2_percent_variance' => $this->total_qtr_forecast_month_2_percent_variance,
            'total_qtr_forecast_month_3_budgeted'         => $this->total_qtr_forecast_month_3_budgeted,
            'total_qtr_forecast_month_3_actual'           => $this->total_qtr_forecast_month_3_actual,
            'total_qtr_forecast_month_3_variance'         => $this->total_qtr_forecast_month_3_variance,
            'total_qtr_forecast_month_3_percent_variance' => $this->total_qtr_forecast_month_3_percent_variance,

            'total_forecast_budgeted'         => $this->total_forecast_budgeted,
            'total_forecast_actual'           => $this->total_forecast_actual,
            'total_forecast_variance'         => $this->total_forecast_variance,
            'total_forecast_percent_variance' => $this->total_forecast_percent_variance,

            'qtr_forecast_month_1_budgeted'         => $this->qtr_forecast_month_1_budgeted,
            'qtr_forecast_month_1_actual'           => $this->qtr_forecast_month_1_actual,
            'qtr_forecast_month_1_variance'         => $this->qtr_forecast_month_1_variance,
            'qtr_forecast_month_1_percent_variance' => $this->qtr_forecast_month_1_percent_variance,
            'qtr_forecast_month_2_budgeted'         => $this->qtr_forecast_month_2_budgeted,
            'qtr_forecast_month_2_actual'           => $this->qtr_forecast_month_2_actual,
            'qtr_forecast_month_2_variance'         => $this->qtr_forecast_month_2_variance,
            'qtr_forecast_month_2_percent_variance' => $this->qtr_forecast_month_2_percent_variance,
            'qtr_forecast_month_3_budgeted'         => $this->qtr_forecast_month_3_budgeted,
            'qtr_forecast_month_3_actual'           => $this->qtr_forecast_month_3_actual,
            'qtr_forecast_month_3_variance'         => $this->qtr_forecast_month_3_variance,
            'qtr_forecast_month_3_percent_variance' => $this->qtr_forecast_month_3_percent_variance,

            "total_ytd_budgeted"         => $this->total_ytd_budgeted,
            "total_ytd_actual"           => $this->total_ytd_actual,
            'total_ytd_variance'         => $this->total_ytd_variance,
            'total_ytd_percent_variance' => $this->total_ytd_percent_variance,

            "comments" => $this->getComments()->toArray(),

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "as_of_month" => $this->advancedVariance->as_of_month,
            "as_of_year"  => $this->advancedVariance->as_of_year,

            "advanced_variance_coefficient" => $this->line_item_coefficient,
            "line_item_name"                => $this->line_item_name,
            "line_item_code"                => $this->line_item_code,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
