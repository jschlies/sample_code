<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Model;

/**
 * Class AdvancedVarianceLineItem
 *
 * @method static AdvancedVarianceLineItem find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static AdvancedVarianceLineItem|Collection findOrFail($id, $columns = ['*']) desc
 */
class AdvancedVarianceLineItemModelBase extends Model
{
    /**
     * Generated
     */

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
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                                                               => 'sometimes|integer',
        'advanced_variance_id'                                             => 'required|integer',
        'native_account_id'                                                => 'sometimes|integer|nullable',
        'report_template_account_group_id'                                 => 'sometimes|integer|nullable',
        'calculated_field_id'                                              => 'sometimes|integer|nullable',
        'calculation_result_info'                                          => 'sometimes|nullable|max:255',
        'calculation_name'                                                 => 'sometimes|nullable|max:255',
        'calculation_description'                                          => 'sometimes|nullable|max:255',
        'calculation_equation_string'                                      => 'sometimes|nullable|max:255',
        'calculation_display_equation_string'                              => 'sometimes|nullable|max:255',
        'native_account_overage_threshold_amount'                          => 'sometimes|nullable|numeric',
        'native_account_overage_threshold_amount_too_good'                 => 'sometimes|nullable|numeric',
        'native_account_overage_threshold_percent'                         => 'sometimes|nullable|numeric',
        'native_account_overage_threshold_percent_too_good'                => 'sometimes|nullable|numeric',
        'native_account_overage_threshold_operator'                        => 'sometimes|nullable|max:255',
        'report_template_account_group_overage_threshold_amount'           => 'sometimes|nullable|numeric',
        'report_template_account_group_overage_threshold_amount_too_good'  => 'sometimes|nullable|numeric',
        'report_template_account_group_overage_threshold_percent'          => 'sometimes|nullable|numeric',
        'report_template_account_group_overage_threshold_percent_too_good' => 'sometimes|nullable|numeric',
        'report_template_account_group_overage_threshold_operator'         => 'sometimes|nullable|max:255',
        'calculated_field_overage_threshold_amount'                        => 'sometimes|nullable|numeric',
        'calculated_field_overage_threshold_amount_too_good'               => 'sometimes|nullable|numeric',
        'calculated_field_overage_threshold_percent'                       => 'sometimes|nullable|numeric',
        'calculated_field_overage_threshold_percent_too_good'              => 'sometimes|nullable|numeric',
        'calculated_field_overage_threshold_operator'                      => 'sometimes|nullable|max:255',
        'line_item_coefficient'                                            => 'sometimes|nullable|numeric',
        'line_item_name'                                                   => 'sometimes|nullable|max:255|min:2',
        'line_item_code'                                                   => 'sometimes|nullable|max:255|min:2',
        'flagged_via_policy'                                               => 'sometimes|nullable|boolean',
        'flagged_manually'                                                 => 'sometimes|nullable|boolean',
        'flagged_manually_date'                                            => 'nullable|sometimes',
        'flagger_user_id'                                                  => 'nullable|sometimes|integer',
        'num_flagged'                                                      => 'sometimes|nullable|integer',
        'num_flagged_via_policy'                                           => 'sometimes|nullable|integer',
        'num_flagged_manually'                                             => 'sometimes|nullable|integer',
        'num_explained'                                                    => 'sometimes|nullable|integer',
        'num_resolved'                                                     => 'sometimes|nullable|integer',
        'monthly_budgeted'                                                 => 'sometimes|nullable|numeric',
        'monthly_actual'                                                   => 'sometimes|nullable|numeric',
        'monthly_variance'                                                 => 'sometimes|nullable|numeric',
        'monthly_percent_variance'                                         => 'sometimes|nullable|numeric',
        'qtd_budgeted'                                                     => 'sometimes|nullable|numeric',
        'qtd_actual'                                                       => 'sometimes|nullable|numeric',
        'qtd_variance'                                                     => 'sometimes|nullable|numeric',
        'qtd_percent_variance'                                             => 'sometimes|nullable|numeric',
        'qtr_monthly_month_1_budgeted'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_1_actual'                                       => 'sometimes|nullable|numeric',
        'qtr_monthly_month_1_variance'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_1_percent_variance'                             => 'sometimes|nullable|numeric',
        'qtr_monthly_month_2_budgeted'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_2_actual'                                       => 'sometimes|nullable|numeric',
        'qtr_monthly_month_2_variance'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_2_percent_variance'                             => 'sometimes|nullable|numeric',
        'qtr_monthly_month_3_budgeted'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_3_actual'                                       => 'sometimes|nullable|numeric',
        'qtr_monthly_month_3_variance'                                     => 'sometimes|nullable|numeric',
        'qtr_monthly_month_3_percent_variance'                             => 'sometimes|nullable|numeric',
        'qtr_qtd_month_1_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_qtd_month_1_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_1_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_1_percent_variance'                                 => 'sometimes|nullable|numeric',
        'qtr_qtd_month_2_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_qtd_month_2_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_2_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_2_percent_variance'                                 => 'sometimes|nullable|numeric',
        'qtr_qtd_month_3_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_qtd_month_3_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_3_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_qtd_month_3_percent_variance'                                 => 'sometimes|nullable|numeric',
        'qtr_ytd_month_1_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_ytd_month_1_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_1_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_1_percent_variance'                                 => 'sometimes|nullable|numeric',
        'qtr_ytd_month_2_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_ytd_month_2_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_2_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_2_percent_variance'                                 => 'sometimes|nullable|numeric',
        'qtr_ytd_month_3_actual'                                           => 'sometimes|nullable|numeric',
        'qtr_ytd_month_3_budgeted'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_3_variance'                                         => 'sometimes|nullable|numeric',
        'qtr_ytd_month_3_percent_variance'                                 => 'sometimes|nullable|numeric',
        'forecast_budgeted'                                                => 'sometimes|nullable|numeric',
        'forecast_actual'                                                  => 'sometimes|nullable|numeric',
        'forecast_variance'                                                => 'sometimes|nullable|numeric',
        'forecast_percent_variance'                                        => 'sometimes|nullable|numeric',
        'qtr_forecast_month_1_budgeted'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_1_actual'                                      => 'sometimes|nullable|numeric',
        'qtr_forecast_month_1_variance'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_1_percent_variance'                            => 'sometimes|nullable|numeric',
        'qtr_forecast_month_2_budgeted'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_2_actual'                                      => 'sometimes|nullable|numeric',
        'qtr_forecast_month_2_variance'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_2_percent_variance'                            => 'sometimes|nullable|numeric',
        'qtr_forecast_month_3_budgeted'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_3_actual'                                      => 'sometimes|nullable|numeric',
        'qtr_forecast_month_3_variance'                                    => 'sometimes|nullable|numeric',
        'qtr_forecast_month_3_percent_variance'                            => 'sometimes|nullable|numeric',
        'ytd_budgeted'                                                     => 'sometimes|nullable|numeric',
        'ytd_actual'                                                       => 'sometimes|nullable|numeric',
        'ytd_variance'                                                     => 'sometimes|nullable|numeric',
        'ytd_percent_variance'                                             => 'sometimes|nullable|numeric',
        'total_monthly_budgeted'                                           => 'sometimes|nullable|numeric',
        'total_monthly_actual'                                             => 'sometimes|nullable|numeric',
        'total_monthly_variance'                                           => 'sometimes|nullable|numeric',
        'total_monthly_percent_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtd_budgeted'                                               => 'sometimes|nullable|numeric',
        'total_qtd_actual'                                                 => 'sometimes|nullable|numeric',
        'total_qtd_variance'                                               => 'sometimes|nullable|numeric',
        'total_qtd_percent_variance'                                       => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_1_budgeted'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_1_actual'                                 => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_1_variance'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_1_percent_variance'                       => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_2_budgeted'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_2_actual'                                 => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_2_variance'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_2_percent_variance'                       => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_3_budgeted'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_3_actual'                                 => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_3_variance'                               => 'sometimes|nullable|numeric',
        'total_qtr_monthly_month_3_percent_variance'                       => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_1_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_1_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_1_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_1_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_2_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_2_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_2_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_2_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_3_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_3_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_3_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_qtd_month_3_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_1_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_1_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_1_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_1_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_2_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_2_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_2_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_2_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_3_actual'                                     => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_3_budgeted'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_3_variance'                                   => 'sometimes|nullable|numeric',
        'total_qtr_ytd_month_3_percent_variance'                           => 'sometimes|nullable|numeric',
        'total_forecast_budgeted'                                          => 'sometimes|nullable|numeric',
        'total_forecast_actual'                                            => 'sometimes|nullable|numeric',
        'total_forecast_variance'                                          => 'sometimes|nullable|numeric',
        'total_forecast_percent_variance'                                  => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_1_budgeted'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_1_actual'                                => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_1_variance'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_1_percent_variance'                      => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_2_budgeted'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_2_actual'                                => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_2_variance'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_2_percent_variance'                      => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_3_budgeted'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_3_actual'                                => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_3_variance'                              => 'sometimes|nullable|numeric',
        'total_qtr_forecast_month_3_percent_variance'                      => 'sometimes|nullable|numeric',
        'total_ytd_budgeted'                                               => 'sometimes|nullable|numeric',
        'total_ytd_actual'                                                 => 'sometimes|nullable|numeric',
        'total_ytd_variance'                                               => 'sometimes|nullable|numeric',
        'total_ytd_percent_variance'                                       => 'sometimes|nullable|numeric',
        'resolver_user_id'                                                 => 'nullable|sometimes|integer',
        'resolved_date'                                                    => 'nullable|sometimes',
        'explanation_update_date'                                          => 'nullable|sometimes',
        'explanation'                                                      => 'sometimes|nullable|max:2000|min:2',
        'explainer_id'                                                     => 'nullable|sometimes|integer',
        'advanced_variance_explanation_type_id'                            => 'nullable|sometimes|integer',
        'explanation_type_date'                                            => 'nullable|sometimes',
        'explanation_type_user_id'                                         => 'nullable|sometimes|integer',
        'sort_order'                                                       => 'sometimes|nullable|integer',
        'is_summary'                                                       => 'sometimes|nullable|boolean',
        'is_summary_tab_default_line_item'                                 => 'sometimes|nullable|boolean',
        'created_at'                                                       => 'nullable|sometimes',
        'updated_at'                                                       => 'nullable|sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [

    ];

    /**
     * @var array
     */
    public static $hasOne_arr = [

    ];

    /**
     * @var array
     */
    public static $belongsTo_arr = [
        "advancedVariance",
        "advancedVarianceExplanationType",
        "calculatedField",
        "user",
        "user",
        "user",
        "nativeAccount",
        "user",
        "reportTemplateAccountGroup",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('advanced_variance_line_items');
        $this->setFillable(
            [

                'advanced_variance_id',
                'native_account_id',
                'report_template_account_group_id',
                'calculated_field_id',
                'calculation_result_info',
                'calculation_name',
                'calculation_description',
                'calculation_equation_string',
                'calculation_display_equation_string',
                'native_account_overage_threshold_amount',
                'native_account_overage_threshold_amount_too_good',
                'native_account_overage_threshold_percent',
                'native_account_overage_threshold_percent_too_good',
                'native_account_overage_threshold_operator',
                'report_template_account_group_overage_threshold_amount',
                'report_template_account_group_overage_threshold_amount_too_good',
                'report_template_account_group_overage_threshold_percent',
                'report_template_account_group_overage_threshold_percent_too_good',
                'report_template_account_group_overage_threshold_operator',
                'calculated_field_overage_threshold_amount',
                'calculated_field_overage_threshold_amount_too_good',
                'calculated_field_overage_threshold_percent',
                'calculated_field_overage_threshold_percent_too_good',
                'calculated_field_overage_threshold_operator',
                'line_item_coefficient',
                'line_item_name',
                'line_item_code',
                'flagged_via_policy',
                'flagged_manually',
                'flagged_manually_date',
                'flagger_user_id',
                'num_flagged',
                'num_flagged_via_policy',
                'num_flagged_manually',
                'num_explained',
                'num_resolved',
                'monthly_budgeted',
                'monthly_actual',
                'monthly_variance',
                'monthly_percent_variance',
                'qtd_budgeted',
                'qtd_actual',
                'qtd_variance',
                'qtd_percent_variance',
                'qtr_monthly_month_1_budgeted',
                'qtr_monthly_month_1_actual',
                'qtr_monthly_month_1_variance',
                'qtr_monthly_month_1_percent_variance',
                'qtr_monthly_month_2_budgeted',
                'qtr_monthly_month_2_actual',
                'qtr_monthly_month_2_variance',
                'qtr_monthly_month_2_percent_variance',
                'qtr_monthly_month_3_budgeted',
                'qtr_monthly_month_3_actual',
                'qtr_monthly_month_3_variance',
                'qtr_monthly_month_3_percent_variance',
                'qtr_qtd_month_1_actual',
                'qtr_qtd_month_1_budgeted',
                'qtr_qtd_month_1_variance',
                'qtr_qtd_month_1_percent_variance',
                'qtr_qtd_month_2_actual',
                'qtr_qtd_month_2_budgeted',
                'qtr_qtd_month_2_variance',
                'qtr_qtd_month_2_percent_variance',
                'qtr_qtd_month_3_actual',
                'qtr_qtd_month_3_budgeted',
                'qtr_qtd_month_3_variance',
                'qtr_qtd_month_3_percent_variance',
                'qtr_ytd_month_1_actual',
                'qtr_ytd_month_1_budgeted',
                'qtr_ytd_month_1_variance',
                'qtr_ytd_month_1_percent_variance',
                'qtr_ytd_month_2_actual',
                'qtr_ytd_month_2_budgeted',
                'qtr_ytd_month_2_variance',
                'qtr_ytd_month_2_percent_variance',
                'qtr_ytd_month_3_actual',
                'qtr_ytd_month_3_budgeted',
                'qtr_ytd_month_3_variance',
                'qtr_ytd_month_3_percent_variance',
                'forecast_budgeted',
                'forecast_actual',
                'forecast_variance',
                'forecast_percent_variance',
                'qtr_forecast_month_1_budgeted',
                'qtr_forecast_month_1_actual',
                'qtr_forecast_month_1_variance',
                'qtr_forecast_month_1_percent_variance',
                'qtr_forecast_month_2_budgeted',
                'qtr_forecast_month_2_actual',
                'qtr_forecast_month_2_variance',
                'qtr_forecast_month_2_percent_variance',
                'qtr_forecast_month_3_budgeted',
                'qtr_forecast_month_3_actual',
                'qtr_forecast_month_3_variance',
                'qtr_forecast_month_3_percent_variance',
                'ytd_budgeted',
                'ytd_actual',
                'ytd_variance',
                'ytd_percent_variance',
                'total_monthly_budgeted',
                'total_monthly_actual',
                'total_monthly_variance',
                'total_monthly_percent_variance',
                'total_qtd_budgeted',
                'total_qtd_actual',
                'total_qtd_variance',
                'total_qtd_percent_variance',
                'total_qtr_monthly_month_1_budgeted',
                'total_qtr_monthly_month_1_actual',
                'total_qtr_monthly_month_1_variance',
                'total_qtr_monthly_month_1_percent_variance',
                'total_qtr_monthly_month_2_budgeted',
                'total_qtr_monthly_month_2_actual',
                'total_qtr_monthly_month_2_variance',
                'total_qtr_monthly_month_2_percent_variance',
                'total_qtr_monthly_month_3_budgeted',
                'total_qtr_monthly_month_3_actual',
                'total_qtr_monthly_month_3_variance',
                'total_qtr_monthly_month_3_percent_variance',
                'total_qtr_qtd_month_1_actual',
                'total_qtr_qtd_month_1_budgeted',
                'total_qtr_qtd_month_1_variance',
                'total_qtr_qtd_month_1_percent_variance',
                'total_qtr_qtd_month_2_actual',
                'total_qtr_qtd_month_2_budgeted',
                'total_qtr_qtd_month_2_variance',
                'total_qtr_qtd_month_2_percent_variance',
                'total_qtr_qtd_month_3_actual',
                'total_qtr_qtd_month_3_budgeted',
                'total_qtr_qtd_month_3_variance',
                'total_qtr_qtd_month_3_percent_variance',
                'total_qtr_ytd_month_1_actual',
                'total_qtr_ytd_month_1_budgeted',
                'total_qtr_ytd_month_1_variance',
                'total_qtr_ytd_month_1_percent_variance',
                'total_qtr_ytd_month_2_actual',
                'total_qtr_ytd_month_2_budgeted',
                'total_qtr_ytd_month_2_variance',
                'total_qtr_ytd_month_2_percent_variance',
                'total_qtr_ytd_month_3_actual',
                'total_qtr_ytd_month_3_budgeted',
                'total_qtr_ytd_month_3_variance',
                'total_qtr_ytd_month_3_percent_variance',
                'total_forecast_budgeted',
                'total_forecast_actual',
                'total_forecast_variance',
                'total_forecast_percent_variance',
                'total_qtr_forecast_month_1_budgeted',
                'total_qtr_forecast_month_1_actual',
                'total_qtr_forecast_month_1_variance',
                'total_qtr_forecast_month_1_percent_variance',
                'total_qtr_forecast_month_2_budgeted',
                'total_qtr_forecast_month_2_actual',
                'total_qtr_forecast_month_2_variance',
                'total_qtr_forecast_month_2_percent_variance',
                'total_qtr_forecast_month_3_budgeted',
                'total_qtr_forecast_month_3_actual',
                'total_qtr_forecast_month_3_variance',
                'total_qtr_forecast_month_3_percent_variance',
                'total_ytd_budgeted',
                'total_ytd_actual',
                'total_ytd_variance',
                'total_ytd_percent_variance',
                'resolver_user_id',
                'resolved_date',
                'explanation_update_date',
                'explanation',
                'explainer_id',
                'advanced_variance_explanation_type_id',
                'explanation_type_date',
                'explanation_type_user_id',
                'sort_order',
                'is_summary',
                'is_summary_tab_default_line_item',

            ]
        );
        $this->setCasts(
            [

                'id'                                                               => 'integer',
                'advanced_variance_id'                                             => 'integer',
                'native_account_id'                                                => 'integer',
                'report_template_account_group_id'                                 => 'integer',
                'calculated_field_id'                                              => 'integer',
                'calculation_result_info'                                          => 'string',
                'calculation_name'                                                 => 'string',
                'calculation_description'                                          => 'string',
                'calculation_equation_string'                                      => 'string',
                'calculation_display_equation_string'                              => 'string',
                'native_account_overage_threshold_amount'                          => 'float',
                'native_account_overage_threshold_amount_too_good'                 => 'float',
                'native_account_overage_threshold_percent'                         => 'float',
                'native_account_overage_threshold_percent_too_good'                => 'float',
                'native_account_overage_threshold_operator'                        => 'string',
                'report_template_account_group_overage_threshold_amount'           => 'float',
                'report_template_account_group_overage_threshold_amount_too_good'  => 'float',
                'report_template_account_group_overage_threshold_percent'          => 'float',
                'report_template_account_group_overage_threshold_percent_too_good' => 'float',
                'report_template_account_group_overage_threshold_operator'         => 'string',
                'calculated_field_overage_threshold_amount'                        => 'float',
                'calculated_field_overage_threshold_amount_too_good'               => 'float',
                'calculated_field_overage_threshold_percent'                       => 'float',
                'calculated_field_overage_threshold_percent_too_good'              => 'float',
                'calculated_field_overage_threshold_operator'                      => 'string',
                'line_item_coefficient'                                            => 'float',
                'line_item_name'                                                   => 'string',
                'line_item_code'                                                   => 'string',
                'flagged_via_policy'                                               => 'boolean',
                'flagged_manually'                                                 => 'boolean',
                'flagged_manually_date'                                            => 'datetime',
                'flagger_user_id'                                                  => 'integer',
                'num_flagged'                                                      => 'integer',
                'num_flagged_via_policy'                                           => 'integer',
                'num_flagged_manually'                                             => 'integer',
                'num_explained'                                                    => 'integer',
                'num_resolved'                                                     => 'integer',
                'monthly_budgeted'                                                 => 'float',
                'monthly_actual'                                                   => 'float',
                'monthly_variance'                                                 => 'float',
                'monthly_percent_variance'                                         => 'float',
                'qtd_budgeted'                                                     => 'float',
                'qtd_actual'                                                       => 'float',
                'qtd_variance'                                                     => 'float',
                'qtd_percent_variance'                                             => 'float',
                'qtr_monthly_month_1_budgeted'                                     => 'float',
                'qtr_monthly_month_1_actual'                                       => 'float',
                'qtr_monthly_month_1_variance'                                     => 'float',
                'qtr_monthly_month_1_percent_variance'                             => 'float',
                'qtr_monthly_month_2_budgeted'                                     => 'float',
                'qtr_monthly_month_2_actual'                                       => 'float',
                'qtr_monthly_month_2_variance'                                     => 'float',
                'qtr_monthly_month_2_percent_variance'                             => 'float',
                'qtr_monthly_month_3_budgeted'                                     => 'float',
                'qtr_monthly_month_3_actual'                                       => 'float',
                'qtr_monthly_month_3_variance'                                     => 'float',
                'qtr_monthly_month_3_percent_variance'                             => 'float',
                'qtr_qtd_month_1_actual'                                           => 'float',
                'qtr_qtd_month_1_budgeted'                                         => 'float',
                'qtr_qtd_month_1_variance'                                         => 'float',
                'qtr_qtd_month_1_percent_variance'                                 => 'float',
                'qtr_qtd_month_2_actual'                                           => 'float',
                'qtr_qtd_month_2_budgeted'                                         => 'float',
                'qtr_qtd_month_2_variance'                                         => 'float',
                'qtr_qtd_month_2_percent_variance'                                 => 'float',
                'qtr_qtd_month_3_actual'                                           => 'float',
                'qtr_qtd_month_3_budgeted'                                         => 'float',
                'qtr_qtd_month_3_variance'                                         => 'float',
                'qtr_qtd_month_3_percent_variance'                                 => 'float',
                'qtr_ytd_month_1_actual'                                           => 'float',
                'qtr_ytd_month_1_budgeted'                                         => 'float',
                'qtr_ytd_month_1_variance'                                         => 'float',
                'qtr_ytd_month_1_percent_variance'                                 => 'float',
                'qtr_ytd_month_2_actual'                                           => 'float',
                'qtr_ytd_month_2_budgeted'                                         => 'float',
                'qtr_ytd_month_2_variance'                                         => 'float',
                'qtr_ytd_month_2_percent_variance'                                 => 'float',
                'qtr_ytd_month_3_actual'                                           => 'float',
                'qtr_ytd_month_3_budgeted'                                         => 'float',
                'qtr_ytd_month_3_variance'                                         => 'float',
                'qtr_ytd_month_3_percent_variance'                                 => 'float',
                'forecast_budgeted'                                                => 'float',
                'forecast_actual'                                                  => 'float',
                'forecast_variance'                                                => 'float',
                'forecast_percent_variance'                                        => 'float',
                'qtr_forecast_month_1_budgeted'                                    => 'float',
                'qtr_forecast_month_1_actual'                                      => 'float',
                'qtr_forecast_month_1_variance'                                    => 'float',
                'qtr_forecast_month_1_percent_variance'                            => 'float',
                'qtr_forecast_month_2_budgeted'                                    => 'float',
                'qtr_forecast_month_2_actual'                                      => 'float',
                'qtr_forecast_month_2_variance'                                    => 'float',
                'qtr_forecast_month_2_percent_variance'                            => 'float',
                'qtr_forecast_month_3_budgeted'                                    => 'float',
                'qtr_forecast_month_3_actual'                                      => 'float',
                'qtr_forecast_month_3_variance'                                    => 'float',
                'qtr_forecast_month_3_percent_variance'                            => 'float',
                'ytd_budgeted'                                                     => 'float',
                'ytd_actual'                                                       => 'float',
                'ytd_variance'                                                     => 'float',
                'ytd_percent_variance'                                             => 'float',
                'total_monthly_budgeted'                                           => 'float',
                'total_monthly_actual'                                             => 'float',
                'total_monthly_variance'                                           => 'float',
                'total_monthly_percent_variance'                                   => 'float',
                'total_qtd_budgeted'                                               => 'float',
                'total_qtd_actual'                                                 => 'float',
                'total_qtd_variance'                                               => 'float',
                'total_qtd_percent_variance'                                       => 'float',
                'total_qtr_monthly_month_1_budgeted'                               => 'float',
                'total_qtr_monthly_month_1_actual'                                 => 'float',
                'total_qtr_monthly_month_1_variance'                               => 'float',
                'total_qtr_monthly_month_1_percent_variance'                       => 'float',
                'total_qtr_monthly_month_2_budgeted'                               => 'float',
                'total_qtr_monthly_month_2_actual'                                 => 'float',
                'total_qtr_monthly_month_2_variance'                               => 'float',
                'total_qtr_monthly_month_2_percent_variance'                       => 'float',
                'total_qtr_monthly_month_3_budgeted'                               => 'float',
                'total_qtr_monthly_month_3_actual'                                 => 'float',
                'total_qtr_monthly_month_3_variance'                               => 'float',
                'total_qtr_monthly_month_3_percent_variance'                       => 'float',
                'total_qtr_qtd_month_1_actual'                                     => 'float',
                'total_qtr_qtd_month_1_budgeted'                                   => 'float',
                'total_qtr_qtd_month_1_variance'                                   => 'float',
                'total_qtr_qtd_month_1_percent_variance'                           => 'float',
                'total_qtr_qtd_month_2_actual'                                     => 'float',
                'total_qtr_qtd_month_2_budgeted'                                   => 'float',
                'total_qtr_qtd_month_2_variance'                                   => 'float',
                'total_qtr_qtd_month_2_percent_variance'                           => 'float',
                'total_qtr_qtd_month_3_actual'                                     => 'float',
                'total_qtr_qtd_month_3_budgeted'                                   => 'float',
                'total_qtr_qtd_month_3_variance'                                   => 'float',
                'total_qtr_qtd_month_3_percent_variance'                           => 'float',
                'total_qtr_ytd_month_1_actual'                                     => 'float',
                'total_qtr_ytd_month_1_budgeted'                                   => 'float',
                'total_qtr_ytd_month_1_variance'                                   => 'float',
                'total_qtr_ytd_month_1_percent_variance'                           => 'float',
                'total_qtr_ytd_month_2_actual'                                     => 'float',
                'total_qtr_ytd_month_2_budgeted'                                   => 'float',
                'total_qtr_ytd_month_2_variance'                                   => 'float',
                'total_qtr_ytd_month_2_percent_variance'                           => 'float',
                'total_qtr_ytd_month_3_actual'                                     => 'float',
                'total_qtr_ytd_month_3_budgeted'                                   => 'float',
                'total_qtr_ytd_month_3_variance'                                   => 'float',
                'total_qtr_ytd_month_3_percent_variance'                           => 'float',
                'total_forecast_budgeted'                                          => 'float',
                'total_forecast_actual'                                            => 'float',
                'total_forecast_variance'                                          => 'float',
                'total_forecast_percent_variance'                                  => 'float',
                'total_qtr_forecast_month_1_budgeted'                              => 'float',
                'total_qtr_forecast_month_1_actual'                                => 'float',
                'total_qtr_forecast_month_1_variance'                              => 'float',
                'total_qtr_forecast_month_1_percent_variance'                      => 'float',
                'total_qtr_forecast_month_2_budgeted'                              => 'float',
                'total_qtr_forecast_month_2_actual'                                => 'float',
                'total_qtr_forecast_month_2_variance'                              => 'float',
                'total_qtr_forecast_month_2_percent_variance'                      => 'float',
                'total_qtr_forecast_month_3_budgeted'                              => 'float',
                'total_qtr_forecast_month_3_actual'                                => 'float',
                'total_qtr_forecast_month_3_variance'                              => 'float',
                'total_qtr_forecast_month_3_percent_variance'                      => 'float',
                'total_ytd_budgeted'                                               => 'float',
                'total_ytd_actual'                                                 => 'float',
                'total_ytd_variance'                                               => 'float',
                'total_ytd_percent_variance'                                       => 'float',
                'resolver_user_id'                                                 => 'integer',
                'resolved_date'                                                    => 'datetime',
                'explanation_update_date'                                          => 'datetime',
                'explanation'                                                      => 'string',
                'explainer_id'                                                     => 'integer',
                'advanced_variance_explanation_type_id'                            => 'integer',
                'explanation_type_date'                                            => 'datetime',
                'explanation_type_user_id'                                         => 'integer',
                'sort_order'                                                       => 'integer',
                'is_summary'                                                       => 'boolean',
                'is_summary_tab_default_line_item'                                 => 'boolean',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function advancedVariance()
    {
        return $this->belongsTo(
            AdvancedVariance::class,
            'advanced_variance_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function advancedVarianceExplanationType()
    {
        return $this->belongsTo(
            AdvancedVarianceExplanationType::class,
            'advanced_variance_explanation_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function calculatedField()
    {
        return $this->belongsTo(
            CalculatedField::class,
            'calculated_field_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'explanation_type_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccount()
    {
        return $this->belongsTo(
            NativeAccount::class,
            'native_account_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function reportTemplateAccountGroup()
    {
        return $this->belongsTo(
            ReportTemplateAccountGroup::class,
            'report_template_account_group_id',
            'id'
        );
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(AdvancedVarianceLineItem::$baseRules, AdvancedVarianceLineItem::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * This is needed to get Audits to work
     *
     * @return string
     */
    public function getMorphClass()
    {
        return AdvancedVarianceLineItem::class;
    }

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * End Of Generated
     */
}
