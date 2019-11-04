<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\HasAttachment;
use DB;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class AdvancedVarianceLineItem
 * @package App\Waypoint\Models
 */
class AdvancedVarianceLineItem extends AdvancedVarianceLineItemModelBase implements AuditableContract
{
    use AuditableTrait;
    use CommentableTrait;
    use HasAttachment;

    const ADVANCEDVARIANCELINEITEM_STATUS_RESOLVED   = 'resolved';
    const ADVANCEDVARIANCELINEITEM_STATUS_UNRESOLVED = 'unresolved';
    public static $advancedvariancelineitem_status_arr = [
        self::ADVANCEDVARIANCELINEITEM_STATUS_RESOLVED,
        self::ADVANCEDVARIANCELINEITEM_STATUS_UNRESOLVED,
    ];

    /** @var []|null */
    private $native_account_id_arr = null;

    /** @var []|null */
    private $report_template_account_group_id_arr = null;

    /** @var  [] */
    private $CalculatedFieldsRevenue = ['Revenue', 'Net Operating Income', 'Net Income', 'Income', 'NOI', 'Net Income'];
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'advanced_variance_id',
        'report_template_account_group_overage_threshold_operator',
        'flagged_via_policy',
        'flagged_manually',
        'flagger_user_id',
        'flagger_user',
        'flagged_manually_date',
        'explanation',
        'advanced_variance_line_item_status',
        'resolver_user_id',
        'resolved_date',
        'explanation_update_date',
        'explainer_id',
        'report_template_account_group_id',
        'native_account_id',
        'advanced_variance_coefficient',
        'calculation_result_info',
        'advanced_variance_explanation_type_id',
        'explanation_type_date',
        'explanation_type_user_id',
    ];

    /** @var number|null */
    private $budgeted_amount_in_question = null;
    /** @var number|null */
    private $actual_amount_in_question = null;

    /**
     * AccessList constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

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

            "native_account_id"        => $this->native_account_id,
            "native_account_type_name" => $this->get_native_account_type_name() ?: null,
            "native_account_type_id"   => $this->get_native_account_type() ? $this->get_native_account_type()->id : null,

            "report_template_account_group_id" => $this->report_template_account_group_id,

            "calculated_field_id"                 => $this->calculated_field_id,
            "calculation_result_info"             => $this->calculation_result_info,
            "calculation_name"                    => $this->calculation_name,
            "calculation_description"             => $this->calculation_description,
            "calculation_equation_string"         => $this->calculation_equation_string,
            "calculation_display_equation_string" => $this->calculation_display_equation_string,

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
            "monthly_percent_variance" => $this->isMonthlyBudgetZeroAndActualNonZero() ? 0 : $this->monthly_percent_variance,

            "qtd_budgeted"         => $this->qtd_budgeted,
            "qtd_actual"           => $this->qtd_actual,
            "qtd_variance"         => $this->qtd_variance,
            "qtd_percent_variance" => $this->isQTDBudgetZeroAndActualNonZero() ? 0 : $this->qtd_percent_variance,

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
            "ytd_percent_variance" => $this->isYTDBudgetZeroAndActualNonZero() ? 0 : $this->ytd_percent_variance,

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

            "advanced_variance_coefficient" => $this->line_item_coefficient,
            "line_item_name"                => $this->line_item_name,
            "line_item_code"                => $this->line_item_code,

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return mixed
     * @throws GeneralException
     */
    protected function get_native_account_type_name()
    {
        if ($this->get_native_account_type())
        {
            return $this->get_native_account_type()->native_account_type_name;
        }
        return false;
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function get_advanced_variance_line_item_status()
    {
        if (
            (
                $this->flagged_via_policy ||
                $this->flagged_manually
            ) &&
            $this->resolver_user_id
        )
        {
            return AdvancedVarianceLineItem::ADVANCEDVARIANCELINEITEM_STATUS_RESOLVED;
        }
        else
        {
            return AdvancedVarianceLineItem::ADVANCEDVARIANCELINEITEM_STATUS_UNRESOLVED;
        }
    }

    /**
     * @return bool
     */
    protected function isMonthlyBudgetZeroAndActualNonZero()
    {
        return $this->monthly_budgeted == 0 && $this->monthly_actual != 0;
    }

    /**
     * @return bool
     */
    protected function isYTDBudgetZeroAndActualNonZero()
    {
        return $this->ytd_budgeted == 0 && $this->ytd_actual != 0;
    }

    /**
     * @return bool
     */
    protected function isQTDBudgetZeroAndActualNonZero()
    {
        return $this->qtd_budgeted == 0 && $this->qtd_actual != 0;
    }

    /**
     * @return null|string
     */
    public function getStatusText()
    {
        if ($this->flagged_via_policy)
        {
            return 'Exceeds Threshold';
        }
        elseif ($this->flagged_manually)
        {
            return 'User Marked';
        }
        return null;
    }

    private $local_overage_threshold_amount_in_question = null;
    private $local_overage_threshold_amount_in_question_too_good = null;
    private $local_overage_threshold_percent_in_question = null;
    private $local_overage_threshold_percent_in_question_too_good = null;
    private $local_overage_threshold_operator_in_question = null;

    private $local_flagged_via_policy = null;
    private $local_flagged_via_policy_1 = null;
    private $local_flagged_via_policy_2 = null;
    private $local_do_good_check;

    private $local_over_budget = null;
    private $local_under_budget = null;
    private $local_over_budget_percent = null;
    private $local_under_budget_percent = null;

    private $local_advanced_variance_coefficient;

    /**
     * @throws GeneralException
     */
    public function check_flagged_via_policy()
    {
        $this->initLocals();
        $this->setup_check_flagged_via_policy();

        /**
         * hmmmm, I suppose a customer could want local_overage_threshold_amount_in_question_too_good
         * or local_overage_threshold_percent_in_question_too_good as in "flag if item is too good by moer
         * than 'zero' dollars".
         *
         * These are nullable in DB.
         */
        if (
            $this->local_overage_threshold_amount_in_question_too_good ||
            $this->local_overage_threshold_percent_in_question_too_good)
        {
            $this->local_do_good_check = true;
        }

        if ($this->local_advanced_variance_coefficient == 1)
        {
            /**
             * this is an account type where too little is good, ie expences
             * this is an account type where too much is bad, ie expences
             */
            $this->local_over_budget = $this->actual_amount_in_question - $this->budgeted_amount_in_question;
            if ( ! $this->budgeted_amount_in_question == 0)
            {
                $this->local_over_budget_percent = ($this->local_over_budget / abs($this->budgeted_amount_in_question)) * 100;
            }
            if ($this->local_do_good_check)
            {
                $this->local_under_budget         = -$this->local_over_budget;
                $this->local_under_budget_percent = -$this->local_over_budget_percent;
            }

            /**
             * if $this->budgeted_amount_in_question == 0
             * we short circuit the testing of % here
             * since you can't have a percentage of zero
             */
            if ($this->budgeted_amount_in_question == 0)
            {
                if ($this->local_over_budget > $this->local_overage_threshold_amount_in_question)
                {
                    $this->local_flagged_via_policy = true;
                }
                if ($this->local_under_budget > $this->native_account_overage_threshold_amount_too_good)
                {
                    $this->local_flagged_via_policy = true;
                }
                /**
                 * we're done here so......
                 */
                if ((boolean) $this->flagged_via_policy !== (boolean) $this->local_flagged_via_policy)
                {
                    $this->flagged_via_policy = (boolean) $this->local_flagged_via_policy;
                    /**
                     * update db direct to avoid infinate loop
                     */
                    DB::update(
                        DB::raw(
                            '
                        UPDATE advanced_variance_line_items
                            SET flagged_via_policy = :FLAGGED_VIA_POLICY
                            WHERE id=:ADVANCED_VARIANCE_LINE_ITEM_ID'
                        ),
                        [
                            'FLAGGED_VIA_POLICY'             => $this->flagged_via_policy,
                            'ADVANCED_VARIANCE_LINE_ITEM_ID' => $this->id,
                        ]
                    );
                }
                return;
            }

            if ($this->local_overage_threshold_operator_in_question == AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_OR)
            {
                if (
                    $this->local_over_budget > $this->local_overage_threshold_amount_in_question ||
                    $this->local_over_budget_percent > $this->local_overage_threshold_percent_in_question
                )
                {
                    $this->local_flagged_via_policy = true;
                }

                if ($this->local_do_good_check)
                {
                    if (
                        $this->local_under_budget > $this->local_overage_threshold_amount_in_question_too_good ||
                        $this->local_under_budget_percent > $this->local_overage_threshold_percent_in_question_too_good
                    )
                    {
                        $this->local_flagged_via_policy = true;
                    }
                }
            }
            elseif ($this->local_overage_threshold_operator_in_question == AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND)
            {
                if (
                    $this->local_over_budget > $this->local_overage_threshold_amount_in_question &&
                    $this->local_over_budget_percent > $this->local_overage_threshold_percent_in_question
                )
                {
                    $this->local_flagged_via_policy = true;
                }

                if ($this->local_do_good_check)
                {
                    if (
                        $this->local_under_budget > $this->local_overage_threshold_amount_in_question_too_good &&
                        $this->local_under_budget_percent > $this->local_overage_threshold_percent_in_question_too_good
                    )
                    {
                        $this->local_flagged_via_policy = true;
                    }
                }
                /**
                 * we're done here so......
                 */
                if ((boolean) $this->flagged_via_policy !== (boolean) $this->local_flagged_via_policy)
                {
                    $this->flagged_via_policy = (boolean) $this->local_flagged_via_policy;
                    /**
                     * update db direct to avoid infinate loop
                     */
                    DB::update(
                        DB::raw(
                            '
                        UPDATE advanced_variance_line_items
                            SET flagged_via_policy = :FLAGGED_VIA_POLICY
                            WHERE id=:ADVANCED_VARIANCE_LINE_ITEM_ID'
                        ),
                        [
                            'FLAGGED_VIA_POLICY'             => $this->flagged_via_policy,
                            'ADVANCED_VARIANCE_LINE_ITEM_ID' => $this->id,
                        ]
                    );
                }
                return;
            }
            else
            {
                throw new GeneralException('Invalid THRESHOLD_OPERATOR' . __FILE__ . ':' . __LINE__);
            }
        }
        else
        {
            /**
             * this is an account type where too little is bad, ie revenue
             * this is an account type where too much is good, ie revenue
             */
            $this->local_under_budget = $this->budgeted_amount_in_question - $this->actual_amount_in_question;
            if ( ! $this->budgeted_amount_in_question == 0)
            {
                $this->local_under_budget_percent = ($this->local_under_budget) / abs($this->budgeted_amount_in_question) * 100;
            }
            if ($this->local_do_good_check)
            {
                $this->local_over_budget = -$this->local_under_budget;
                if ( ! $this->budgeted_amount_in_question == 0)
                {
                    $this->local_over_budget_percent = -$this->local_under_budget_percent;
                }
            }

            /**
             * if $this->budgeted_amount_in_question == 0
             * we short circuit the testing of % here
             * since you can't have a percentage of zero
             */
            if ($this->budgeted_amount_in_question == 0)
            {
                if ($this->local_under_budget > $this->local_overage_threshold_amount_in_question)
                {
                    $this->local_flagged_via_policy = true;
                }
                if ($this->local_do_good_check)
                {
                    if ($this->local_over_budget > $this->native_account_overage_threshold_amount_too_good)
                    {
                        $this->local_flagged_via_policy = true;
                    }
                }
                /**
                 * we're done here so......
                 */
                if ((boolean) $this->flagged_via_policy !== (boolean) $this->local_flagged_via_policy)
                {
                    $this->flagged_via_policy = (boolean) $this->local_flagged_via_policy;
                    /**
                     * update db direct to avoid infinate loop
                     */
                    DB::update(
                        DB::raw(
                            '
                        UPDATE advanced_variance_line_items
                            SET flagged_via_policy = :FLAGGED_VIA_POLICY
                            WHERE id=:ADVANCED_VARIANCE_LINE_ITEM_ID'
                        ),
                        [
                            'FLAGGED_VIA_POLICY'             => $this->flagged_via_policy,
                            'ADVANCED_VARIANCE_LINE_ITEM_ID' => $this->id,
                        ]
                    );
                }
                return;
            }

            if ($this->local_overage_threshold_operator_in_question == AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_OR)
            {
                if (
                    $this->local_under_budget > $this->local_overage_threshold_amount_in_question ||
                    $this->local_under_budget_percent > $this->local_overage_threshold_percent_in_question
                )
                {
                    $this->local_flagged_via_policy = true;
                }

                if ($this->local_do_good_check)
                {
                    if (
                        $this->local_over_budget > $this->local_overage_threshold_amount_in_question_too_good ||
                        $this->local_over_budget_percent > $this->local_overage_threshold_percent_in_question_too_good
                    )
                    {
                        $this->local_flagged_via_policy = true;
                    }
                }
            }
            elseif ($this->local_overage_threshold_operator_in_question == AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND)
            {
                if (
                    $this->local_under_budget > $this->local_overage_threshold_amount_in_question &&
                    $this->local_under_budget_percent > $this->local_overage_threshold_percent_in_question
                )
                {
                    $this->local_flagged_via_policy = true;
                }

                if ($this->local_do_good_check)
                {
                    if (
                        $this->local_over_budget > $this->local_overage_threshold_amount_in_question_too_good &&
                        $this->local_over_budget_percent > $this->local_overage_threshold_percent_in_question_too_good
                    )
                    {
                        $this->local_flagged_via_policy = true;
                    }
                }
            }
            else
            {
                throw new GeneralException('Invalid THRESHOLD_OPERATOR' . __FILE__ . ':' . __LINE__);
            }
        }
        /**
         * now flip flagged_via_policy if needed
         */
        if ((boolean) $this->flagged_via_policy !== (boolean) $this->local_flagged_via_policy)
        {
            $this->flagged_via_policy = (boolean) $this->local_flagged_via_policy;
            /**
             * update db direct to avoid infinate loop
             */
            DB::update(
                DB::raw(
                    '
                        UPDATE advanced_variance_line_items
                            SET flagged_via_policy = :FLAGGED_VIA_POLICY
                            WHERE id=:ADVANCED_VARIANCE_LINE_ITEM_ID'
                ),
                [
                    'FLAGGED_VIA_POLICY'             => $this->flagged_via_policy,
                    'ADVANCED_VARIANCE_LINE_ITEM_ID' => $this->id,
                ]
            );
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function flaggerUser()
    {
        return $this->belongsTo(
            User::class,
            'flagger_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function resolverUser()
    {
        return $this->belongsTo(
            User::class,
            'resolver_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function explainerUser()
    {
        return $this->belongsTo(
            User::class,
            'explainer_id',
            'id'
        );
    }

    /**
     * @return array|mixed
     */
    public function get_native_account_id_arr()
    {
        if ( ! $this->reportTemplateAccountGroup && ! $this->nativeAccount)
        {
            return [];
        }

        if ($this->native_account_id_arr === null)
        {
            $this->native_account_id_arr = [];
            /**
             * if this points at a native account
             */
            if ($this->nativeAccount)
            {
                $this->native_account_id_arr[] = $this->native_account_id;
                return $this->native_account_id_arr;
            }

            /**
             * if this points at a reportTemplateAccountGroup
             */
            $this->native_account_id_arr = $this->reportTemplateAccountGroup->get_native_account_id_arr();
        }
        return $this->native_account_id_arr;
    }

    /**
     * @return array|mixed
     */
    public function get_report_template_account_group_id_arr()
    {
        if ( ! $this->reportTemplateAccountGroup && ! $this->nativeAccount)
        {
            throw new GeneralException('Unable to find reportTemplateAccountGroup or nativeAccount in AdvancedVarianceLineItem' . ' ' . __FILE__ . ':' . __LINE__);
        }
        /**
         * if this points at a native account
         */
        if ($this->nativeAccount)
        {
            return [];
        }

        /**
         * if this points at a reportTemplateAccountGroup
         */
        if ($this->report_template_account_group_id_arr === null)
        {
            $this->report_template_account_group_id_arr[] = $this->reportTemplateAccountGroup->id;
            /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupChildObj */
            foreach ($this->reportTemplateAccountGroup->reportTemplateAccountGroupChildren as $ReportTemplateAccountGroupChildObj)
            {
                $this->report_template_account_group_id_arr = array_merge(
                    $this->report_template_account_group_id_arr,
                    $ReportTemplateAccountGroupChildObj->get_report_template_account_group_id_arr()
                );
            }
        }
        return $this->report_template_account_group_id_arr;
    }

    /**
     * @param string $property_name
     * @param App\Waypoint\Collection $AdvancedVarianceLineItemsObjArr
     * @return mixed
     * @throws GeneralException
     *
     * NOTE that by passing in the sibling AdvancedVarianceLineItems, we save lots of long DB hits
     */
    public function roll_up($property_name, $AdvancedVarianceLineItemsObjArr)
    {
        if ($property_name == 'num_flagged')
        {
            return $AdvancedVarianceLineItemsObjArr
                       ->whereIn(
                           'native_account_id',
                           $this->get_native_account_id_arr()
                       )
                       ->filter(
                           function ($value)
                           {
                               return $value->num_flagged_via_policy || $value->num_flagged_manually;
                           }
                       )
                       ->count()

                   +

                   $AdvancedVarianceLineItemsObjArr
                       ->whereIn(
                           'report_template_account_group_id',
                           $this->get_report_template_account_group_id_arr()
                       )
                       ->filter(
                           function ($value)
                           {
                               return $value->num_flagged_via_policy || $value->num_flagged_manually;
                           }
                       )
                       ->count();
        }
        if ($property_name == 'num_resolved')
        {
            return $AdvancedVarianceLineItemsObjArr
                       ->whereIn(
                           'native_account_id',
                           $this->get_native_account_id_arr()
                       )
                       ->filter(
                           function ($value)
                           {
                               return $value->resolver_user_id;
                           }
                       )
                       ->count()

                   +

                   $AdvancedVarianceLineItemsObjArr
                       ->whereIn(
                           'report_template_account_group_id',
                           $this->get_report_template_account_group_id_arr()
                       )
                       ->filter(
                           function ($value)
                           {
                               return $value->resolver_user_id;
                           }
                       )
                       ->count();
        }

        if (
            self::getCastType($property_name) == 'float' ||
            self::getCastType($property_name) == 'integer'
        )
        {
            return
                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'native_account_id',
                        $this->get_native_account_id_arr()
                    )
                    ->sum($property_name)

                +

                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'report_template_account_group_id',
                        $this->get_report_template_account_group_id_arr()
                    )
                    ->sum($property_name);
        }
        elseif (
            self::getCastType($property_name) == 'boolean'
        )
        {
            return
                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'native_account_id',
                        $this->get_native_account_id_arr()
                    )
                    ->where(
                        $property_name,
                        true
                    )
                    ->count()

                +

                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'report_template_account_group_id',
                        $this->get_report_template_account_group_id_arr()
                    )
                    ->where(
                        $property_name,
                        true
                    )
                    ->count();
        }
        elseif (
            self::getCastType($property_name) == 'string'
        )
        {
            return
                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'native_account_id',
                        $this->get_native_account_id_arr()
                    )
                    ->filter(
                        function ($item) use ($property_name)
                        {
                            return $item->$property_name != null;
                        }
                    )
                    ->count()

                +

                $AdvancedVarianceLineItemsObjArr
                    ->whereIn(
                        'report_template_account_group_id',
                        $this->get_report_template_account_group_id_arr()
                    )
                    ->filter(
                        function ($item) use ($property_name)
                        {
                            return $item->$property_name != null;
                        }
                    )
                    ->count();
        }
        else
        {
            throw new GeneralException('Invalid cast encountered' . ' ' . __FILE__ . ':' . __LINE__);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function advancedVarianceSkinny()
    {
        return $this->belongsTo(
            AdvancedVarianceSkinny::class,
            'advanced_variance_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function reportTemplateAccountGroupSummary()
    {
        return $this->belongsTo(
            ReportTemplateAccountGroupSummary::class,
            'report_template_account_group_id',
            'id'
        );
    }

    /**
     * @return mixed|NativeAccountType
     */
    public function get_native_account_type()
    {
        if ($this->native_account_id)
        {
            return $this->nativeAccount->nativeAccountType;
        }
        elseif ($this->report_template_account_group_id)
        {
            return $this->reportTemplateAccountGroup->nativeAccountType;
        }
        return null;
    }

    public function set_amounts_in_question()
    {
        $this->budgeted_amount_in_question = null;
        $this->actual_amount_in_question   = null;
        if ($this->native_account_id)
        {
            if ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_MONTHLY)
            {
                $this->budgeted_amount_in_question = $this->monthly_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->monthly_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_YTD)
            {
                $this->budgeted_amount_in_question = $this->ytd_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->ytd_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_QTD)
            {
                $this->budgeted_amount_in_question = $this->qtd_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->qtd_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_FORECAST)
            {
                $this->budgeted_amount_in_question = $this->forecast_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->forecast_actual ?: 0;
            }
        }
        elseif ($this->report_template_account_group_id || $this->calculated_field_id)
        {
            if ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_MONTHLY)
            {
                $this->budgeted_amount_in_question = $this->total_monthly_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->total_monthly_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_YTD)
            {
                $this->budgeted_amount_in_question = $this->total_ytd_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->total_ytd_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_QTD)
            {
                $this->budgeted_amount_in_question = $this->total_qtd_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->total_qtd_actual ?: 0;
            }
            elseif ($this->advancedVariance->trigger_mode == AdvancedVariance::TRIGGER_MODE_FORECAST)
            {
                $this->budgeted_amount_in_question = $this->total_forecast_budgeted ?: 0;
                $this->actual_amount_in_question   = $this->total_forecast_actual ?: 0;
            }
        }
        if ($this->budgeted_amount_in_question === null || $this->actual_amount_in_question === null)
        {
            throw new GeneralException('unable to set budgeted_amount_in_question or actual_amount_in_question');
        }
    }

    private function initLocals()
    {
        $this->local_overage_threshold_amount_in_question           = null;
        $this->local_overage_threshold_amount_in_question_too_good  = null;
        $this->local_overage_threshold_percent_in_question          = null;
        $this->local_overage_threshold_percent_in_question_too_good = null;
        $this->local_overage_threshold_operator_in_question         = null;

        $this->local_flagged_via_policy   = null;
        $this->local_flagged_via_policy_1 = null;
        $this->local_flagged_via_policy_2 = null;
        $this->local_do_good_check        = null;

        $this->local_over_budget          = null;
        $this->local_under_budget         = null;
        $this->local_over_budget_percent  = null;
        $this->local_under_budget_percent = null;
    }

    private function setup_check_flagged_via_policy()
    {
        /**
         * depending on whether this AdvancedVarianceLineItem refs a nativeAccount or a reportTemplateAccountGroup,
         * select the proper $this->budgeted_amount_in_question and $this->actual_amount_in_question
         * values and proper $NativeAccountTypeTrailerObj
         */
        if (
            $this->native_account_id &&
            (
                $this->advancedVariance->threshold_mode == AdvancedVariance::THRESHOLD_MODE_NATIVE_ACCOUNT ||
                $this->advancedVariance->threshold_mode == AdvancedVariance::THRESHOLD_MODE_BOTH
            )
        )
        {
            $NativeAccountTypeTrailerObj = $this->nativeAccount->getCoeffients($this->advancedVariance->property_id);
            /**
             * @todo WTF - why not $this->line_item_coefficient
             */
            $this->local_advanced_variance_coefficient = $NativeAccountTypeTrailerObj->advanced_variance_coefficient;
            /**
             * the $this->native_account_id_arr is really only germane for AdvancedVarianceLineItem's that
             * refer to a reportTemplateAccountGroup. We populate this only to be consistent
             */
            $this->native_account_id_arr                                = [$this->native_account_id];
            $this->local_overage_threshold_amount_in_question           = $this->native_account_overage_threshold_amount;
            $this->local_overage_threshold_amount_in_question_too_good  = $this->native_account_overage_threshold_amount_too_good;
            $this->local_overage_threshold_percent_in_question          = $this->native_account_overage_threshold_percent;
            $this->local_overage_threshold_percent_in_question_too_good = $this->native_account_overage_threshold_percent_too_good;
            $this->local_overage_threshold_operator_in_question         = $this->native_account_overage_threshold_operator;

            $this->set_amounts_in_question();
        }
        elseif (
            $this->report_template_account_group_id &&
            (
                $this->advancedVariance->threshold_mode == AdvancedVariance::THRESHOLD_MODE_REPORT_TEMPLATE_ACCOUNT_GROUP ||
                $this->advancedVariance->threshold_mode == AdvancedVariance::THRESHOLD_MODE_BOTH
            )
        )
        {
            $NativeAccountTypeTrailerObj = $this->reportTemplateAccountGroup->nativeAccountType->getCoeffients($this->advancedVariance->property_id);
            /**
             * @todo WTF - why not $this->line_item_coefficient
             */
            $this->local_advanced_variance_coefficient                  = $NativeAccountTypeTrailerObj->advanced_variance_coefficient;
            $this->local_overage_threshold_amount_in_question           = $this->report_template_account_group_overage_threshold_amount;
            $this->local_overage_threshold_amount_in_question_too_good  = $this->report_template_account_group_overage_threshold_amount_too_good;
            $this->local_overage_threshold_percent_in_question          = $this->report_template_account_group_overage_threshold_percent;
            $this->local_overage_threshold_percent_in_question_too_good = $this->report_template_account_group_overage_threshold_percent_too_good;
            $this->local_overage_threshold_operator_in_question         = $this->report_template_account_group_overage_threshold_operator;

            $this->set_amounts_in_question();
        }
        elseif ($this->calculated_field_id)
        {
            $this->local_advanced_variance_coefficient = 1;
            foreach ($this->CalculatedFieldsRevenue as $name)
            {
                if ($this->line_item_name === $name)
                {
                    $this->local_advanced_variance_coefficient = -1;
                    break;
                }
            }

            $this->set_amounts_in_question();

            $this->local_overage_threshold_amount_in_question           = $this->calculated_field_overage_threshold_amount;
            $this->local_overage_threshold_amount_in_question_too_good  = $this->calculated_field_overage_threshold_amount_too_good;
            $this->local_overage_threshold_percent_in_question          = $this->calculated_field_overage_threshold_percent;
            $this->local_overage_threshold_percent_in_question_too_good = $this->calculated_field_overage_threshold_percent_too_good;
            $this->local_overage_threshold_operator_in_question         = $this->calculated_field_overage_threshold_operator;
        }
        else
        {
            /**
             * covers edge case where advanced_variance_line_item was flagged_via_policy but later
             * client conf changed.
             *
             * also covers calculated fields
             */
            if (false !== (boolean) $this->local_flagged_via_policy)
            {
                $this->flagged_via_policy = false;

                /**
                 * update db direct to avoid infinate loop
                 */
                DB::update(
                    DB::raw(
                        '
                        UPDATE advanced_variance_line_items
                            SET flagged_via_policy = :FLAGGED_VIA_POLICY
                            WHERE id=:ADVANCED_VARIANCE_LINE_ITEM_ID'
                    ),
                    [
                        'FLAGGED_VIA_POLICY'             => $this->flagged_via_policy,
                        'ADVANCED_VARIANCE_LINE_ITEM_ID' => $this->id,
                    ]
                );
            }
        }
    }
}
