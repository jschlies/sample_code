<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class NativeChartActualBudgetVarianceOverTimeController
 */
class ActualBudgetVarianceOverTimeController extends NativeChartAmountController
{
    /**
     * @param integer $client_id
     * @param $property_id
     * @param $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForProperty($client_id, $property_id, $report_template_account_group_id, Request $request)
    {
        /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);

        /**
         * @var Carbon $RequestedFromDateObj
         * @var Carbon $RequestedToDateObj
         * @var Carbon $BenchmarkGenerationDateObj
         */
        try
        {
            /**
             * I wish this we're better
             */
            list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj) =
                $this->processInputForNativeChartAmountController($client_id, $request->all());
        }
        catch (GeneralException $e)
        {
            if (preg_match("/RequestedFromDate/", $e->getMessage()))
            {
                return $this->sendResponse(null, $e->getMessage(), null, null, []);
            }
        }

        $overtime_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->find($report_template_account_group_id);

        $native_accounts_id_arr = $ReportTemplateAccountGroupObj->get_native_account_id_arr();
        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_accounts_id_arr;

        $PropertyInQuestionObj = $PropertyRepositoryObj
            ->with('nativeAccountAmountsFiltered')
            ->find($property_id);

        /**
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated.
         *
         * remember that nativeAccountAmountsFiltered is filtered by '
         * Property::$nativeAccountAmountsFilteredFromDate
         * Property::$nativeAccountAmountsFilteredToDate and
         * Property::$nativeAccountAmountsFilteredNativeAccountIds
         */
        /** @var Collection $PropertyObjArr */
        $PropertyObjArr = collect_waypoint([$PropertyInQuestionObj]);

        $overtime_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceOvertime',
            $input,
            $BenchmarkGenerationDateObj,
            $property_id,
            null,
            $ReportTemplateAccountGroupObj,
            null,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        /**
         * anything???????
         */
        if ( ! $PropertyInQuestionObj
            ->nativeAccountAmountsFiltered->filter(
                function (NativeAccountAmount $NativeAccountAmountObj) use ($RequestedFromDateObj, $RequestedToDateObj, $ReportTemplateAccountGroupObj, $native_accounts_id_arr)
                {
                    return
                        in_array($NativeAccountAmountObj->native_account_id, $native_accounts_id_arr) &&
                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($RequestedFromDateObj) &&
                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($RequestedToDateObj);
                }
            )->count($RequestedFromDateObj . $RequestedToDateObj))
        {
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $overtime_arr['metadata']);
        };

        $overtime_arr['data'] = [];
        /** @var Carbon $LoopFromDateObj */
        $LoopFromDateObj = clone $RequestedFromDateObj;
        $LoopToDateObj   = clone $LoopFromDateObj;
        $LoopToDateObj->endOfMonth();

        while ($LoopFromDateObj->lessThanOrEqualTo($RequestedToDateObj))
        {
            $month_element = [];

            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            list($budget, $actual, $ActualToDateObj) =
                $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                    $PropertyObjArr,
                    [$property_id],
                    $native_accounts_id_arr,
                    $LoopFromDateObj,
                    $LoopToDateObj
                );
            $month_element['month']              = $LoopFromDateObj->format('m');
            $month_element['year']               = $LoopFromDateObj->format('Y');
            $month_element['actual']             = null;
            $month_element['budget']             = null;
            $month_element['variance']           = null;
            $month_element['variancePercentage'] = null;

            if ($ActualToDateObj && $budget && $actual)
            {
                $month_element['actual']             = $actual;
                $month_element['budget']             = $budget;
                $month_element['variance']           = $actual - $budget;
                $month_element['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;
            }
            $overtime_arr['data'][$LoopFromDateObj->format('m') . '_' . $LoopFromDateObj->format('Y')] = $month_element;

            $LoopFromDateObj->addMonth(1);
            $LoopToDateObj = clone $LoopFromDateObj;
            $LoopToDateObj->endOfMonth();
        }

        return $this->sendResponse($overtime_arr['data'], 'ActualBudgetVarianceOvertime(s) retrieved successfully', null, null, $overtime_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param $property_id
     * @param integer $calculated_field_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getCalculatedFieldActualBudgetVarianceOverTimeForProperty($client_id, $property_id, $calculated_field_id, Request $request)
    {
        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        /** @var CalculatedFieldRepository $CalculatedFieldRepositoryObj */
        $CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);

        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $CalculatedFieldRepositoryObj
            ->with('calculatedFieldEquations.calculatedFieldVariables.reportTemplateAccountGroup.nativeAccounts')
            ->with('calculatedFieldEquations.calculatedFieldVariables.reportTemplateAccountGroup.reportTemplateAccountGroupChildren.nativeAccounts')
            ->with('calculatedFieldEquations.calculatedFieldVariables.reportTemplateAccountGroup.reportTemplateAccountGroupChildren.reportTemplateAccountGroupChildren.nativeAccounts')
            ->with('calculatedFieldEquations.properties')
            ->find($calculated_field_id);

        /**
         * @var Carbon $RequestedFromDateObj
         * @var Carbon $RequestedToDateObj
         * @var Carbon $BenchmarkGenerationDateObj
         */
        list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj) =
            $this->processInputForNativeChartAmountController($client_id, $request->all());

        $overtime_arr = [];

        $overtime_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceOvertime',
            $input,
            $BenchmarkGenerationDateObj,
            $property_id,
            null,
            null,
            $CalculatedFieldObj,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        $native_account_id_arr = $CalculatedFieldObj->get_native_account_id_arr();
        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_account_id_arr;

        /** @var Property $PropertyInQuestionObj */
        $PropertyInQuestionObj = $PropertyRepositoryObj
            ->with('nativeAccountAmountsFiltered')
            ->find($property_id);

        /**
         * anything???????
         */
        if ( ! $PropertyInQuestionObj
            ->nativeAccountAmountsFiltered
            ->filter(
                function (NativeAccountAmount $NativeAccountAmountObj) use ($RequestedFromDateObj, $RequestedToDateObj, $native_account_id_arr)
                {
                    return
                        in_array($NativeAccountAmountObj->native_account_id, $native_account_id_arr) &&
                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($RequestedFromDateObj) &&
                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($RequestedToDateObj);
                }
            )
            ->count($RequestedFromDateObj . $RequestedToDateObj))
        {
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $overtime_arr['metadata']);
        };
        /**
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated.
         *
         * remember that nativeAccountAmountsFiltered is filtered by '
         * Property::$nativeAccountAmountsFilteredFromDate
         * Property::$nativeAccountAmountsFilteredToDate and
         * Property::$nativeAccountAmountsFilteredNativeAccountIds
         */
        /** @var Collection $PropertyObjArr */
        $PropertyObjArr = collect_waypoint([$PropertyInQuestionObj]);

        $overtime_arr['data'] = [];

        /** @var Carbon $LoopFromDateObj */
        $LoopFromDateObj = clone $RequestedFromDateObj;
        $LoopToDateObj   = clone $LoopFromDateObj;
        $LoopToDateObj->endOfMonth();

        while ($LoopFromDateObj->lessThanOrEqualTo($RequestedToDateObj))
        {
            $month_element = [];
            list($budget, $actual) =
                $this->processTotalsForPropertyArrCalculatedField(
                    $PropertyObjArr,
                    [$property_id],
                    $CalculatedFieldObj,
                    $LoopFromDateObj,
                    $LoopToDateObj
                );
            $month_element['month'] = $LoopFromDateObj->format('m');
            $month_element['year']  = $LoopFromDateObj->format('Y');

            $month_element['actual']             = null;
            $month_element['budget']             = null;
            $month_element['variance']           = null;
            $month_element['variancePercentage'] = null;

            if (is_numeric($actual) && is_numeric($budget))
            {
                $month_element['actual']             = $actual;
                $month_element['budget']             = $budget;
                $month_element['variance']           = $actual - $budget;
                $month_element['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;
            }

            $overtime_arr['data'][$LoopFromDateObj->format('m') . '_' . $LoopFromDateObj->format('Y')] = $month_element;

            $LoopFromDateObj->addMonth(1);
            $LoopToDateObj = clone $LoopFromDateObj;
            $LoopToDateObj->endOfMonth();
        }

        return $this->sendResponse($overtime_arr['data'], 'ActualBudgetVarianceOvertime(s) retrieved successfully', null, null, $overtime_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForPropertyGroup($client_id, $property_group_id, $report_template_account_group_id, Request $request)
    {
        /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        /** @var PropertyGroupRepository $PropertyGroupRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);

        /**
         * @var Carbon $RequestedFromDateObj
         * @var Carbon $RequestedToDateObj
         * @var Carbon $BenchmarkGenerationDateObj
         */
        try
        {
            /**
             * I wish this we're better
             */
            list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj) =
                $this->processInputForNativeChartAmountController($client_id, $request->all());
        }
        catch (GeneralException $e)
        {
            if (preg_match("/RequestedFromDate/", $e->getMessage()))
            {
                return $this->sendResponse(null, $e->getMessage(), null, null, []);
            }
        }

        $overtime_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->find($report_template_account_group_id);

        $native_account_id_arr = $ReportTemplateAccountGroupObj->get_native_account_id_arr();

        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_account_id_arr;

        $PropertyGroupInQuestionObj = $PropertyGroupRepositoryObj
            ->with('properties.nativeAccountAmountsFiltered')
            ->find($property_group_id);

        $overtime_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceOvertime',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $property_group_id,
            $ReportTemplateAccountGroupObj,
            null,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        if ( ! $PropertyGroupInQuestionObj
            ->nativeAccountAmountsFilteredObjArr(
                $RequestedFromDateObj,
                $RequestedToDateObj
            )
            ->count())
        {
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $overtime_arr['metadata']);
        };

        /**
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated.
         *
         * remember that nativeAccountAmountsFiltered is filtered by '
         * Property::$nativeAccountAmountsFilteredFromDate
         * Property::$nativeAccountAmountsFilteredToDate and
         * Property::$nativeAccountAmountsFilteredNativeAccountIds
         */

        $overtime_arr['data'] = [];

        /** @var Carbon $LoopFromDateObj */
        $LoopFromDateObj = clone $RequestedFromDateObj;
        $LoopToDateObj   = clone $LoopFromDateObj;
        $LoopToDateObj->endOfMonth();

        $property_in_question_is_arr = $PropertyGroupInQuestionObj->properties->pluck('id')->toArray();

        while ($LoopFromDateObj->lessThanOrEqualTo($RequestedToDateObj))
        {
            $month_element                    = [];
            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            list($budget, $actual) =
                $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                    $PropertyGroupInQuestionObj->properties,
                    $property_in_question_is_arr,
                    $native_account_id_arr,
                    $LoopFromDateObj,
                    $LoopToDateObj
                );

            $month_element['month'] = $LoopFromDateObj->format('m');
            $month_element['year']  = $LoopFromDateObj->format('Y');

            $month_element['actual']             = null;
            $month_element['budget']             = null;
            $month_element['variance']           = null;
            $month_element['variancePercentage'] = null;

            if (is_numeric($actual) && is_numeric($budget))
            {
                $month_element['actual']             = $actual;
                $month_element['budget']             = $budget;
                $month_element['variance']           = $actual - $budget;
                $month_element['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;
            }
            $overtime_arr['data'][$LoopFromDateObj->format('m') . '_' . $LoopFromDateObj->format('Y')] = $month_element;

            $LoopFromDateObj->addMonth(1);
            $LoopToDateObj = clone $LoopFromDateObj;
            $LoopToDateObj->endOfMonth();
        }

        return $this->sendResponse($overtime_arr['data'], 'ActualBudgetVarianceOvertime(s) retrieved successfully', null, null, $overtime_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param integer $calculated_field_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getCalculatedFieldActualBudgetVarianceOverTimeForPropertyGroup($client_id, $property_group_id, $calculated_field_id, Request $request)
    {
        /** @var $CalculatedFieldRepositoryObj CalculatedFieldRepository */
        $CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);
        /** @var PropertyGroupRepository $PropertyGroupRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);

        /**
         * @var Carbon $RequestedFromDateObj
         * @var Carbon $RequestedToDateObj
         * @var Carbon $BenchmarkGenerationDateObj
         */
        try
        {
            /**
             * I wish this we're better
             */
            list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj) =
                $this->processInputForNativeChartAmountController($client_id, $request->all());
        }
        catch (GeneralException $e)
        {
            if (preg_match("/RequestedFromDate/", $e->getMessage()))
            {
                return $this->sendResponse(null, $e->getMessage(), null, null, []);
            }
        }

        $overtime_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $CalculatedFieldObj =
            $CalculatedFieldRepositoryObj
                ->with('calculatedFieldEquations.calculatedFieldVariables')
                ->find($calculated_field_id);

        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $CalculatedFieldObj->get_native_account_id_arr();

        $PropertyGroupInQuestionObj = $PropertyGroupRepositoryObj
            ->with('properties.nativeAccountAmountsFiltered')
            ->find($property_group_id);

        $overtime_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceOvertime',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $property_group_id,
            null,
            $CalculatedFieldObj,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        if ( ! $PropertyGroupInQuestionObj
            ->nativeAccountAmountsFilteredObjArr(
                $RequestedFromDateObj,
                $RequestedToDateObj
            )
            ->count())
        {
            return $this->sendResponse(null, 'No data found for propertyGroup(s)/data range in question', null, null, $overtime_arr['metadata']);
        };

        /**
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated.
         *
         * remember that nativeAccountAmountsFiltered is filtered by '
         * Property::$nativeAccountAmountsFilteredFromDate
         * Property::$nativeAccountAmountsFilteredToDate and
         * Property::$nativeAccountAmountsFilteredNativeAccountIds
         */

        $overtime_arr['data'] = [];

        /** @var Carbon $LoopFromDateObj */
        $LoopFromDateObj = clone $RequestedFromDateObj;
        $LoopToDateObj   = clone $LoopFromDateObj;
        $LoopToDateObj->endOfMonth();

        while ($LoopFromDateObj->lessThanOrEqualTo($RequestedToDateObj))
        {

            $month_element = [];
            list($budget, $actual) =
                $this->processTotalsForPropertyArrCalculatedField(
                    $PropertyGroupInQuestionObj->properties,
                    $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                    $CalculatedFieldObj,
                    $LoopFromDateObj,
                    $LoopToDateObj
                );

            $month_element['month'] = $LoopFromDateObj->format('m');
            $month_element['year']  = $LoopFromDateObj->format('Y');

            $month_element['actual']             = null;
            $month_element['budget']             = null;
            $month_element['variance']           = null;
            $month_element['variancePercentage'] = null;

            if ($budget && $actual)
            {
                $month_element['actual']             = $actual;
                $month_element['budget']             = $budget;
                $month_element['variance']           = $actual - $budget;
                $month_element['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;
            }
            $overtime_arr['data'][$LoopFromDateObj->format('m') . '_' . $LoopFromDateObj->format('Y')] = $month_element;

            $LoopFromDateObj->addMonth(1);
            $LoopToDateObj = clone $LoopFromDateObj;
            $LoopToDateObj->endOfMonth();
        }

        return $this->sendResponse($overtime_arr['data'], 'ActualBudgetVarianceOvertime(s) retrieved successfully', null, null, $overtime_arr['metadata']);
    }

}
