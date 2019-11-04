<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\Ledger\NativeCoaLedgerRepository;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Prettus\Repository\Exceptions\RepositoryException;
use function in_array;

class AdvancedVarianceRepository extends AdvancedVarianceRepositoryBase
{
    /** @var  AdvancedVarianceLineItemRepository */
    private $AdvancedVarianceLineItemRepositoryObj;

    /** @var  AdvancedVarianceThresholdRepository */
    private $AdvancedVarianceThresholdRepositoryObj;
    /** @var  NativeAccountRepository */
    private $NativeAccountRepositoryObj;
    /** @var UserRepository */
    private $UserRepositoryObj;
    /** @var RelatedUserRepository */
    private $RelatedUserRepositoryObj;
    /** @var PropertyRepository */
    private $PropertyRepositoryObj;
    /** @var array */
    private $LedgerVarianceDataArr;
    /** @var array */
    private $LedgerVarianceDataMonth1Arr;
    /** @var array */
    private $LedgerVarianceDataMonth2Arr;
    /** @var array */
    private $LedgerVarianceDataMonth3Arr;
    /** @var  [] */
    private $CalculatedFieldsRevenue = ['Revenue', 'Net Operating Income', 'Net Income', 'Income', 'NOI', 'Net Income'];

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->UserRepositoryObj          = App::make(UserRepository::class);
        $this->RelatedUserRepositoryObj   = App::make(RelatedUserRepository::class);
        $this->PropertyRepositoryObj      = App::make(PropertyRepository::class);
        $this->NativeAccountRepositoryObj = App::make(NativeAccountRepository::class);
    }

    /** @var  NativeCoaLedgerRepository|NativeCoaLedgerMockRepository */
    private static $NativeCoaLedgerRepositoryObj;

    /**
     * Save a new AdvancedVariance in repository
     *
     * @param array $attributes
     * @return AdvancedVariance|null
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        /**
         * be careful to not set off events if $this->suppress_events
         * @todo This needs refactoring badly
         */
        $this->UserRepositoryObj->setSuppressEvents($this->suppress_events);
        $this->RelatedUserRepositoryObj->setSuppressEvents($this->suppress_events);
        $this->PropertyRepositoryObj->setSuppressEvents($this->suppress_events);
        $this->NativeAccountRepositoryObj->setSuppressEvents($this->suppress_events);
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);
        $this->AdvancedVarianceLineItemRepositoryObj->setSuppressEvents($this->suppress_events);
        $this->AdvancedVarianceThresholdRepositoryObj = App::make(AdvancedVarianceThresholdRepository::class);
        $this->AdvancedVarianceThresholdRepositoryObj->setSuppressEvents($this->suppress_events);

        AdvancedVariance::setSuspendValidation(true);
        AdvancedVarianceLineItem::setSuspendValidation(true);

        /**
         * the as_of_month/as_of_year is the month/year of resently recieved accounting data
         */
        if ( ! isset($attributes['property_id']) || ! $attributes['property_id'])
        {
            throw new GeneralException('property_id required ' . __FILE__ . ':' . __LINE__, 400);
        }
        if ( ! isset($attributes['period_type']) || ! $attributes['period_type'])
        {
            $attributes['period_type'] = $this->get_conf_values('ADVANCED_VARIANCE_FREQ', $attributes['property_id']);
        }
        if ( ! isset($attributes['trigger_mode']) || ! $attributes['trigger_mode'])
        {
            $attributes['trigger_mode'] = $this->get_conf_values('ADVANCED_VARIANCE_TRIGGER', $attributes['property_id']);
        }
        if ( ! isset($attributes['as_of_month']) || ! $attributes['as_of_month'])
        {
            throw new GeneralException('Please provide a as_of_month ' . __FILE__ . ':' . __LINE__, 400);
        }
        if ( ! isset($attributes['as_of_year']) || ! $attributes['as_of_year'])
        {
            throw new GeneralException('Please provide a as_of_year ' . __FILE__ . ':' . __LINE__, 400);
        }

        if ( ! in_array($attributes['period_type'], AdvancedVariance::$period_type_arr))
        {
            throw new GeneralException('period_type is invalid ' . __FILE__ . ':' . __LINE__, 400);
        }
        if ( ! in_array($attributes['trigger_mode'], AdvancedVariance::$trigger_mode_value_arr))
        {
            throw new GeneralException('trigger_mode is invalid ' . __FILE__ . ':' . __LINE__, 400);
        }

        if (
            $attributes['period_type'] == AdvancedVariance::PERIOD_TYPE_QUARTERLY &&
            ! in_array($attributes['as_of_month'], [3, 6, 9, 12])
        )
        {
            throw new GeneralException('period_type is quarterly but as_of_month is ' . $attributes['as_of_month'] . __FILE__ . ':' . __LINE__, 400);
        }

        $attributes['locked_date']    = null;
        $attributes['locker_user_id'] = null;

        /** @var App\Waypoint\Models\Property $PropertyObj */
        if ( ! $PropertyObj =
            $this->PropertyRepositoryObj
                ->with('client')
                ->with('nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->with('nativeCoas.nativeAccounts.reportTemplateMappings.reportTemplateAccountGroup')
                ->with('nativeCoas.nativeAccounts.nativeCoa')
                ->with('advancedVarianceThresholds')
                ->find($attributes['property_id'])
        )
        {
            throw new GeneralException('No such property ' . __FILE__ . ':' . __LINE__, 400);
        }
        /** @var NativeCoa $NativeCoaObj */
        if ( ! $NativeCoaObj = $PropertyObj->nativeCoas->first())
        {
            throw new GeneralException('No COA defined for property id = : ' . $PropertyObj->id, 500);
        }

        /**
         * here's the deal. If someone tries to again create an $AdvancedVarianceObj that
         * per advanced_variance_start_date and property_id already exists, DO NOT DELETE THE $AdvancedVarianceObj.
         * Rather, update $AdvancedVarianceLineItemObj using a new call to
         * $this->getLedgerVarianceDataArr
         *
         * @var AdvancedVariance $AdvancedVarianceObj
         */
        $overwrite_mode = false;
        if ($AdvancedVarianceObj =
            $this->get_hydrated_advanced_variance(null, $attributes['property_id'], $attributes['as_of_year'], $attributes['as_of_month'])
        )
        {
            if ($AdvancedVarianceObj->locked())
            {
                throw new GeneralException('Advanced variance is locked' . ' ' . __FILE__ . ':' . __LINE__);
            }

            $overwrite_mode = true;
            if (
                isset($attributes['report_template_id']) &&
                $attributes['report_template_id'] &&
                $attributes['report_template_id'] != $AdvancedVarianceObj->report_template_id)
            {
                throw new GeneralException('You cannot change the report_template_id of an AdvancedVariance');
            }

            $attributes['report_template_id'] = $AdvancedVarianceObj->report_template_id;

            if ($attributes['period_type'] != $AdvancedVarianceObj->period_type)
            {
                throw new GeneralException('You cannot change the period_type of an AdvancedVariance' . __FILE__ . ':' . __LINE__);
            }
            if ($attributes['as_of_month'] != $AdvancedVarianceObj->as_of_month)
            {
                throw new GeneralException('You cannot change the as_of_month of an AdvancedVariance' . __FILE__ . ':' . __LINE__);
            }
            if ($attributes['as_of_year'] != $AdvancedVarianceObj->as_of_year)
            {
                throw new GeneralException('You cannot change the as_of_year of an AdvancedVariance' . __FILE__ . ':' . __LINE__);
            }
            if ($attributes['property_id'] != $AdvancedVarianceObj->property_id)
            {
                throw new GeneralException('You cannot change the property_id of an AdvancedVariance' . __FILE__ . ':' . __LINE__);
            }
            if ($attributes['property_id'] != $AdvancedVarianceObj->property_id)
            {
                throw new GeneralException('You cannot change the property_id of an AdvancedVariance' . __FILE__ . ':' . __LINE__);
            }

            $attributes['threshold_mode'] = $AdvancedVarianceObj->property->client->getConfigValue('ADVANCED_VARIANCE_THRESHOLD_MODE');
            $AdvancedVarianceObj          = parent::update($attributes, $AdvancedVarianceObj->id);

            $AdvancedVarianceObj = $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);
        }
        else
        {
            $attributes['threshold_mode'] = $PropertyObj->client->getConfigValue('ADVANCED_VARIANCE_THRESHOLD_MODE');

            /**
             * we are in over write mode. Only create a $AdvancedVarianceObj if
             * none exists for advanced_variance_start_date and property_id
             */
            if ( ! isset($attributes['report_template_id']) || ! $attributes['report_template_id'])
            {
                $attributes['report_template_id'] = $PropertyObj->client->defaultAdvancedVarianceReportTemplate->id;
            }
            $attributes['advanced_variance_status']     = AdvancedVariance::ACTIVE_STATUS_UNLOCKED;
            $attributes['advanced_variance_start_date'] =
                Carbon::create(
                    $attributes['as_of_year'],
                    $attributes['as_of_month'],
                    1, 0, 0, 0
                )->format('Y-m-d H:i:s');
            $attributes['target_locked_date']           =
                Carbon::create(
                    $attributes['as_of_year'],
                    $attributes['as_of_month'],
                    1, 0, 0, 0
                )->format('Y-m-d H:i:s');

            /** @var AdvancedVariance $AdvancedVarianceObj */
            $AdvancedVarianceObj = parent::create($attributes);
            $AdvancedVarianceObj = $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);
        }

        /**
         * only use codes that are ref'ed in report template
         * AND
         * point to a native account that is in the property in questions nativeCoas which has proper trailer
         * @todo this is really slow
         * @todo this is really slow
         * @todo this is really slow
         * @todo this is really slow
         * @todo this is really slow
         * grab the intersection of the $native_codes and filter on the $nativeAccounts of the property in question
         */

        /**
         * let's populate $advanced_variance_default_native_coa_code_arr
         * with the account codes that are the intersection
         * of the native accounts that this report template refers to and
         * the native accounts in the chart of accounts that this
         * property points at
         * @var Collection $PropertyNativeAccountsObjArr
         */
        $PropertyNativeAccountsObjArr = $PropertyObj
            ->nativeCoas
            ->first()
            ->nativeAccounts;

        /** @var Collection $ReportTemplateNativeAccountsObjArr */
        $ReportTemplateNativeAccountsObjArr = $AdvancedVarianceObj->reportTemplate->reportTemplateAccountGroups->map(
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroup */
            function ($ReportTemplateAccountGroup)
            {
                return $ReportTemplateAccountGroup->nativeAccounts;
            }
        )->flatten();

        $advanced_variance_default_native_id_arr =
            array_unique(
                array_intersect(
                    $PropertyNativeAccountsObjArr->pluck('id')->toArray(),
                    $ReportTemplateNativeAccountsObjArr->pluck('id')->toArray()
                )
            );

        $FilteredNativeAccountObjArr                   = $this->NativeAccountRepositoryObj->findWhereIn(
            'id',
            $advanced_variance_default_native_id_arr
        );
        $advanced_variance_default_native_coa_code_arr = $FilteredNativeAccountObjArr->pluck('native_account_code')->toArray();
        try
        {
            $this->LedgerVarianceDataArr = $this->getLedgerVarianceDataArr(
                $advanced_variance_default_native_coa_code_arr,
                $AdvancedVarianceObj->property_id,
                $AdvancedVarianceObj->as_of_month,
                $AdvancedVarianceObj->as_of_year,
                $AdvancedVarianceObj->period_type
            );
            if ($AdvancedVarianceObj->period_type == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
            {
                $this->LedgerVarianceDataMonth1Arr = $this->getLedgerVarianceDataArr(
                    $advanced_variance_default_native_coa_code_arr,
                    $AdvancedVarianceObj->property_id,
                    $AdvancedVarianceObj->as_of_month - 2,
                    $AdvancedVarianceObj->as_of_year,
                    AdvancedVariance::PERIOD_TYPE_MONTHLY
                );
                $this->LedgerVarianceDataMonth2Arr = $this->getLedgerVarianceDataArr(
                    $advanced_variance_default_native_coa_code_arr,
                    $AdvancedVarianceObj->property_id,
                    $AdvancedVarianceObj->as_of_month - 1,
                    $AdvancedVarianceObj->as_of_year,
                    AdvancedVariance::PERIOD_TYPE_MONTHLY
                );
                $this->LedgerVarianceDataMonth3Arr = $this->getLedgerVarianceDataArr(
                    $advanced_variance_default_native_coa_code_arr,
                    $AdvancedVarianceObj->property_id,
                    $AdvancedVarianceObj->as_of_month,
                    $AdvancedVarianceObj->as_of_year,
                    AdvancedVariance::PERIOD_TYPE_MONTHLY
                );
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('Call to AdvancedVarianceRepository::getLedgerVarianceDataArr() failed - client_id_old = ' . $AdvancedVarianceObj->property->client->client_id_old);
        }

        /**
         * @var  AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
         *
         * Clean up (potentially) any $AdvancedVarianceLineItemObj's that are not currently in
         * $advanced_variance_default_native_coa_codes or array_keys($this->LedgerVarianceDataArr)
         */
        foreach ($AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
        {
            /**
             * if an advancedVarianceLineItem points to a native account that is not in
             * $advanced_variance_default_native_coa_codes, delete id
             */
            if (
                $AdvancedVarianceLineItemObj->native_account_id &&
                ! in_array($AdvancedVarianceLineItemObj->nativeAccount->native_account_code, $advanced_variance_default_native_coa_code_arr)
            )
            {
                $this->AdvancedVarianceLineItemRepositoryObj->delete($AdvancedVarianceLineItemObj->id);
                continue;
            }

            /**
             * if an advancedVarianceLineItem points to a native account that is not
             * found in $this->getLedgerVarianceDataArr() , delete id
             */
            if (
                $AdvancedVarianceLineItemObj->native_account_id &&
                ! in_array($AdvancedVarianceLineItemObj->nativeAccount->native_account_code, array_keys($this->LedgerVarianceDataArr))
            )
            {
                $this->AdvancedVarianceLineItemRepositoryObj->delete($AdvancedVarianceLineItemObj->id);
                continue;
            }
        }
        $AdvancedVarianceObj = $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);

        if (count($this->LedgerVarianceDataArr) == 0)
        {
            throw new GeneralException(
                'No ledger data for this property, month, year: ' . implode(',', $advanced_variance_default_native_coa_code_arr) . ' at ' . __LINE__ . ':' . __FILE__, 500);
        }
        if ( ! $this->detect_non_zero())
        {
            throw new GeneralException(
                'All zero ledger data for this property, month, year: ' .
                implode(',', $advanced_variance_default_native_coa_code_arr) . ' at ' . __LINE__ . ':' . __FILE__,
                500
            );
        }

        $this->AdvancedVarianceLineItemRepositoryObj->setSuppressEvents(true);

        foreach (array_unique($advanced_variance_default_native_coa_code_arr) as $advanced_variance_default_native_coa_code)
        {
            if ( ! $NativeAccountObj = $NativeCoaObj->nativeAccounts->where('native_account_code', $advanced_variance_default_native_coa_code)->first())
            {
                continue;
            }
            $NativeAccountAdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj
                ->getNativeAccountAdvancedVarianceThreshold(
                    $PropertyObj->client_id,
                    $PropertyObj->id,
                    $NativeAccountObj->id,
                    $NativeAccountObj->native_account_type_id
                );
            if (isset($this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]))
            {
                $this->init_quarterly_details($advanced_variance_default_native_coa_code);

                if ( ! $AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems
                    ->where(
                        'native_account_id',
                        $NativeAccountObj->id
                    )->first()
                )
                {
                    /**
                     * create some lineItems
                     */
                    /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
                    if ($attributes['period_type'] == AdvancedVariance::PERIOD_TYPE_MONTHLY)
                    {
                        $this->CreateMonthlyAdvancedVarianceLineItem(
                            $AdvancedVarianceObj,
                            $NativeAccountObj,
                            $NativeAccountAdvancedVarianceThresholdObj,
                            $advanced_variance_default_native_coa_code
                        );

                    }
                    elseif ($attributes['period_type'] == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
                    {
                        $this->CreateQuarterlyAdvancedVarianceLineItem(
                            $AdvancedVarianceObj,
                            $NativeAccountObj,
                            $NativeAccountAdvancedVarianceThresholdObj,
                            $advanced_variance_default_native_coa_code
                        );
                    }
                    else
                    {
                        throw new GeneralException('Invalid advanced variance frequency' . __FILE__ . ':' . __LINE__);
                    }
                }
                else
                {
                    if ($attributes['period_type'] == AdvancedVariance::PERIOD_TYPE_MONTHLY)
                    {
                        $this->UpdateMonthlyAdvancedVarianceLineItem(
                            $AdvancedVarianceObj,
                            $NativeAccountObj,
                            $AdvancedVarianceLineItemObj,
                            $NativeAccountAdvancedVarianceThresholdObj,
                            $advanced_variance_default_native_coa_code
                        );
                    }
                    elseif ($attributes['period_type'] == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
                    {
                        $this->UpdateQuarterlyAdvancedVarianceLineItem(
                            $AdvancedVarianceObj,
                            $NativeAccountObj,
                            $AdvancedVarianceLineItemObj,
                            $NativeAccountAdvancedVarianceThresholdObj,
                            $advanced_variance_default_native_coa_code
                        );
                    }
                    else
                    {
                        throw new GeneralException('Invalid advanced variance frequency' . __FILE__ . ':' . __LINE__);
                    }
                }
            }
            else
            {
                if ($AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems
                    ->where(
                        'native_account_id',
                        $NativeCoaObj
                            ->nativeAccounts
                            ->where('native_account_code', $advanced_variance_default_native_coa_code)
                            ->first()->id
                    )->first()
                )
                {
                    /**
                     * looks like native account did not get data from ledger
                     */
                    $this->delete($AdvancedVarianceLineItemObj->id);
                }
            }
        }

        /**
         * add reportTemplateAccountGroups line item it it does not exist
         * @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
         */
        foreach ($AdvancedVarianceObj->reportTemplate->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
        {
            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj
                ->getReportTemplateAccountGroupAdvancedVarianceThreshold(
                    $PropertyObj->client_id,
                    $PropertyObj->id,
                    $ReportTemplateAccountGroupObj->id,
                    $ReportTemplateAccountGroupObj->native_account_type_id
                );
            if ( ! $AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems
                ->where('report_template_account_group_id', $ReportTemplateAccountGroupObj->id)
                ->first()
            )
            {
                $this->AdvancedVarianceLineItemRepositoryObj->create(
                    [
                        'advanced_variance_id'             => $AdvancedVarianceObj->id,
                        'native_account_id'                => null,
                        'report_template_account_group_id' => $ReportTemplateAccountGroupObj->id,

                        'native_account_overage_threshold_amount'           => null,
                        'native_account_overage_threshold_amount_too_good'  => null,
                        'native_account_overage_threshold_percent'          => null,
                        'native_account_overage_threshold_percent_too_good' => null,
                        'native_account_overage_threshold_operator'         => null,

                        'report_template_account_group_overage_threshold_amount'           =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount,
                        'report_template_account_group_overage_threshold_amount_too_good'  =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount_too_good,
                        'report_template_account_group_overage_threshold_percent'          =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent,
                        'report_template_account_group_overage_threshold_percent_too_good' =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent_too_good,
                        'report_template_account_group_overage_threshold_operator'         =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator,

                        'calculated_field_overage_threshold_amount'           => null,
                        'calculated_field_overage_threshold_amount_too_good'  => null,
                        'calculated_field_overage_threshold_percent'          => null,
                        'calculated_field_overage_threshold_percent_too_good' => null,
                        'calculated_field_overage_threshold_operator'         => null,

                        'line_item_coefficient' => $ReportTemplateAccountGroupObj->nativeAccountType->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient,
                        'line_item_name'        => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                        'line_item_code'        => $ReportTemplateAccountGroupObj->report_template_account_group_code,

                        'sort_order'                       => $ReportTemplateAccountGroupObj->sort_order,
                        'is_summary'                       => $ReportTemplateAccountGroupObj->is_summary,
                        'is_summary_tab_default_line_item' => $ReportTemplateAccountGroupObj->is_summary_tab_default_line_item,
                    ]
                );
            }
            else
            {
                $this->AdvancedVarianceLineItemRepositoryObj->update(
                    [
                        'report_template_account_group_overage_threshold_amount'           =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount,
                        'report_template_account_group_overage_threshold_amount_too_good'  =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount_too_good,
                        'report_template_account_group_overage_threshold_percent'          =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent,
                        'report_template_account_group_overage_threshold_percent_too_good' =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent_too_good,
                        'report_template_account_group_overage_threshold_operator'         =>
                            $ReportTemplateAccountGroupAdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator,

                        'line_item_coefficient' => $ReportTemplateAccountGroupObj->nativeAccountType->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient,
                        'line_item_name'        => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                        'line_item_code'        => $ReportTemplateAccountGroupObj->report_template_account_group_code,

                        'sort_order'                       => $ReportTemplateAccountGroupObj->sort_order,
                        'is_summary'                       => $ReportTemplateAccountGroupObj->is_summary,
                        'is_summary_tab_default_line_item' => $ReportTemplateAccountGroupObj->is_summary_tab_default_line_item,
                    ],
                    $AdvancedVarianceLineItemObj->id
                );
            }
        }

        foreach ($AdvancedVarianceObj->reportTemplate->calculatedFields as $CalculatedFieldObj)
        {
            /**
             * just in case there is no equation for this AND there is no
             * default in calculatedFieldEquationProperties, delete the $AdvancedVarianceLineItemObj
             * in question
             * calculatedFieldEquationsForProperty() should have logged an error
             */
            /** @var CalculatedFieldEquation $CalculatedFieldEquationsObj */
            if ( ! $CalculatedFieldEquationsObj = $CalculatedFieldObj->calculatedFieldEquationsForProperty($AdvancedVarianceObj->property_id))
            {
                if ($AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems
                    ->where('calculated_field_id', $CalculatedFieldObj->id)
                    ->first()
                )
                {
                    $this->AdvancedVarianceLineItemRepositoryObj->delete($AdvancedVarianceLineItemObj->id);
                }
                continue;
            }

            $CalculatedFieldAdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj
                ->getCalculatedFieldAdvancedVarianceThreshold(
                    $PropertyObj->client_id,
                    $PropertyObj->id,
                    $CalculatedFieldObj->id
                );
            if ($AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems
                ->where('calculated_field_id', $CalculatedFieldObj->id)
                ->first()
            )
            {
                $this->AdvancedVarianceLineItemRepositoryObj->update(
                    [
                        'calculated_field_overage_threshold_amount'           => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount,
                        'calculated_field_overage_threshold_amount_too_good'  => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount_too_good,
                        'calculated_field_overage_threshold_percent'          => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent,
                        'calculated_field_overage_threshold_percent_too_good' => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent_too_good,
                        'calculated_field_overage_threshold_operator'         => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator,

                        'line_item_name' => $CalculatedFieldObj->name,

                        'calculation_name'                    => $CalculatedFieldEquationsObj->name,
                        'calculation_description'             => $CalculatedFieldEquationsObj->description,
                        'calculation_equation_string'         => $CalculatedFieldEquationsObj->equation_string_parsed,
                        'calculation_display_equation_string' => $CalculatedFieldEquationsObj->display_equation_string,

                        'sort_order'                       => $CalculatedFieldObj->sort_order,
                        'is_summary'                       => $CalculatedFieldObj->is_summary,
                        'is_summary_tab_default_line_item' => $CalculatedFieldObj->is_summary_tab_default_line_item,
                    ],
                    $AdvancedVarianceLineItemObj->id
                );
            }
            else
            {
                $calculated_field_coefficient = 1;
                foreach ($this->CalculatedFieldsRevenue as $name)
                {
                    if ($CalculatedFieldObj->name === $name)
                    {
                        $calculated_field_coefficient = -1;
                        break;
                    }
                }

                $this->AdvancedVarianceLineItemRepositoryObj->create(
                    [
                        'advanced_variance_id'             => $AdvancedVarianceObj->id,
                        'native_account_id'                => null,
                        'report_template_account_group_id' => null,
                        'calculated_field_id'              => $CalculatedFieldObj->id,

                        'native_account_overage_threshold_amount'           => null,
                        'native_account_overage_threshold_amount_too_good'  => null,
                        'native_account_overage_threshold_percent'          => null,
                        'native_account_overage_threshold_percent_too_good' => null,
                        'native_account_overage_threshold_operator'         => null,

                        'report_template_account_group_overage_threshold_amount'           => null,
                        'report_template_account_group_overage_threshold_amount_too_good'  => null,
                        'report_template_account_group_overage_threshold_percent'          => null,
                        'report_template_account_group_overage_threshold_percent_too_good' => null,
                        'report_template_account_group_overage_threshold_operator'         => null,

                        'calculated_field_overage_threshold_amount'           => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount,
                        'calculated_field_overage_threshold_amount_too_good'  => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount_too_good,
                        'calculated_field_overage_threshold_percent'          => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent,
                        'calculated_field_overage_threshold_percent_too_good' => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent_too_good,
                        'calculated_field_overage_threshold_operator'         => $CalculatedFieldAdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator,

                        'line_item_coefficient' => $calculated_field_coefficient,
                        'line_item_name'        => $CalculatedFieldObj->name,
                        'line_item_code'        => uniqid(),

                        'calculation_name'                    => $CalculatedFieldEquationsObj->name,
                        'calculation_description'             => $CalculatedFieldEquationsObj->description,
                        'calculation_equation_string'         => $CalculatedFieldEquationsObj->equation_string_parsed,
                        'calculation_display_equation_string' => $CalculatedFieldEquationsObj->display_equation_string,

                        'sort_order'                       => $CalculatedFieldObj->sort_order,
                        'is_summary'                       => $CalculatedFieldObj->is_summary,
                        'is_summary_tab_default_line_item' => $CalculatedFieldObj->is_summary_tab_default_line_item,
                    ]
                );
            }
        }
        $AdvancedVarianceObj = $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);
        /**
         * check that at least one advancedVarianceLineItems exists for $AdvancedVarianceObj
         */
        if ( ! $AdvancedVarianceObj->advancedVarianceLineItems
            ->filter(
                function ($AdvancedVarianceLineItemObj, $key)
                {
                    return ! is_null($AdvancedVarianceLineItemObj->native_account_id);
                }
            )
            ->count()
        )
        {
            $this->delete($AdvancedVarianceObj->id);
            throw new GeneralException(
                'Empty $AdvancedVarianceObj detected after create $this->LedgerVarianceDataArr count = ' . count($this->LedgerVarianceDataArr) .
                ' $advanced_variance_default_native_coa_codes = ' . print_r($advanced_variance_default_native_coa_code_arr, true)
            );
        }

        if ($overwrite_mode)
        {
            /**
             * now let's unlock this bad boy if it needs it
             */

            if (
                $AdvancedVarianceObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED ||
                $AdvancedVarianceObj->locker_user_id != null ||
                $AdvancedVarianceObj->locked_date != null
            )
            {
                if ( ! $AdvancedVarianceObj->approved())
                {
                    $AdvancedVarianceObj->advanced_variance_status = AdvancedVariance::ACTIVE_STATUS_UNLOCKED;
                    $AdvancedVarianceObj->locker_user_id           = null;
                    $AdvancedVarianceObj->locked_date              = null;
                    $AdvancedVarianceObj->save();
                }
                else
                {
                    $results = DB::select(
                        DB::raw(
                            "SELECT
                                count(*) AS advanced_variance_line_item_count
                                FROM advanced_variances

                                JOIN advanced_variance_line_items ON advanced_variance_line_items.advanced_variance_id = advanced_variances.id
                                WHERE
                                    advanced_variances.id = :ADVANCED_VARIANCES_ID AND
                                    (
                                        advanced_variance_line_items.flagged_via_policy = TRUE OR
                                        advanced_variance_line_items.flagged_manually = TRUE
                                    ) AND
                                    advanced_variance_line_items.resolver_user_id IS NULL
                            "
                        ),
                        [
                            'ADVANCED_VARIANCES_ID' => $AdvancedVarianceObj->id,
                        ]
                    );
                    if ($results[0]->advanced_variance_line_item_count > 0)
                    {
                        $AdvancedVarianceObj->advanced_variance_status = AdvancedVariance::ACTIVE_STATUS_UNLOCKED;
                        $AdvancedVarianceObj->locker_user_id           = null;
                        $AdvancedVarianceObj->locked_date              = null;
                        $AdvancedVarianceObj->save();
                    }
                }
            }
        }
        else
        {
            /**
             * always refresh_reviewers in create case
             */
            $this->refresh_reviewers($AdvancedVarianceObj);
            $AdvancedVarianceObj = $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);
        }

        /** @var App\Waypoint\Collection $RTAGAdvancedVarianceLineItemObjArr */
        $RTAGAdvancedVarianceLineItemObjArr = $AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->filter(
                function ($AdvancedVarianceLineItemObj)
                {
                    return $AdvancedVarianceLineItemObj->report_template_account_group_id == null;
                }
            );
        if ($RTAGAdvancedVarianceLineItemObjArr->count() == 0)
        {
            $this->delete($AdvancedVarianceObj->id);
            throw new GeneralException(
                'Empty $AdvancedVarianceObj detected (no report groups)after create $this->LedgerVarianceDataArr count = ' . count($this->LedgerVarianceDataArr) .
                ' $advanced_variance_default_native_coa_codes = ' . print_r($advanced_variance_default_native_coa_code_arr, true)
            );
        }

        /** @var App\Waypoint\Collection $NAAdvancedVarianceLineItemObjArr */
        $NAAdvancedVarianceLineItemObjArr = $AdvancedVarianceObj
            ->advancedVarianceLineItems
            ->filter(
                function ($AdvancedVarianceLineItemObj)
                {
                    return $AdvancedVarianceLineItemObj->report_template_account_group_id !== null;
                }
            );
        if ($NAAdvancedVarianceLineItemObjArr->count() == 0)
        {
            $this->delete($AdvancedVarianceObj->id);
            throw new GeneralException(
                'Empty $AdvancedVarianceObj detected (no native accounts)after create $this->LedgerVarianceDataArr count = ' . count($this->LedgerVarianceDataArr) .
                ' $advanced_variance_default_native_coa_codes = ' . print_r($advanced_variance_default_native_coa_code_arr, true)
            );
        }

        /**
         * this is not a notification. It updates the num_* fields in advanced_variances table
         */
        $this->triggerCreatedEvent($AdvancedVarianceObj);

        AdvancedVariance::setSuspendValidation(false);
        AdvancedVarianceLineItem::setSuspendValidation(false);
        Cache::tags('AdvancedVariance_' . $AdvancedVarianceObj->property->client_id)->flush();

        return $this->get_hydrated_advanced_variance($AdvancedVarianceObj->id);
    }

    /**
     * @param $AdvancedVarianceObj
     * @param $NativeCoaObj
     * @param $NativeAccountAdvancedVarianceThresholdObj
     * @param $advanced_variance_default_native_coa_code
     * @return AdvancedVarianceLineItem
     */
    public function CreateMonthlyAdvancedVarianceLineItem(
        $AdvancedVarianceObj,
        NativeAccount $NativeAccountObj,
        $NativeAccountAdvancedVarianceThresholdObj,
        $advanced_variance_default_native_coa_code
    ) {
        $line_item_coefficient = $NativeAccountObj->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $line_item_name        = $NativeAccountObj->native_account_name;
        $line_item_code        = $NativeAccountObj->native_account_code;

        $ReportTemplateMappingObj = $NativeAccountObj
            ->reportTemplateMappings->filter(
                function ($ReportTemplateMappingObj) use ($AdvancedVarianceObj)
                {
                    /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == $AdvancedVarianceObj->report_template_id;
                }
            )->first();

        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->create(
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
                'native_account_id'    => $NativeAccountObj->id,

                'monthly_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'monthly_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'monthly_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'monthly_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'forecast_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'forecast_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'forecast_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'forecast_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'ytd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_budgeted'] ?: 0,
                'ytd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_actual'] ?: 0,
                'ytd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_variance'] ?: 0,
                'ytd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_percent_variance'] ?: 0,

                'qtd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_budgeted'] ?: 0,
                'qtd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_actual'] ?: 0,
                'qtd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_variance'] ?: 0,
                'qtd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_percent_variance'] ?: 0,

                'native_account_overage_threshold_amount'           =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount,
                'native_account_overage_threshold_amount_too_good'  =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount_too_good,
                'native_account_overage_threshold_percent'          =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent,
                'native_account_overage_threshold_percent_too_good' =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent_too_good,
                'native_account_overage_threshold_operator'         =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_operator,

                'report_template_account_group_overage_threshold_amount'           => null,
                'report_template_account_group_overage_threshold_amount_too_good'  => null,
                'report_template_account_group_overage_threshold_percent'          => null,
                'report_template_account_group_overage_threshold_percent_too_good' => null,
                'report_template_account_group_overage_threshold_operator'         => null,

                'calculated_field_overage_threshold_amount'           => null,
                'calculated_field_overage_threshold_amount_too_good'  => null,
                'calculated_field_overage_threshold_percent'          => null,
                'calculated_field_overage_threshold_percent_too_good' => null,
                'calculated_field_overage_threshold_operator'         => null,

                'qtr_monthly_month_1_budgeted'         => null,
                'qtr_monthly_month_1_actual'           => null,
                'qtr_monthly_month_1_variance'         => null,
                'qtr_monthly_month_1_percent_variance' => null,

                'qtr_monthly_month_2_budgeted'         => null,
                'qtr_monthly_month_2_actual'           => null,
                'qtr_monthly_month_2_variance'         => null,
                'qtr_monthly_month_2_percent_variance' => null,

                'qtr_monthly_month_3_budgeted'         => null,
                'qtr_monthly_month_3_actual'           => null,
                'qtr_monthly_month_3_variance'         => null,
                'qtr_monthly_month_3_percent_variance' => null,

                'line_item_coefficient' => $line_item_coefficient,
                'line_item_name'        => $line_item_name,
                'line_item_code'        => $line_item_code,

                'sort_order'                       => $ReportTemplateMappingObj->sort_order,
                'is_summary'                       => $ReportTemplateMappingObj->is_summary,
                'is_summary_tab_default_line_item' => $ReportTemplateMappingObj->is_summary_tab_default_line_item,
            ]
        );
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @param $AdvancedVarianceObj
     * @param $NativeCoaObj
     * @param $NativeAccountAdvancedVarianceThresholdObj
     * @param $advanced_variance_default_native_coa_code
     * @return AdvancedVarianceLineItem
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function CreateQuarterlyAdvancedVarianceLineItem(
        $AdvancedVarianceObj,
        NativeAccount $NativeAccountObj,
        $NativeAccountAdvancedVarianceThresholdObj,
        $advanced_variance_default_native_coa_code
    ) {

        $line_item_coefficient       = $NativeAccountObj->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $line_item_name              = $NativeAccountObj->native_account_name;
        $line_item_code              = $NativeAccountObj->native_account_code;
        $ReportTemplateMappingObj    = $NativeAccountObj
            ->reportTemplateMappings->filter(
                function ($ReportTemplateMappingObj) use ($AdvancedVarianceObj)
                {
                    /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == $AdvancedVarianceObj->report_template_id;
                }
            )->first();
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->create(
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
                'native_account_id'    => $NativeAccountObj->id,

                'monthly_budgeted'         => null,
                'monthly_actual'           => null,
                'monthly_variance'         => null,
                'monthly_percent_variance' => null,

                'forecast_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'forecast_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'forecast_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'forecast_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'ytd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_budgeted'] ?: 0,
                'ytd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_actual'] ?: 0,
                'ytd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_variance'] ?: 0,
                'ytd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_percent_variance'] ?: 0,

                'qtd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_budgeted'] ?: 0,
                'qtd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_actual'] ?: 0,
                'qtd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_variance'] ?: 0,
                'qtd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_percent_variance'] ?: 0,

                'native_account_overage_threshold_amount'           =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount,
                'native_account_overage_threshold_amount_too_good'  =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount_too_good,
                'native_account_overage_threshold_percent'          =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent,
                'native_account_overage_threshold_percent_too_good' =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent_too_good,
                'native_account_overage_threshold_operator'         =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_operator,

                'report_template_account_group_overage_threshold_amount'           => null,
                'report_template_account_group_overage_threshold_amount_too_good'  => null,
                'report_template_account_group_overage_threshold_percent'          => null,
                'report_template_account_group_overage_threshold_percent_too_good' => null,
                'report_template_account_group_overage_threshold_operator'         => null,

                'calculated_field_overage_threshold_amount'           => null,
                'calculated_field_overage_threshold_amount_too_good'  => null,
                'calculated_field_overage_threshold_percent'          => null,
                'calculated_field_overage_threshold_percent_too_good' => null,
                'calculated_field_overage_threshold_operator'         => null,

                'qtr_monthly_month_1_budgeted'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_1_actual'           => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_1_variance'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_1_percent_variance' => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_monthly_month_2_budgeted'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_2_actual'           => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_2_variance'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_2_percent_variance' => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_monthly_month_3_budgeted'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_3_actual'           => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_3_variance'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_3_percent_variance' => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_forecast_month_1_budgeted'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_1_actual'           => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_1_variance'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_1_percent_variance' => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'qtr_forecast_month_2_budgeted'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_2_actual'           => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_2_variance'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_2_percent_variance' => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'qtr_forecast_month_3_budgeted'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_3_actual'           => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_3_variance'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_3_percent_variance' => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'line_item_coefficient' => $line_item_coefficient,
                'line_item_name'        => $line_item_name,
                'line_item_code'        => $line_item_code,

                'sort_order'                       => $ReportTemplateMappingObj->sort_order,
                'is_summary'                       => $ReportTemplateMappingObj->is_summary,
                'is_summary_tab_default_line_item' => $ReportTemplateMappingObj->is_summary_tab_default_line_item,
            ]
        );
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @param $AdvancedVarianceObj
     * @param $AdvancedVarianceLineItemObj
     * @param $NativeAccountAdvancedVarianceThresholdObj
     * @param $advanced_variance_default_native_coa_code
     * @return AdvancedVarianceLineItem
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function UpdateMonthlyAdvancedVarianceLineItem(
        $AdvancedVarianceObj,
        NativeAccount $NativeAccountObj,
        $AdvancedVarianceLineItemObj,
        $NativeAccountAdvancedVarianceThresholdObj,
        $advanced_variance_default_native_coa_code
    ) {

        $line_item_coefficient    = $NativeAccountObj->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $line_item_name           = $NativeAccountObj->native_account_name;
        $line_item_code           = $NativeAccountObj->native_account_code;
        $ReportTemplateMappingObj = $NativeAccountObj
            ->reportTemplateMappings->filter(
                function ($ReportTemplateMappingObj) use ($AdvancedVarianceObj)
                {
                    /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == $AdvancedVarianceObj->report_template_id;
                }
            )->first();

        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update(
            [
                'monthly_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'monthly_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'monthly_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'monthly_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'forecast_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'forecast_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'forecast_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'forecast_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'ytd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_budgeted'] ?: 0,
                'ytd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_actual'] ?: 0,
                'ytd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_variance'] ?: 0,
                'ytd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_percent_variance'] ?: 0,

                'qtd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_budgeted'] ?: 0,
                'qtd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_actual'] ?: 0,
                'qtd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_variance'] ?: 0,
                'qtd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_percent_variance'] ?: 0,

                'native_account_overage_threshold_amount'           =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount,
                'native_account_overage_threshold_amount_too_good'  =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount_too_good,
                'native_account_overage_threshold_percent'          =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent,
                'native_account_overage_threshold_percent_too_good' =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent_too_good,
                'native_account_overage_threshold_operator'         =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_operator,

                'qtr_monthly_month_1_budgeted'         => null,
                'qtr_monthly_month_1_actual'           => null,
                'qtr_monthly_month_1_variance'         => null,
                'qtr_monthly_month_1_percent_variance' => null,

                'qtr_monthly_month_2_budgeted'         => null,
                'qtr_monthly_month_2_actual'           => null,
                'qtr_monthly_month_2_variance'         => null,
                'qtr_monthly_month_2_percent_variance' => null,

                'qtr_monthly_month_3_budgeted'         => null,
                'qtr_monthly_month_3_actual'           => null,
                'qtr_monthly_month_3_variance'         => null,
                'qtr_monthly_month_3_percent_variance' => null,

                'qtr_forecast_month_1_budgeted'         => null,
                'qtr_forecast_month_1_actual'           => null,
                'qtr_forecast_month_1_variance'         => null,
                'qtr_forecast_month_1_percent_variance' => null,

                'qtr_forecast_month_2_budgeted'         => null,
                'qtr_forecast_month_2_actual'           => null,
                'qtr_forecast_month_2_variance'         => null,
                'qtr_forecast_month_2_percent_variance' => null,

                'qtr_forecast_month_3_budgeted'         => null,
                'qtr_forecast_month_3_actual'           => null,
                'qtr_forecast_month_3_variance'         => null,
                'qtr_forecast_month_3_percent_variance' => null,

                'line_item_coefficient' => $line_item_coefficient,
                'line_item_name'        => $line_item_name,
                'line_item_code'        => $line_item_code,

                'sort_order'                       => $ReportTemplateMappingObj->sort_order,
                'is_summary'                       => $ReportTemplateMappingObj->is_summary,
                'is_summary_tab_default_line_item' => $ReportTemplateMappingObj->is_summary_tab_default_line_item,
            ],
            $AdvancedVarianceLineItemObj->id
        );
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @param $AdvancedVarianceObj
     * @param $AdvancedVarianceLineItemObj
     * @param $NativeAccountAdvancedVarianceThresholdObj
     * @param $advanced_variance_default_native_coa_code
     * @return AdvancedVarianceLineItem
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function UpdateQuarterlyAdvancedVarianceLineItem(
        $AdvancedVarianceObj,
        NativeAccount $NativeAccountObj,
        $AdvancedVarianceLineItemObj,
        $NativeAccountAdvancedVarianceThresholdObj,
        $advanced_variance_default_native_coa_code
    ) {
        $line_item_coefficient    = $NativeAccountObj->getCoeffients($AdvancedVarianceObj->property_id)->advanced_variance_coefficient;
        $line_item_name           = $NativeAccountObj->native_account_name;
        $line_item_code           = $NativeAccountObj->native_account_code;
        $ReportTemplateMappingObj = $NativeAccountObj
            ->reportTemplateMappings->filter(
                function ($ReportTemplateMappingObj) use ($AdvancedVarianceObj)
                {
                    /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                    return $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_id == $AdvancedVarianceObj->report_template_id;
                }
            )->first();

        $this->AdvancedVarianceLineItemRepositoryObj->setSuppressEvents(true);
        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update(
            [
                'monthly_budgeted'         => null,
                'monthly_actual'           => null,
                'monthly_variance'         => null,
                'monthly_percent_variance' => null,

                'forecast_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'forecast_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'forecast_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'forecast_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'ytd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_budgeted'] ?: 0,
                'ytd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_actual'] ?: 0,
                'ytd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_variance'] ?: 0,
                'ytd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['ytd_percent_variance'] ?: 0,

                'qtd_budgeted'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_budgeted'] ?: 0,
                'qtd_actual'           => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_actual'] ?: 0,
                'qtd_variance'         => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_variance'] ?: 0,
                'qtd_percent_variance' => $this->LedgerVarianceDataArr[$advanced_variance_default_native_coa_code]['qtd_percent_variance'] ?: 0,

                'qtr_monthly_month_1_budgeted'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_1_actual'           => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_1_variance'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_1_percent_variance' => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_monthly_month_2_budgeted'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_2_actual'           => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_2_variance'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_2_percent_variance' => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_monthly_month_3_budgeted'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_budgeted'] ?: 0,
                'qtr_monthly_month_3_actual'           => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_actual'] ?: 0,
                'qtr_monthly_month_3_variance'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_variance'] ?: 0,
                'qtr_monthly_month_3_percent_variance' => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['monthly_percent_variance'] ?: 0,

                'qtr_forecast_month_1_budgeted'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_1_actual'           => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_1_variance'         => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_1_percent_variance' => $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'qtr_forecast_month_2_budgeted'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_2_actual'           => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_2_variance'         => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_2_percent_variance' => $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'qtr_forecast_month_3_budgeted'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_budgeted'] ?: 0,
                'qtr_forecast_month_3_actual'           => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_actual'] ?: 0,
                'qtr_forecast_month_3_variance'         => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_variance'] ?: 0,
                'qtr_forecast_month_3_percent_variance' => $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code]['forecast_percent_variance'] ?: 0,

                'native_account_overage_threshold_amount'           =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount,
                'native_account_overage_threshold_amount_too_good'  =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_amount_too_good,
                'native_account_overage_threshold_percent'          =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent,
                'native_account_overage_threshold_percent_too_good' =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_percent_too_good,
                'native_account_overage_threshold_operator'         =>
                    $NativeAccountAdvancedVarianceThresholdObj->native_account_overage_threshold_operator,

                'line_item_coefficient' => $line_item_coefficient,
                'line_item_name'        => $line_item_name,
                'line_item_code'        => $line_item_code,

                'sort_order'                       => $ReportTemplateMappingObj->sort_order,
                'is_summary'                       => $ReportTemplateMappingObj->is_summary,
                'is_summary_tab_default_line_item' => $ReportTemplateMappingObj->is_summary_tab_default_line_item,
            ],
            $AdvancedVarianceLineItemObj->id
        );
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function refresh_reviewers(AdvancedVariance $AdvancedVarianceObj)
    {
        /**
         * merge $PropertyObj->client->getConfigValue('ADVANCED_VARIANCE_REVIEWERS') and $PropertyObj->getConfigValue('ADVANCED_VARIANCE_REVIEWERS')
         */
        $all_reviewer_emails = [];
        if ($AdvancedVarianceObj->property->client->getConfigValue('ADVANCED_VARIANCE_REVIEWERS'))
        {
            $all_reviewer_emails = array_merge($all_reviewer_emails, $AdvancedVarianceObj->property->client->getConfigValue('ADVANCED_VARIANCE_REVIEWERS'));
        }
        if ($AdvancedVarianceObj->property->getConfigValue('ADVANCED_VARIANCE_REVIEWERS'))
        {
            $all_reviewer_emails = array_merge($all_reviewer_emails, $AdvancedVarianceObj->property->getConfigValue('ADVANCED_VARIANCE_REVIEWERS'));
        }
        $all_reviewer_emails = array_unique($all_reviewer_emails);

        /**
         * just in case, let's check, gotta have a reviewer and gotta have a threshold
         */
        if ( ! count($all_reviewer_emails) ||
             ! $AdvancedVarianceObj->property->client->getConfigValue('ADVANCED_VARIANCE_THRESHOLD_MODE')
        )
        {
            throw new GeneralException('Client nor properly configured for advanced variances - client ' . $AdvancedVarianceObj->property->client->name . ' at ' . __CLASS__ . __LINE__);
        }

        /**
         * Add reviewers just in case user were added to config since first run of this report
         */
        foreach ($all_reviewer_emails as $reviewer_user_email)
        {
            $criteria = [
                'email'         => $reviewer_user_email,
                'client_id'     => $AdvancedVarianceObj->property->client_id,
                'active_status' => User::ACTIVE_STATUS_ACTIVE,
            ];
            if ( ! $UserObj = $this->UserRepositoryObj->findWhere($criteria)->first())
            {
                continue;
            }

            /**
             * no waypoint employees allowed
             */
            if ($UserObj->is_hidden)
            {
                continue;
            }
            /** @var User $UserObj */
            if (
            ! $UserObj->canAccessProperty($AdvancedVarianceObj->property_id)
            )
            {
                continue;
            }
            $AdvancedVarianceObj->add_reviewer($UserObj->id);
        }
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return AdvancedVariance
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        if (isset($attributes['advanced_variance_status']))
        {
            if ($attributes['advanced_variance_status'] == AdvancedVariance::ACTIVE_STATUS_UNLOCKED)
            {
                $attributes['locker_user_id'] = null;
                $attributes['locked_date']    = null;
            }
            elseif ($attributes['advanced_variance_status'] == AdvancedVariance::ACTIVE_STATUS_LOCKED)
            {
                if (
                    ! isset($attributes['locker_user_id']) ||
                    ! $attributes['locker_user_id']
                )
                {
                    throw new GeneralException('locker_user_id is required', 403);
                }

                $attributes['locked_date'] = Carbon::now()->format('Y-m-d H:i:s');
            }
            else
            {
                throw new GeneralException('invalid advanced_variance_status detected', 403);
            }
            if (count($attributes) != 3)
            {
                throw new GeneralException('too many parameters detected', 403);
            }
        }

        /**
         * if one is passed in, both are required
         */
        if (
            isset($attributes['advanced_variance_explanation_type_id']) ||
            isset($attributes['explanation_type_user_id'])
        )
        {
            if ( !
            (
                isset($attributes['advanced_variance_explanation_type_id']) &&
                $attributes['advanced_variance_explanation_type_id'] &&
                isset($attributes['explanation_type_user_id']) &&
                $attributes['explanation_type_user_id']
            )
            )
            {
                throw new GeneralException('advanced_variance_explanation_type_id and explanation_type_user_id are required', 403);
            }

            $attributes['explanation_type_date'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        if (
            isset($attributes['advanced_variance_status']) &&
            $attributes['advanced_variance_status'] == AdvancedVariance::ACTIVE_STATUS_LOCKED
        )
        {
            $AdvancedVariancesObj = $this->find($id);
            /**
             * check if allowed to lock
             */
            if ($AdvancedVariancesObj->advanced_variance_status == AdvancedVariance::ACTIVE_STATUS_LOCKED)
            {
                return $AdvancedVariancesObj;
            }

            $results = DB::select(
                DB::raw(
                    "SELECT
                        count(*) AS advanced_variance_line_item_count
                        FROM advanced_variances

                        JOIN advanced_variance_line_items ON advanced_variance_line_items.advanced_variance_id = advanced_variances.id
                        WHERE
                            advanced_variances.id = :ADVANCED_VARIANCES_ID AND
                            (
                                advanced_variance_line_items.flagged_via_policy = TRUE OR
                                advanced_variance_line_items.flagged_manually = TRUE
                            ) AND
                            advanced_variance_line_items.resolver_user_id IS NULL
                "
                ),
                [
                    'ADVANCED_VARIANCES_ID' => $id,
                ]
            );
            if ($results[0]->advanced_variance_line_item_count > 0)
            {
                throw new GeneralException('You may not lock an advanced variance if it has a line item that is flagged, unexplained and unresolved', 403);
            }
        }

        $AdvancedVarianceObj = parent::update($attributes, $id);

        Cache::tags('AdvancedVariance_' . $AdvancedVarianceObj->property->client_id)->flush();
        return $AdvancedVarianceObj;
    }

    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVariance::class;
    }

    /**
     * @param integer $property_id
     * @return array
     */
    public function get_native_account_code_hash($property_id)
    {
        $return_me = [];
        $results   = DB::select(
            DB::raw(
                "SELECT
                    native_accounts.id AS id ,
                    native_accounts.native_account_code AS native_account_code
                    FROM native_accounts

                    JOIN native_coas          ON native_accounts.native_coa_id = native_coas.id
                    JOIN property_native_coas ON property_native_coas.native_coa_id = native_coas.id
                    WHERE
                        property_native_coas.property_id = :PROPERTY_ID

                "
            ),
            [
                'PROPERTY_ID' => $property_id,
            ]
        );

        foreach ($results as $result)
        {
            $return_me[$result->native_account_code] = $result->id;
        }
        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @param integer $user_id
     * @param integer $property_id
     * @return bool
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function add_reviewer($advanced_variance_id, $user_id, $property_id)
    {
        $CandidateUserObj = $this->UserRepositoryObj->find($user_id);

        /**
         * no waypoint employees allowed
         */
        if ($CandidateUserObj->is_hidden)
        {
            return false;
        }

        /** @var RelatedUserTypeRepository $RelatedUserTypeRepositoryObj */
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        if ( ! $RelatedUserTypeObj = $RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'              => $CandidateUserObj->client_id,
                'related_object_type'    => AdvancedVariance::class,
                'related_object_subtype' => AdvancedVariance::REVIEWER,
            ]
        )->first())
        {
            throw new GeneralException('No RelatedUserTypeObj', 500);
        }

        if ( ! $this->RelatedUserRepositoryObj->findWhere(
            [
                'user_id'              => $CandidateUserObj->id,
                'related_object_id'    => $advanced_variance_id,
                'related_user_type_id' => $RelatedUserTypeObj->id,
            ]
        )->first())
        {
            $this->RelatedUserRepositoryObj->create(
                [
                    'user_id'              => $CandidateUserObj->id,
                    'related_object_id'    => $advanced_variance_id,
                    'related_user_type_id' => $RelatedUserTypeObj->id,
                ]
            );
        }
    }

    /**
     * @param integer $advanced_variance_id
     * @param integer $user_id
     * @return bool
     * @throws GeneralException
     *
     * @todo push this into relatedUserTrait
     */
    public function remove_reviewer($advanced_variance_id, $user_id)
    {
        if ( ! $CandidateUserObj = $this->UserRepositoryObj->find($user_id))
        {
            return false;
        }

        /** @var RelatedUserTypeRepository $RelatedUserTypeRepositoryObj */
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        if ( ! $RelatedUserTypeObj = $RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'              => $CandidateUserObj->client_id,
                'related_object_type'    => AdvancedVariance::class,
                'related_object_subtype' => AdvancedVariance::REVIEWER,
            ]
        )->first())
        {
            throw new GeneralException('No RelatedUserTypeObj', 500);
        }

        if ($RelatedUserObj = $this->RelatedUserRepositoryObj->findWhere(
            [
                'user_id'              => $CandidateUserObj->id,
                'related_object_id'    => $advanced_variance_id,
                'related_user_type_id' => $RelatedUserTypeObj->id,
            ]
        )->first())
        {
            $this->RelatedUserRepositoryObj->delete($RelatedUserObj->id);
            return true;
        }
        throw new GeneralException('user not related');
    }

    /**
     * @param $value_name
     * @param integer $property_id
     * @return mixed
     * @throws GeneralException
     */
    public function get_conf_values($value_name, $property_id)
    {
        $ClientConfigObj = $this->getClientConfigObj($property_id);
        if (isset($ClientConfigObj->$value_name))
        {
            if (
                is_numeric($ClientConfigObj->$value_name) ||
                is_string($ClientConfigObj->$value_name)
            )
            {
                return $ClientConfigObj->$value_name;
            }
            return (array) $ClientConfigObj->$value_name;
        }
        throw new GeneralException('Cannot get_conf_values for value ' . $value_name, 500);
    }

    /**
     * @return mixed
     */
    public function getClientConfigObj($property_id)
    {
        return $this->PropertyRepositoryObj->find($property_id)->client->getConfigJSON();
    }

    /**
     * @param array $advanced_variance_default_native_coa_codes_arr
     * @param int $property_id
     * @param int $as_of_month
     * @param int $as_of_year
     * @param $quarterly
     * @return array
     * @throws ModelNotFoundException
     */
    public function getLedgerVarianceDataArr(array $advanced_variance_default_native_coa_codes_arr, int $property_id, int $as_of_month, int $as_of_year, $quarterly): array
    {
        if ( ! $PropertyObj = $this->PropertyRepositoryObj->find($property_id))
        {
            throw new ModelNotFoundException('no such property');
        }

        $minutes   = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key       = "getLedgerVarianceDataArr_property_id=" . $PropertyObj->id . '_as_of_month=' . $as_of_month . '_as_of_year' . $as_of_year . '_quarterly' . $quarterly . md5(json_encode($advanced_variance_default_native_coa_codes_arr)) . '_' . md5(__FILE__ . __LINE__);
        $return_me =
            Cache::tags([
                            'AdvancedVariance_' . $PropertyObj->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($advanced_variance_default_native_coa_codes_arr, $property_id, $as_of_month, $as_of_year, $quarterly)
                     {
                         try
                         {
                             $AsOfMonthYearObj          = Carbon::create($as_of_year, $as_of_month, 1, 0, 0, 0);
                             $ledger_native_account_arr = self::getNativeCoaLedgerRepositoryObj()->getLedgerNativeAccounts(
                                 $property_id,
                                 $advanced_variance_default_native_coa_codes_arr,
                                 $AsOfMonthYearObj,
                                 ($quarterly == AdvancedVariance::PERIOD_TYPE_QUARTERLY ? true : false)
                             );

                             $return_me = [];
                             foreach ($ledger_native_account_arr as $ledger_native_account)
                             {
                                 if (in_array($ledger_native_account['native_code'], $advanced_variance_default_native_coa_codes_arr))
                                 {
                                     $return_me[$ledger_native_account['native_code']] = $ledger_native_account;
                                 }
                             }
                         }
                         catch (GeneralException $e)
                         {
                             throw $e;
                         }
                         catch (Exception $e)
                         {
                             throw new GeneralException('Call to NativeCoaLedgerRepository failed - property_id = ' . $property_id, 500, $e);
                         }

                         return $return_me;
                     }
                 );

        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @return array
     */
    public function getFlaggedAdvancedVarianceLineItems($advanced_variance_id)
    {
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);

        $return_me = [];
        $results   = DB::select(
            DB::raw(
                "SELECT
                    advanced_variance_line_items.id
                    FROM advanced_variances

                    JOIN advanced_variance_line_items ON advanced_variance_line_items.advanced_variance_id = advanced_variances.id
                    WHERE
                        advanced_variances.id = :ADVANCED_VARIANCE_ID AND
                        (
                          advanced_variance_line_items.flagged_manually = TRUE OR
                          advanced_variance_line_items.flagged_via_policy = TRUE
                        )
                "
            ),
            [
                'ADVANCED_VARIANCE_ID' => $advanced_variance_id,
            ]
        );

        foreach ($results as $result)
        {
            $return_me[] = $this->AdvancedVarianceLineItemRepositoryObj->find($result->id);
        }
        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @return array
     */
    public function getManuallyFlaggedAdvancedVarianceLineItems($advanced_variance_id)
    {
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);

        $return_me = [];
        $results   = DB::select(
            DB::raw(
                "SELECT
                    advanced_variance_line_items.id
                    FROM advanced_variances

                    JOIN advanced_variance_line_items ON advanced_variance_line_items.advanced_variance_id = advanced_variances.id
                    WHERE
                        advanced_variances.id = :ADVANCED_VARIANCE_ID AND
                        advanced_variance_line_items.flagged_manually = TRUE
                "
            ),
            [
                'ADVANCED_VARIANCE_ID' => $advanced_variance_id,
            ]
        );

        foreach ($results as $result)
        {
            $return_me[] = $this->AdvancedVarianceLineItemRepositoryObj->find($result->id);
        }
        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @return array
     */
    public function getFlaggedByPolicyAdvancedVarianceLineItems($advanced_variance_id)
    {
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);

        $return_me = [];
        $results   = DB::select(
            DB::raw(
                "SELECT
                    advanced_variance_line_items.id
                    FROM advanced_variances

                    JOIN advanced_variance_line_items ON advanced_variance_line_items.advanced_variance_id = advanced_variances.id
                    WHERE
                        advanced_variances.id = :ADVANCED_VARIANCE_ID AND
                        advanced_variance_line_items.flagged_via_policy = TRUE
                "
            ),
            [
                'ADVANCED_VARIANCE_ID' => $advanced_variance_id,
            ]
        );

        foreach ($results as $result)
        {
            $return_me[] = $this->AdvancedVarianceLineItemRepositoryObj->find($result->id);
        }
        return $return_me;
    }

    /**
     * @return NativeCoaLedgerRepository|NativeCoaLedgerMockRepository
     */
    public static function getNativeCoaLedgerRepositoryObj()
    {
        if ( ! self::$NativeCoaLedgerRepositoryObj)
        {
            self::$NativeCoaLedgerRepositoryObj = App::make(NativeCoaLedgerRepository::class);
        }
        return self::$NativeCoaLedgerRepositoryObj;
    }

    /**
     * @param NativeCoaLedgerRepository|NativeCoaLedgerMockRepository $NativeCoaLedgerRepositoryObj
     */
    public static function setNativeCoaLedgerRepositoryObj($NativeCoaLedgerRepositoryObj)
    {
        self::$NativeCoaLedgerRepositoryObj = $NativeCoaLedgerRepositoryObj;
    }

    /**
     * @param integer $client_id
     * @return Collection
     */
    public function getAdvancedVariancesWithClientId($client_id)
    {
        $results                  = DB::select(
            DB::raw(
                "SELECT
                    advanced_variances.id
                    FROM advanced_variances
                    JOIN properties ON advanced_variances.property_id = properties.id
                    WHERE
                        properties.client_id = :CLIENT_ID
                "
            ),
            [
                'CLIENT_ID' => $client_id,
            ]
        );
        $advanced_variance_id_arr = array_map(function ($result) { return $result->id; }, $results);
        $return_me                = $this->findWhereIn('id', $advanced_variance_id_arr);
        return $return_me;
    }

    /**
     * @return bool
     */
    protected function detect_non_zero()
    {
        foreach ($this->LedgerVarianceDataArr as $code => $amts)
        {
            if (
                $amts['monthly_budgeted'] != 0 ||
                $amts['monthly_actual'] != 0 ||
                $amts['ytd_budgeted'] != 0 ||
                $amts['ytd_actual'] != 0 ||
                $amts['qtd_budgeted'] != 0 ||
                $amts['qtd_actual'] != 0
            )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $LedgerVarianceDataArr
     */
    public function setLedgerVarianceDataArr(array $LedgerVarianceDataArr): void
    {
        $this->LedgerVarianceDataArr = $LedgerVarianceDataArr;
    }

    /**
     * @param $advanced_variance_default_native_coa_code
     */
    public function init_quarterly_details($advanced_variance_default_native_coa_code): void
    {
        $index_list_arr = [
            'monthly_budgeted',
            'monthly_actual',
            'monthly_variance',
            'monthly_percent_variance',

            'forecast_budgeted',
            'forecast_actual',
            'forecast_variance',
            'forecast_percent_variance',

            'ytd_budgeted',
            'ytd_actual',
            'ytd_variance',
            'ytd_percent_variance',

            'qtd_budgeted',
            'qtd_actual',
            'qtd_variance',
            'qtd_percent_variance',
        ];
        foreach ($index_list_arr as $label)
        {
            if ( ! isset($this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code][$label]))
            {
                $this->LedgerVarianceDataMonth1Arr[$advanced_variance_default_native_coa_code][$label] = null;
            }
            if ( ! isset($this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code][$label]))
            {
                $this->LedgerVarianceDataMonth2Arr[$advanced_variance_default_native_coa_code][$label] = null;
            }
            if ( ! isset($this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code][$label]))
            {
                $this->LedgerVarianceDataMonth3Arr[$advanced_variance_default_native_coa_code][$label] = null;
            }
        }
    }

    /**
     * @param integer $client_id
     * @param null $property_id_arr
     * @return Collection
     */
    public function get_unique_advanced_variance_dates($client_id, $property_id_arr = null)
    {
        $return_me = [];
        if ($property_id_arr)
        {
            $results = DB::select(
                DB::raw(
                    "SELECT
                        advanced_variances.as_of_month, advanced_variances.as_of_year
                    FROM advanced_variances
                    WHERE
                        advanced_variances.property_id in( :PROPERTY_ID_ARR )
                    GROUP BY advanced_variances.as_of_month, advanced_variances.as_of_year
                "
                ),
                [
                    'PROPERTY_ID_ARR' => $property_id_arr,
                ]
            );
        }
        else
        {
            $results = DB::select(
                DB::raw(
                    "SELECT
                        advanced_variances.as_of_month, as_of_year
                    FROM advanced_variances
                    JOIN properties ON advanced_variances.property_id = properties.id
                    WHERE
                        properties.client_id = :CLIENT_ID
                    GROUP BY advanced_variances.as_of_month, advanced_variances.as_of_year
                "
                ),
                [
                    'CLIENT_ID' => $client_id,
                ]
            );
        }
        foreach ($results as $result)
        {
            $return_me[] = ['as_of_month' => $result->as_of_month, 'as_of_year' => $result->as_of_year];
        }
        return collect_waypoint($return_me);
    }

    /**
     * @param integer $client_id
     * @return Collection
     */
    public function get_unique_advanced_variance_dates_client($client_id)
    {
        $return_me = [];

        $results = DB::select(
            DB::raw(
                "SELECT
                        advanced_variances.as_of_month, as_of_year
                    FROM advanced_variances
                    JOIN properties ON advanced_variances.property_id = properties.id
                    WHERE
                        properties.client_id = :CLIENT_ID
                    GROUP BY advanced_variances.as_of_month, advanced_variances.as_of_year
                "
            ),
            [
                'CLIENT_ID' => $client_id,
            ]
        );

        foreach ($results as $result)
        {
            $return_me[] = ['as_of_month' => $result->as_of_month, 'as_of_year' => $result->as_of_year];
        }
        return collect_waypoint($return_me);
    }

    /**
     * @param integer $client_id
     * @param $property_id_arr
     * @return Collection
     */
    public function get_unique_advanced_variance_dates_properties($client_id = null, $property_id_arr = [])
    {
        $return_me = [];
        $results   = [];
        if ($property_id_arr)
        {
            $results = DB::select(
                DB::raw(
                    "
                        SELECT
                            advanced_variances.as_of_month, advanced_variances.as_of_year
                        FROM advanced_variances
                        WHERE
                            advanced_variances.property_id in( " . $property_id_arr . " )
                        GROUP BY advanced_variances.as_of_month, advanced_variances.as_of_year
                    "
                )
            );
        }
        foreach ($results as $result)
        {
            $return_me[] = ['as_of_month' => $result->as_of_month, 'as_of_year' => $result->as_of_year];
        }
        return collect_waypoint($return_me);
    }

    /**
     * @param integer $client_id
     * @param $property_group_id
     * @return Collection
     * @throws GeneralException
     */
    public function get_unique_advanced_variance_dates_property_group($client_id, $property_group_id)
    {
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $PropertyGroupRepositoryObj
            ->with('properties')
            ->find($property_group_id);

        $results = [];
        if ($all_property_ids = $PropertyGroupObj->properties->pluck('id')->toArray())
        {
            $results = DB::select(
                DB::raw(
                    "SELECT
                            advanced_variances.as_of_month, advanced_variances.as_of_year
                        FROM advanced_variances
                        WHERE
                            advanced_variances.property_id in( " . implode(',', $all_property_ids) . " )
                        GROUP BY advanced_variances.as_of_month, advanced_variances.as_of_year
                    "
                )
            );
        }
        $return_me = [];
        foreach ($results as $result)
        {
            $return_me[] = ['as_of_month' => $result->as_of_month, 'as_of_year' => $result->as_of_year];
        }
        self::$NativeCoaLedgerRepositoryObj = null;
        return collect_waypoint($return_me);
    }

    /**
     * @param $advanced_variance_id
     * @param integer $property_id
     * @param $as_of_year
     * @param $as_of_month
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get_hydrated_advanced_variance($advanced_variance_id = null, $property_id = null, $as_of_year = null, $as_of_month = null)
    {
        if ($advanced_variance_id)
        {
            $findWhereArr = ['id' => $advanced_variance_id];
        }
        else
        {
            $findWhereArr = [
                'advanced_variance_start_date' => Carbon::create($as_of_year, $as_of_month, 1, 0, 0, 0)->format('Y-m-d H:i:s'),
                'property_id'                  => $property_id,
            ];
        }

        $AdvancedVarianceObj =
            $this
                ->with('advancedVarianceLineItems.advancedVariance')
                ->with('advancedVarianceLineItems.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems.reportTemplateAccountGroup')
                ->with('reportTemplate.calculatedFields')
                ->with('reportTemplate.reportTemplateAccountGroups.nativeAccounts')
                ->with('advancedVarianceApprovals')
                ->with('advancedVarianceLineItems.calculatedField.calculatedFieldEquations')
                ->with('advancedVarianceLineItems.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems.nativeAccount.reportTemplateMappings.reportTemplateAccountGroup')
                ->with('advancedVarianceLineItems.reportTemplateAccountGroup.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->with('advancedVarianceLineItems.reportTemplateAccountGroup.nativeAccountType.nativeAccountTypeTrailers')
                ->with('lockerUser')
                ->with('property')
                ->with('reportTemplate.reportTemplateAccountGroups.reportTemplateMappings.nativeAccount')
                ->findWhere(
                    $findWhereArr
                )->first();
        return $AdvancedVarianceObj;
    }

    /**
     * @param Client $ClientObj
     * @return array
     */
    function get_scheduled_advanced_variance_parameters(Client $ClientObj)
    {
        $client_config_json = $ClientObj->getConfigJson(true);

        $property_schedules = [];
        foreach ($ClientObj->properties as $PropertyObj)
        {
            $property_config_json       = $PropertyObj->getConfigJson(true);
            $advanced_variance_schedule = $property_config_json['ADVANCED_VARIANCE_SCHEDULE'] ?? $client_config_json['ADVANCED_VARIANCE_SCHEDULE'] ?? 'none';

            if (in_array($advanced_variance_schedule,
                         [
                             AdvancedVariance::PERIOD_TYPE_MONTHLY,
                             AdvancedVariance::PERIOD_TYPE_QUARTERLY,
                         ]
            )
            )
            {
                // Manually strip out trailing spaces because MySQL ignores
                // them anyway and we need our keys to be consistent.
                $property_schedules[rtrim($PropertyObj->property_code, " ")] = [
                    'property' => $PropertyObj,
                    'schedule' => $advanced_variance_schedule,
                ];
            }
            elseif ($advanced_variance_schedule !== 'none')
            {
                throw new GeneralException(
                    "Unexpected ADVANCED_VARIANCE_SCHEDULE value for property " . $PropertyObj->id . ": " . $advanced_variance_schedule
                );
            }
        }

        // Don't try to connect to the staging DB if not necessary.
        // Old/test clients may not have the TARGET_ASOF_MONTH table.
        if ( ! $property_schedules)
        {
            return [];
        }

        $staging_database = DatabaseConnectionRepository::getStagingDatabaseConnection($ClientObj);
        $results          = $staging_database
            ->table('TARGET_ASOF_MONTH')
            ->select(['PROPERTY_CODE', 'YEARMONTH'])
            ->whereIn('PROPERTY_CODE', array_keys($property_schedules))
            ->get();

        $advanced_variance_parameters_array = [];
        foreach ($results as $result)
        {
            // YEARMONTH is stored as one int in the DB
            $as_of_year  = intdiv($result->YEARMONTH, 100);
            $as_of_month = $result->YEARMONTH % 100;

            $property_code = rtrim($result->PROPERTY_CODE, " ");
            $schedule      = $property_schedules[$property_code]['schedule'];
            $PropertyObj   = $property_schedules[$property_code]['property'];
            if (
                $schedule === AdvancedVariance::PERIOD_TYPE_QUARTERLY
                && ! in_array($as_of_month, [3, 6, 9, 12])
            )
            {
                continue;
            }

            // Selectively hydrate advanced variances, so $PropertyObj->advancedVariances->count() is always 1 or 0
            $PropertyObj->load([
                                   'advancedVariances' => function ($query) use ($as_of_month, $as_of_year)
                                   {
                                       $query->where('as_of_month', $as_of_month)->where('as_of_year', $as_of_year);
                                   },
                               ]);
            if ($PropertyObj->advancedVariances->count() === 0)
            {
                $advanced_variance_parameters_array[] = [
                    'client_id'   => $ClientObj->id,
                    'property_id' => $PropertyObj->id,
                    'as_of_year'  => $as_of_year,
                    'as_of_month' => $as_of_month,
                ];
            }
        }

        return $advanced_variance_parameters_array;
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerCreatedEvent($AdvancedVarianceObj)
    {
        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            event(
                new \App\Waypoint\Events\AdvancedVarianceCreatedEvent(
                    $AdvancedVarianceObj,
                    [
                        'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceObj)
    {
        /**
         * Check if there are events set up for this model.
         */
        if (
        $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            event(
                new \App\Waypoint\Events\AdvancedVarianceUpdatedEvent(
                    $AdvancedVarianceObj,
                    [
                        'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerDeletedEvent($AdvancedVarianceObj)
    {
        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            event(
                new \App\Waypoint\Events\AdvancedVarianceDeletedEvent(
                    $AdvancedVarianceObj,
                    [
                        'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                        'wipe_out_list'                =>
                            [
                                'properties' =>
                                    [
                                        '^advancedVarianceSummaries_property_.*',

                                    ],

                                'property_groups' =>
                                    [
                                        '^AdvancedVarianceSummaryByPropertyGroupId_.*',
                                        '^unique_advanced_variance_dates_property_group_.*',

                                    ],
                            ],
                    ]
                )
            );
        }
    }

    /**
     * Delete a AdvancedVariance entity in repository by id
     *
     * @param int $advanced_variance_id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($advanced_variance_id)
    {
        $AdvancedVarianceObj = $this->find($advanced_variance_id);
        $result              = parent::delete($advanced_variance_id);
        Cache::tags('AdvancedVariance_' . $AdvancedVarianceObj->property->client_id)->flush();

        return $result;
    }
}
