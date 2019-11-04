<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;

use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldVariable;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Repositories\AdvancedVarianceFullRepository;
use App\Waypoint\Repositories\ReportTemplateFullRepository;
use App\Waypoint\S3Trait;
use DB;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Exceptions\GeneralException;
use Uuid;
use \FormulaInterpreter\Compiler as FormulaInterpreterCompiler;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceLineItemRefreshJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    use S3Trait;

    /** @var  [] */
    private $advanced_variance_line_item_arr;

    private $properties_to_calculate = [
        "monthly_budgeted",
        "monthly_actual",

        "ytd_budgeted",
        "ytd_actual",

        "qtd_budgeted",
        "qtd_actual",

        "qtr_monthly_month_1_budgeted",
        "qtr_monthly_month_1_actual",

        "qtr_monthly_month_2_budgeted",
        "qtr_monthly_month_2_actual",

        "qtr_monthly_month_3_budgeted",
        "qtr_monthly_month_3_actual",

        "qtr_qtd_month_1_budgeted",
        "qtr_qtd_month_1_actual",

        "qtr_qtd_month_2_budgeted",
        "qtr_qtd_month_2_actual",

        "qtr_qtd_month_3_budgeted",
        "qtr_qtd_month_3_actual",

        "qtr_forecast_month_1_budgeted",
        "qtr_forecast_month_1_actual",

        "qtr_forecast_month_2_budgeted",
        "qtr_forecast_month_2_actual",

        "qtr_forecast_month_3_budgeted",
        "qtr_forecast_month_3_actual",

        "qtr_ytd_month_1_budgeted",
        "qtr_ytd_month_1_actual",

        "qtr_ytd_month_2_budgeted",
        "qtr_ytd_month_2_actual",

        "qtr_ytd_month_3_budgeted",
        "qtr_ytd_month_3_actual",

        "forecast_budgeted",
        "forecast_actual",
    ];

    /** @var  [] */
    private $CalculatedFieldsRevenue = ['Revenue', 'Net Operating Income', 'Net Income', 'Income', 'NOI', 'Net Income'];

    /** @var  AdvancedVariance */
    private $AdvancedVarianceObj;

    /**
     * AdvancedVarianceLineItemRefreshJob constructor.
     * @param $advanced_variance_line_item_arr
     */
    public function __construct($advanced_variance_line_item_arr)
    {
        $this->advanced_variance_line_item_arr = $advanced_variance_line_item_arr;
    }

    /**
     * @throws JobException
     * @todo break this up
     */
    public function handle()
    {
        try
        {
            /**
             * @todo Push this into a repository
             */
            /** @var AdvancedVarianceRepository $AdvancedVarianceRepositoryObj */
            $AdvancedVarianceRepositoryObj = App::make(AdvancedVarianceRepository::class)->setSuppressEvents(true);

            if ( ! $this->AdvancedVarianceObj = $AdvancedVarianceRepositoryObj
                ->with('advancedVarianceLineItems.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems.nativeAccount.reportTemplateMappings.reportTemplateAccountGroup')
                ->with('advancedVarianceLineItems.reportTemplateAccountGroup.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems.reportTemplateAccountGroup.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems')
                ->with('advancedVarianceLineItems.calculatedField.calculatedFieldEquations')
                ->with('advancedVarianceApprovals')
                ->with('property')
                ->with('lockerUser')
                ->findWithoutFail(
                    $this->advanced_variance_line_item_arr['advanced_variance_id']
                )
            )
            {
                throw new GeneralException(
                    'Failed to find advanced_variance -' . print_r($this->advanced_variance_line_item_arr, 1) .
                    ' in ' . __CLASS__ . ' ' . __FILE__ . ':' . __LINE__);
            }

            /**
             * if we save off locker_user_id and null it
             * which effectively unlocks the report. We hit DB direct to avoid
             * other events.
             */
            $locker_user_id = null;
            if ($this->AdvancedVarianceObj->locked())
            {
                $locker_user_id = $this->AdvancedVarianceObj->locker_user_id;
                DB::update(
                    DB::raw(
                        "
                        UPDATE advanced_variances SET
                            locker_user_id = null
                            WHERE id = :ADVANCED_VARIANCE_ID
                    "
                    ),
                    [
                        'ADVANCED_VARIANCE_ID' => $this->advanced_variance_line_item_arr['advanced_variance_id'],
                    ]
                );
                $this->AdvancedVarianceObj->refresh();
            }

            $this->process_advanced_variance();
            $this->AdvancedVarianceObj = $AdvancedVarianceRepositoryObj->get_hydrated_advanced_variance($this->AdvancedVarianceObj->id);

            /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
            foreach ($this->AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
            {
                if ($AdvancedVarianceLineItemObj->report_template_account_group_id !== null)
                {
                    $this->roll_up_advanced_variance_line_item_report_template_account_group($AdvancedVarianceLineItemObj);
                }

                if ($AdvancedVarianceLineItemObj->native_account_id !== null)
                {
                    $this->process_advanced_variance_line_item_native_account($AdvancedVarianceLineItemObj);
                }

                if ($AdvancedVarianceLineItemObj->calculated_field_id !== null)
                {
                    /**
                     * this just updates fields that change due to name or sort changes
                     */
                    $this->process_advanced_variance_line_item_calculated_field($AdvancedVarianceLineItemObj);
                }
            }

            $this->AdvancedVarianceObj = $AdvancedVarianceRepositoryObj->get_hydrated_advanced_variance($this->AdvancedVarianceObj->id);

            if ($this->AdvancedVarianceObj->period_type == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
            {
                /**
                 * get last month's Advanced Variance report for this property, report_template/as_of_year
                 */
                if ($this->AdvancedVarianceObj->as_of_month == 3)
                {
                    $LastMonthAdvancedVarianceObj = null;
                }
                else
                {
                    $LastMonthAdvancedVarianceObj = $AdvancedVarianceRepositoryObj->findWhere(
                        [
                            'report_template_id' => $this->AdvancedVarianceObj->report_template_id,
                            'as_of_year'         => $this->AdvancedVarianceObj->as_of_year,
                            'period_type'        => AdvancedVariance::PERIOD_TYPE_QUARTERLY,
                            ['as_of_month', '<', $this->AdvancedVarianceObj->as_of_month],
                        ]
                    )->sortByDesc('as_of_month')->first();
                }

                foreach ($this->AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
                {
                    if ($AdvancedVarianceLineItemObj->native_account_id !== null)
                    {
                        $this->process_advanced_variance_line_item_native_account_quarterly($AdvancedVarianceLineItemObj, $LastMonthAdvancedVarianceObj);
                    }
                }

                /**
                 * now that the native account values have been set, roll them up
                 */
                foreach ($this->AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
                {
                    if ($AdvancedVarianceLineItemObj->report_template_account_group_id !== null)
                    {
                        $this->roll_up_advanced_variance_line_item_report_template_account_group_quarterly($AdvancedVarianceLineItemObj);
                    }
                }
            }

            $this->AdvancedVarianceObj = $AdvancedVarianceRepositoryObj->get_hydrated_advanced_variance($this->AdvancedVarianceObj->id);

            foreach ($this->AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
            {
                if ($AdvancedVarianceLineItemObj->calculated_field_id !== null)
                {
                    /**
                     * do the calculation. this needs to be last
                     */
                    $this->process_calculated_fields($AdvancedVarianceLineItemObj);
                }
            }

            /**
             * put back locker_user_id
             * see above
             */
            if ($locker_user_id)
            {
                DB::update(
                    DB::raw(
                        "
                        UPDATE advanced_variances SET
                            locker_user_id = :LOCKER_USER_ID
                            WHERE id = :ADVANCED_VARIANCE_ID
                    "
                    ),
                    [
                        'LOCKER_USER_ID'       => $locker_user_id,
                        'ADVANCED_VARIANCE_ID' => $this->AdvancedVarianceObj->id,
                    ]
                );
            }

            $this->AdvancedVarianceObj->refresh();
            $this->dump_advanced_variance_to_s3();
        }
        catch (GeneralException $e)
        {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            throw  $e;
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            throw new GeneralException($e->getMessage() . __CLASS__, 404, $e);
        }
    }

    /**
     * @throws App\Waypoint\Exceptions\ValidationException
     */
    private function process_advanced_variance()
    {
        $this->AdvancedVarianceObj->num_flagged = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->filter(
                function ($value, $key)
                {
                    return $value->flagged_via_policy || $value->flagged_manually;
                }
            )
            ->count();

        $this->AdvancedVarianceObj->num_flagged_via_policy = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->where('flagged_via_policy', 1)
            ->count();

        $this->AdvancedVarianceObj->num_flagged_manually = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->where('flagged_manually', 1)
            ->count();

        $this->AdvancedVarianceObj->num_explained = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->whereNotIn('explanation_update_date', [null])
            ->count();

        $this->AdvancedVarianceObj->num_line_items = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->count();

        $this->AdvancedVarianceObj->num_resolved = $this->AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->filter(
                function (AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
                {
                    return $AdvancedVarianceLineItemObj->resolver_user_id &&
                           (
                               $AdvancedVarianceLineItemObj->flagged_via_policy ||
                               $AdvancedVarianceLineItemObj->flagged_manually
                           );
                }
            )
            ->count();

        $this->AdvancedVarianceObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     *
     * @todo - break this up
     */
    private function roll_up_advanced_variance_line_item_report_template_account_group(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        /** monthly */
        $AdvancedVarianceLineItemObj->total_monthly_budgeted = $AdvancedVarianceLineItemObj->roll_up(
            'monthly_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems);
        $AdvancedVarianceLineItemObj->total_monthly_actual   = $AdvancedVarianceLineItemObj->roll_up(
            'monthly_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_monthly_variance = $AdvancedVarianceLineItemObj->roll_up(
            'monthly_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_monthly');

        /** YTD */
        $AdvancedVarianceLineItemObj->total_ytd_budgeted = $AdvancedVarianceLineItemObj->roll_up(
            'ytd_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_ytd_actual   = $AdvancedVarianceLineItemObj->roll_up(
            'ytd_actual', $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_ytd_variance = $AdvancedVarianceLineItemObj->roll_up(
            'ytd_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_ytd');

        /** QTD */
        $AdvancedVarianceLineItemObj->total_qtd_budgeted = $AdvancedVarianceLineItemObj->roll_up(
            'qtd_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtd_actual   = $AdvancedVarianceLineItemObj->roll_up(
            'qtd_actual', $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtd_variance = $AdvancedVarianceLineItemObj->roll_up(
            'qtd_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtd');

        /** forecast */
        $AdvancedVarianceLineItemObj->total_forecast_budgeted = $AdvancedVarianceLineItemObj->roll_up(
            'forecast_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_forecast_actual   = $AdvancedVarianceLineItemObj->roll_up(
            'forecast_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_forecast_variance = $AdvancedVarianceLineItemObj->roll_up(
            'forecast_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_forecast');

        /** QTR month detail*/
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_1_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_1_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_1_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_1_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_1_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_1_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_1');

        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_2_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_2_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_2_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_2_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_2_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_2_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_2');

        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_3_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_3_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_3_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_3_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_monthly_month_3_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_monthly_month_3_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_3');

        /** forecast month detail*/
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_1_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_1_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_1_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_1_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_1_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_1_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_1');

        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_2_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_2_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_2_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_2_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_2_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_2_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_2');

        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_3_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_3_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_3_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_3_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_forecast_month_3_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_forecast_month_3_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_3');

        /** various flags */
        $AdvancedVarianceLineItemObj->num_flagged = $AdvancedVarianceLineItemObj->roll_up(
            'num_flagged',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->num_flagged_via_policy = $AdvancedVarianceLineItemObj->roll_up(
            'flagged_via_policy',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->num_flagged_manually   = $AdvancedVarianceLineItemObj->roll_up(
            'flagged_manually',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->num_explained          = $AdvancedVarianceLineItemObj->roll_up(
            'explanation',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->num_resolved           = $AdvancedVarianceLineItemObj->roll_up(
            'num_resolved',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );

        $AdvancedVarianceLineItemObj->line_item_coefficient
            = $AdvancedVarianceLineItemObj->reportTemplateAccountGroup
            ->nativeAccountType
            ->getCoeffients(
                $this->AdvancedVarianceObj->property_id
            )->advanced_variance_coefficient;
        $AdvancedVarianceLineItemObj->line_item_name
            = $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_name;
        $AdvancedVarianceLineItemObj->line_item_code
            = $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_code;

        $AdvancedVarianceLineItemObj->sort_order = $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->sort_order;
        $AdvancedVarianceLineItemObj->is_summary = $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->is_summary;

        $AdvancedVarianceLineItemObj->check_flagged_via_policy();

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @param $line_item_key
     */
    private function set_variance(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $line_item_key)
    {
        $budgeted         = $line_item_key . '_budgeted';
        $actual           = $line_item_key . '_actual';
        $variance         = $line_item_key . '_variance';
        $percent_variance = $line_item_key . '_percent_variance';

        $AdvancedVarianceLineItemObj->$variance = $AdvancedVarianceLineItemObj->$actual - $AdvancedVarianceLineItemObj->$budgeted;

        if ($AdvancedVarianceLineItemObj->$budgeted == 0)
        {
            $AdvancedVarianceLineItemObj->$percent_variance = 0;
        }
        else
        {
            $AdvancedVarianceLineItemObj->$percent_variance = 100 * $AdvancedVarianceLineItemObj->$variance / $AdvancedVarianceLineItemObj->$budgeted;
        }
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @throws App\Waypoint\Exceptions\ValidationException
     */
    private function process_advanced_variance_line_item_native_account(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        $AdvancedVarianceLineItemObj->line_item_coefficient
            = $AdvancedVarianceLineItemObj->nativeAccount->getCoeffients($this->AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $AdvancedVarianceLineItemObj->line_item_name
            = $AdvancedVarianceLineItemObj->nativeAccount->native_account_name;
        $AdvancedVarianceLineItemObj->line_item_code
            = $AdvancedVarianceLineItemObj->nativeAccount->native_account_code;

        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
        $AdvancedVarianceObj      = $this->AdvancedVarianceObj;
        $ReportTemplateMappingObj = $AdvancedVarianceLineItemObj
            ->nativeAccount
            ->reportTemplateMappings->filter(
                function ($ReportTemplateMappingObj) use ($AdvancedVarianceObj)
                {
                    /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == $AdvancedVarianceObj->report_template_id;
                }
            )->first();

        $AdvancedVarianceLineItemObj->sort_order = $ReportTemplateMappingObj->sort_order;
        $AdvancedVarianceLineItemObj->is_summary = $ReportTemplateMappingObj->is_summary;

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @throws App\Waypoint\Exceptions\ValidationException
     */
    private function process_advanced_variance_line_item_calculated_field(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        /**
         * just in case there is no equation for this AND there is no
         * default in calculatedFieldEquationProperties, delete the $AdvancedVarianceLineItemObj
         * in question.
         * calculatedFieldEquationsForProperty() should have logged an error
         *
         * @todo - see HER-3156
         */
        /** @var CalculatedFieldEquation $CalculatedFieldEquationsObj */
        if ( ! $CalculatedFieldEquationsObj = $AdvancedVarianceLineItemObj->calculatedField->calculatedFieldEquationsForProperty($AdvancedVarianceLineItemObj->advancedVariance->property_id))
        {
            $AdvancedVarianceLineItemObj->delete();
            return;
        }

        $AdvancedVarianceLineItemObj->line_item_coefficient = 1;
        foreach ($this->CalculatedFieldsRevenue as $name)
        {
            if ($AdvancedVarianceLineItemObj->calculatedField->name === $name)
            {
                $AdvancedVarianceLineItemObj->line_item_coefficient = -1;
                break;
            }
        }

        $AdvancedVarianceLineItemObj->line_item_name = $AdvancedVarianceLineItemObj->calculatedField->name;
        $AdvancedVarianceLineItemObj->line_item_code = uniqid();

        $AdvancedVarianceLineItemObj->calculation_name                    = $CalculatedFieldEquationsObj->name;
        $AdvancedVarianceLineItemObj->calculation_description             = $CalculatedFieldEquationsObj->description;
        $AdvancedVarianceLineItemObj->calculation_equation_string         = $CalculatedFieldEquationsObj->equation_string_parsed;
        $AdvancedVarianceLineItemObj->calculation_display_equation_string = $CalculatedFieldEquationsObj->display_equation_string;

        $AdvancedVarianceLineItemObj->sort_order = $AdvancedVarianceLineItemObj->calculatedField->sort_order;
        $AdvancedVarianceLineItemObj->is_summary = $AdvancedVarianceLineItemObj->calculatedField->is_summary;

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @param $LastMonthAdvancedVarianceObj
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     */
    private function process_advanced_variance_line_item_native_account_quarterly(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj, $LastMonthAdvancedVarianceObj)
    {
        $AdvancedVarianceLineItemObj->qtr_qtd_month_1_actual   = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_actual;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_1_budgeted = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_budgeted;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_1_variance = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_variance;
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_monthly_month_1');

        $AdvancedVarianceLineItemObj->qtr_qtd_month_2_actual   = $AdvancedVarianceLineItemObj->qtr_qtd_month_1_actual + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_actual;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_2_budgeted = $AdvancedVarianceLineItemObj->qtr_qtd_month_1_budgeted + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_budgeted;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_2_variance = $AdvancedVarianceLineItemObj->qtr_qtd_month_1_variance + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_variance;
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_monthly_month_2');

        $AdvancedVarianceLineItemObj->qtr_qtd_month_3_actual   = $AdvancedVarianceLineItemObj->qtr_qtd_month_2_actual + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_actual;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_3_budgeted = $AdvancedVarianceLineItemObj->qtr_qtd_month_2_budgeted + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_budgeted;
        $AdvancedVarianceLineItemObj->qtr_qtd_month_3_variance = $AdvancedVarianceLineItemObj->qtr_qtd_month_2_variance + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_variance;
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_monthly_month_3');

        if ($LastMonthAdvancedVarianceObj)
        {
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_actual   = $LastMonthAdvancedVarianceObj->ytd_actual + $AdvancedVarianceLineItemObj->qtr_monthly_month_1_actual;
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_budgeted = $LastMonthAdvancedVarianceObj->ytd_budgeted + $AdvancedVarianceLineItemObj->qtr_monthly_month_1_budgeted;
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_variance = $LastMonthAdvancedVarianceObj->ytd_variance + $AdvancedVarianceLineItemObj->qtr_monthly_month_1_variance;
        }
        else
        {
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_actual   = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_actual;
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_budgeted = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_budgeted;
            $AdvancedVarianceLineItemObj->qtr_ytd_month_1_variance = $AdvancedVarianceLineItemObj->qtr_monthly_month_1_variance;
        }
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_ytd_month_1');

        $AdvancedVarianceLineItemObj->qtr_ytd_month_2_actual   = $AdvancedVarianceLineItemObj->qtr_ytd_month_1_actual + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_actual;
        $AdvancedVarianceLineItemObj->qtr_ytd_month_2_budgeted = $AdvancedVarianceLineItemObj->qtr_ytd_month_1_budgeted + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_budgeted;
        $AdvancedVarianceLineItemObj->qtr_ytd_month_2_variance = $AdvancedVarianceLineItemObj->qtr_ytd_month_1_variance + $AdvancedVarianceLineItemObj->qtr_monthly_month_2_variance;
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_ytd_month_2');

        $AdvancedVarianceLineItemObj->qtr_ytd_month_3_actual   = $AdvancedVarianceLineItemObj->qtr_ytd_month_2_actual + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_actual;
        $AdvancedVarianceLineItemObj->qtr_ytd_month_3_budgeted = $AdvancedVarianceLineItemObj->qtr_ytd_month_2_budgeted + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_budgeted;
        $AdvancedVarianceLineItemObj->qtr_ytd_month_3_variance = $AdvancedVarianceLineItemObj->qtr_ytd_month_2_variance + $AdvancedVarianceLineItemObj->qtr_monthly_month_3_variance;
        $this->set_variance($AdvancedVarianceLineItemObj, 'qtr_ytd_month_3');

        $AdvancedVarianceLineItemObj->line_item_coefficient
            = $AdvancedVarianceLineItemObj->nativeAccount->getCoeffients($this->AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $AdvancedVarianceLineItemObj->line_item_name
            = $AdvancedVarianceLineItemObj->nativeAccount->native_account_name;
        $AdvancedVarianceLineItemObj->line_item_code
            = $AdvancedVarianceLineItemObj->nativeAccount->native_account_code;

        $AdvancedVarianceLineItemObj->check_flagged_via_policy();

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     */
    private function roll_up_advanced_variance_line_item_report_template_account_group_quarterly(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_1_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_1_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_1_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_1_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_1_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_1_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_1');

        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_2_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_2_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_2_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_2_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_2_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_2_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_2');

        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_3_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_3_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_3_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_3_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_qtd_month_3_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_qtd_month_3_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_3');

        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_1_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_1_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_1_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_1_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_1_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_1_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_1');

        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_2_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_2_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_2_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_2_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_2_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_2_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_2');

        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_3_actual
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_3_actual',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_3_budgeted
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_3_budgeted',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $AdvancedVarianceLineItemObj->total_qtr_ytd_month_3_variance
            = $AdvancedVarianceLineItemObj->roll_up(
            'qtr_ytd_month_3_variance',
            $this->AdvancedVarianceObj->advancedVarianceLineItems
        );
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_3');

        $AdvancedVarianceLineItemObj->check_flagged_via_policy();

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     */
    private function process_calculated_fields(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        $AdvancedVarianceObj = $this->AdvancedVarianceObj;
        /**
         * if a property specific equation exists ......
         */
        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
        if ( ! $CalculatedFieldEquationObj =
            $AdvancedVarianceLineItemObj->calculatedField
                ->calculatedFieldEquations
                ->filter(
                    function ($CalculatedFieldEquationObj) use ($AdvancedVarianceObj)
                    {
                        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
                        return $CalculatedFieldEquationObj->properties->filter(
                            function ($PropertyObj) use ($AdvancedVarianceObj)
                            {
                                return $PropertyObj->id == $AdvancedVarianceObj->property_id;
                            }
                        );
                    }
                )->first()
        )
        {
            $CalculatedFieldEquationObj =
                $AdvancedVarianceLineItemObj->calculatedField->calculatedFieldEquations
                    ->filter(
                        function ($CalculatedFieldEquationObj)
                        {
                            return $CalculatedFieldEquationObj->properties->count() == 0;
                        }
                    )->first();
        }

        $FormulaInterpreterCompiler   = new FormulaInterpreterCompiler();
        $FormulaInterpreterExecutable = $FormulaInterpreterCompiler->compile($CalculatedFieldEquationObj->equation_string_parsed);

        /** @var [] $variable_arr */
        $variable_arr = [];
        /**
         * @var CalculatedFieldVariable $CalculatedFieldVariableObj
         *
         * build the $variable_arr array by looping through calculatedFieldVariables
         */
        foreach ($CalculatedFieldEquationObj->calculatedFieldVariables as $CalculatedFieldVariableObj)
        {
            if ($CalculatedFieldVariableObj->native_account_id)
            {
                $prefix                              = 'NA_' . $CalculatedFieldVariableObj->native_account_id;
                $VariableAdvancedVarianceLineItemObj = $this->AdvancedVarianceObj->advancedVarianceLineItems
                    ->filter(

                        function ($AdvancedVarianceLineItemObj) use ($CalculatedFieldVariableObj)
                        {
                            return $AdvancedVarianceLineItemObj->native_account_id == $CalculatedFieldVariableObj->native_account_id;
                        }
                    )->first();
                foreach ($this->properties_to_calculate as $property_to_calculate)
                {
                    $variable_arr[$property_to_calculate][$prefix] = $VariableAdvancedVarianceLineItemObj ? $VariableAdvancedVarianceLineItemObj->$property_to_calculate : null;
                }
            }
            elseif ($CalculatedFieldVariableObj->report_template_account_group_id)
            {
                $prefix                              = 'RTAG_' . $CalculatedFieldVariableObj->report_template_account_group_id;
                $VariableAdvancedVarianceLineItemObj = $this->AdvancedVarianceObj->advancedVarianceLineItems
                    ->filter(

                        function ($AdvancedVarianceLineItemObj) use ($CalculatedFieldVariableObj)
                        {
                            return $AdvancedVarianceLineItemObj->report_template_account_group_id == $CalculatedFieldVariableObj->report_template_account_group_id;
                        }
                    )->first();

                foreach ($this->properties_to_calculate as $property_to_calculate)
                {
                    $total_name                                    = 'total_' . $property_to_calculate;
                    $variable_arr[$property_to_calculate][$prefix] = $VariableAdvancedVarianceLineItemObj ? $VariableAdvancedVarianceLineItemObj->$total_name : null;
                }
            }
            else
            {
                throw new GeneralException('Invalid equation in AdvancedVarianceLineItemRefreshJob ' . $CalculatedFieldEquationObj->equation_string . ' ' . __FILE__ . ':' . __LINE__);
            }
        }

        /**
         * wipe out calculations where at least one val is null and null it's value
         */
        $null_variable_element_detected = [];

        $AdvancedVarianceLineItemObj->calculation_result_info = null;
        if (count($variable_arr) > 0)
        {
            foreach ($this->properties_to_calculate as $property_to_calculate)
            {
                foreach ($variable_arr[$property_to_calculate] as $variable_name => $variable_element_arr)
                {
                    if ($variable_element_arr === null)
                    {
                        unset($variable_arr[$property_to_calculate]);

                        $total_name                                             = 'total_' . $property_to_calculate;
                        $AdvancedVarianceLineItemObj->$total_name               = null;
                        $null_variable_element_detected[$property_to_calculate] = true;
                        $AdvancedVarianceLineItemObj->calculation_result_info   = 'Variable data not found ' . $variable_name;
                        continue;
                    }
                }
            }
            foreach ($variable_arr as $property_to_calculate => $variable_element_arr)
            {
                try
                {
                    $total_name = 'total_' . $property_to_calculate;
                    if (isset($null_variable_element_detected[$property_to_calculate]))
                    {
                        $AdvancedVarianceLineItemObj->$total_name = null;
                    }
                    else
                    {
                        $total_name                               = 'total_' . $property_to_calculate;
                        $AdvancedVarianceLineItemObj->$total_name = $FormulaInterpreterExecutable->run($variable_element_arr);
                    }
                }
                catch (Exception $e)
                {
                    $AdvancedVarianceLineItemObj->calculation_result_info = 'Divide from zero issue or otherwise invalid calculation';
                    $AdvancedVarianceLineItemObj->$total_name             = null;
                }
            }
        }
        else
        {
            /**
             * deal with calculations with no variables - what Ezra wanted
             */
            foreach ($this->properties_to_calculate as $property_to_calculate)
            {
                $total_name      = 'total_' . $property_to_calculate;
                $equation_string = $CalculatedFieldEquationObj->equation_string;

                try
                {
                    eval('$x = ' . $equation_string . ';');
                    /** @noinspection PhpUndefinedVariableInspection */
                    $AdvancedVarianceLineItemObj->$total_name = $x;
                }
                catch (GeneralException $e)
                {
                    $AdvancedVarianceLineItemObj->$total_name = $equation_string;
                }
            }
        }

        $this->set_variance($AdvancedVarianceLineItemObj, 'total_monthly');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_ytd');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtd');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_forecast');

        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_1');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_2');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_monthly_month_3');

        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_1');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_2');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_forecast_month_3');

        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_1');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_2');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_qtd_month_3');

        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_1');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_2');
        $this->set_variance($AdvancedVarianceLineItemObj, 'total_qtr_ytd_month_3');

        $AdvancedVarianceLineItemObj->check_flagged_via_policy();

        $AdvancedVarianceLineItemObj->save();
    }

    /**
     * @throws GeneralException
     */
    private function dump_advanced_variance_to_s3()
    {
        if (config('waypoint.advanced_variance_json_data_store_disc', 's3_advanced_variances') == 'disable')
        {
            return;
        }
        /** @var  $AdvancedVarianceFullRepositoryObj */
        $AdvancedVarianceFullRepositoryObj = App::make(AdvancedVarianceFullRepository::class);
        $ReportTemplateFullRepositoryObj   = App::make(ReportTemplateFullRepository::class);
        $AdvancedVarianceFullObj           = $AdvancedVarianceFullRepositoryObj
            ->with('advancedVarianceLineItems.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
            ->with('advancedVarianceLineItems.advancedVariance')
            ->find($this->AdvancedVarianceObj->id);

        $advanced_variance_json_string     = json_encode($AdvancedVarianceFullObj->toArray());
        $advanced_variance_json_string_md5 = md5($advanced_variance_json_string);

        $ReportTemplateFullObj = $ReportTemplateFullRepositoryObj
            ->with('reportTemplateAccountGroups.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
            ->with('reportTemplateAccountGroups.nativeAccountType.nativeAccountTypeTrailers')
            ->find($AdvancedVarianceFullObj->report_template_id);

        $report_template_json_string     = json_encode($ReportTemplateFullObj->toArray());
        $report_template_json_string_md5 = md5($report_template_json_string);

        if ($advanced_variance_json_string_md5 != $AdvancedVarianceFullObj->s3_dump_md5)
        {
            $s3_object_key = 'advanced_variance_' . $AdvancedVarianceFullObj->id . '_' . Uuid::generate() . '.json';
            DB::update(
                DB::raw(
                    "
                        UPDATE advanced_variances SET
                            s3_dump_md5 = :S3_DUMP_MD5,
                            last_s3_dump_name = :LAST_S3_DUMP_NAME,
                            last_s3_dump_date = NOW()
                            WHERE id = :ADVANCED_VARIANCE_ID
                    "
                ),
                [
                    'S3_DUMP_MD5'          => $advanced_variance_json_string_md5,
                    'LAST_S3_DUMP_NAME'    => $s3_object_key,
                    'ADVANCED_VARIANCE_ID' => $AdvancedVarianceFullObj->id,
                ]
            );
            $this->send_to_s3($s3_object_key, $advanced_variance_json_string, config('waypoint.advanced_variance_json_data_store_disc', 's3_advanced_variances'));
        }

        if ($report_template_json_string_md5 != $ReportTemplateFullObj->s3_dump_md5)
        {
            $s3_object_key = 'report_template_' . $ReportTemplateFullObj->id . '_' . Uuid::generate() . '.json';
            DB::update(
                DB::raw(
                    "
                        UPDATE report_templates SET
                            s3_dump_md5 = :S3_DUMP_MD5,
                            last_s3_dump_name = :LAST_S3_DUMP_NAME,
                            last_s3_dump_date = NOW()
                            WHERE id = :ADVANCED_VARIANCE_ID
                    "
                ),
                [
                    'S3_DUMP_MD5'          => $advanced_variance_json_string_md5,
                    'LAST_S3_DUMP_NAME'    => $s3_object_key,
                    'ADVANCED_VARIANCE_ID' => $AdvancedVarianceFullObj->id,
                ]
            );
            $this->send_to_s3($s3_object_key, $report_template_json_string, config('waypoint.advanced_variance_json_data_store_disc', 's3_advanced_variances'));
        }
    }
}
