<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class ActualBudgetVarianceTotalController
 */
class ActualBudgetVarianceTotalController extends NativeChartAmountController
{
    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupActualBudgetVarianceTotalForProperty($client_id, $property_id, $report_template_account_group_id, Request $request)
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

        $rank_switch = isset($input['rank']) && $input['rank'];

        $total_arr = [];
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->find($report_template_account_group_id);

        /**
         * since we want trend.....
         */
        $RequestedFromDateLess1YearObj = clone $RequestedFromDateObj;
        $RequestedFromDateLess1YearObj->subYear(1);
        $RequestedToDateLess1YearObj = clone $RequestedToDateObj;
        $RequestedToDateLess1YearObj->subYear(1);

        $native_account_id_arr = $ReportTemplateAccountGroupObj->get_native_account_id_arr();

        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateLess1YearObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_account_id_arr;

        $PropertyInQuestionObj = $PropertyRepositoryObj
            ->with('nativeAccountAmountsFiltered')
            ->find($property_id);

        $total_arr['metadata'] = $this->generateNativeChartMetadata(
            $rank_switch ? 'actualBudgetVarianceTotalRank' : 'actualBudgetVarianceTotal',
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
                function (NativeAccountAmount $NativeAccountAmountObj) use ($RequestedFromDateObj, $RequestedToDateObj, $native_account_id_arr)
                {
                    return
                        in_array($NativeAccountAmountObj->native_account_id, $native_account_id_arr) &&
                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($RequestedFromDateObj) &&
                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($RequestedToDateObj);
                }
            )->count($RequestedFromDateObj . $RequestedToDateObj))
        {
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $total_arr['metadata']);
        };

        $rank_arr = null;
        if ($rank_switch)
        {
            /**
             * remember that nativeAccountAmountsFiltered is filtered by '
             * Property::$nativeAccountAmountsFilteredFromDate
             * Property::$nativeAccountAmountsFilteredToDate and
             * Property::$nativeAccountAmountsFilteredNativeAccountIds
             */
            $PropertyObjArr                   = $PropertyRepositoryObj
                ->with('nativeAccountAmountsFiltered')
                ->findWhereIn(
                    'id',
                    $this->getCurrentLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()
                );
            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            $rank_arr                         = $NativeAccountAmountRepositoryObj->rankAndScorePropertiesByActualForRTAG(
                $PropertyObjArr,
                $native_account_id_arr,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );
            if ($rank_arr)
            {
                $total_arr['data']["property_rank"]       =
                    $rank_arr
                        ->filter(
                            function ($rank_element) use ($property_id)
                            {
                                return $rank_element['property_id'] == $property_id;
                            }
                        )
                        ->first()['rank'];
                $total_arr['data']['property_rank_total'] = $rank_arr->count();
            }
        }
        else
        {
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
            $PropertyObjArr = collect_waypoint([$PropertyInQuestionObj]);
        }

        $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
        list($total_budget, $total_actual) =
            $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                $PropertyObjArr,
                [$property_id],
                $native_account_id_arr,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );

        /**
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated).
         */
        $variance = $total_actual - $total_budget;

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($last_year_total_budget, $last_year_total_actual, $ActualToDateObj) =
            $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                $PropertyObjArr,
                [$property_id],
                $native_account_id_arr,
                $RequestedFromDateLess1YearObj,
                $RequestedToDateLess1YearObj
            );

        $total_arr['data']["trend"]      = null;
        $total_arr['data']["trend_unit"] = null;
        if ($ActualToDateObj)
        {
            $total_arr['data']["trend"]      = (($total_actual - $last_year_total_actual) / $last_year_total_actual) * 100;
            $total_arr['data']["trend_unit"] = "percentage";
        }

        $total_arr['data']["actual"]             = $total_actual;
        $total_arr['data']["budget"]             = $total_budget;
        $total_arr['data']["variance"]           = $variance;
        $total_arr['data']['variancePercentage'] = $total_budget ? (100 * ($total_actual - $total_budget)) / $total_budget : null;

        return $this->sendResponse($total_arr['data'], 'ActualBudgetVarianceTotal(s) retrieved successfully', null, null, $total_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $calculated_field_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getCalculatedFieldActualBudgetVarianceTotalForProperty($client_id, $property_id, $calculated_field_id, Request $request)
    {
        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        /** @var CalculatedFieldRepository $CalculatedFieldRepositoryObj */
        $CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);

        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $CalculatedFieldRepositoryObj
            ->with('calculatedFieldEquations.calculatedFieldVariables.reportTemplateAccountGroup.reportTemplateAccountGroupChildren')
            ->find($calculated_field_id);

        $total_arr = [];

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

        $rank_switch = isset($input['rank']) && $input['rank'];

        $total_arr['metadata'] = $this->generateNativeChartMetadata(
            $rank_switch ? 'actualBudgetVarianceTotalRank' : 'actualBudgetVarianceTotal',
            $input,
            $BenchmarkGenerationDateObj,
            $property_id,
            null,
            null,
            $CalculatedFieldObj,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        /**
         * since we want trend.....
         */
        $RequestedFromDateLess1YearObj = clone $RequestedFromDateObj;
        $RequestedFromDateLess1YearObj->subYear(1);
        $RequestedToDateLess1YearObj = clone $RequestedToDateObj;
        $RequestedToDateLess1YearObj->subYear(1);

        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateLess1YearObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $CalculatedFieldObj->get_native_account_id_arr();

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
                function (NativeAccountAmount $NativeAccountAmountObj) use ($RequestedFromDateObj, $RequestedToDateObj, $CalculatedFieldObj)
                {
                    return
                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($RequestedFromDateObj) &&
                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($RequestedToDateObj);
                }
            )
            ->count())
        {
            /**
             * ObjectOrCollectionObj set to null to avoid "{}"
             */
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $total_arr['metadata']);
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
        $PropertyObjArr = collect_waypoint([$PropertyInQuestionObj]);

        $total_arr['data'] = [];
        if ($rank_switch)
        {
            $PropertyObjArr                   = $PropertyRepositoryObj
                ->with('nativeAccountAmountsFiltered')
                ->findWhereIn(
                    'id',
                    $this->getCurrentLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()
                );
            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            $rank_arr                         = $NativeAccountAmountRepositoryObj->rankAndScorePropertyByActualForCalculatedFields(
                $PropertyObjArr,
                $CalculatedFieldObj,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );

            if ($rank_arr)
            {
                $total_arr['data']["property_rank"]       =
                    $rank_arr
                        ->filter(
                            function ($rank_element) use ($property_id)
                            {
                                return $rank_element['property_id'] == $property_id;
                            }
                        )
                        ->first()['rank'];
                $total_arr['data']['property_rank_total'] = $rank_arr->count();
            }
        }

        list($budget, $actual) =
            $this->processTotalsForPropertyArrCalculatedField(
                $PropertyObjArr,
                [$property_id],
                $CalculatedFieldObj,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );

        $total_arr['data']['budget']             = $budget;
        $total_arr['data']['actual']             = $actual;
        $total_arr['data']["variance"]           = $actual - $budget;
        $total_arr['data']['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($last_year_total_budget, $last_year_total_actual) =
            $this->processTotalsForPropertyArrCalculatedField(
                $PropertyObjArr,
                [$property_id],
                $CalculatedFieldObj,
                $RequestedFromDateLess1YearObj,
                $RequestedToDateLess1YearObj
            );

        $total_arr['data']["trend"]      = null;
        $total_arr['data']["trend_unit"] = null;
        if ($total_arr['data']['actual'] && $last_year_total_actual)
        {
            $total_arr['data']["trend"]      = (($total_arr['data']['actual'] - $last_year_total_actual) / $last_year_total_actual) * 100;
            $total_arr['data']["trend_unit"] = "percentage";
        }

        return $this->sendResponse($total_arr['data'], 'ActualBudgetVarianceTotal(s) retrieved successfully', null, null, $total_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupActualBudgetVarianceTotalForPropertyGroup($client_id, $property_group_id, $report_template_account_group_id, Request $request)
    {
        /** @var NativeAccountAmountRepository $NativeAccountAmountRepositoryObj */
        $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
        /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        /** @var PropertyGroupRepository $PropertyGroupRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);

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

        $total_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->find($report_template_account_group_id);
        /**
         * since we want trend.....
         */
        $RequestedFromDateLess1YearObj = clone $RequestedFromDateObj;
        $RequestedFromDateLess1YearObj->subYear(1);
        $RequestedToDateLess1YearObj = clone $RequestedToDateObj;
        $RequestedToDateLess1YearObj->subYear(1);

        /**
         * @var PropertyGroup $PropertyGroupInQuestionObj
         *
         * At this point, all the data we need for what we want to do is in
         * $PropertyObjArr (notice that the property->nativeAccountAmountsFiltered
         * is populated.
         *
         * remember that nativeAccountAmountsFiltered is filtered by '
         * Property::$nativeAccountAmountsFilteredFromDate
         * Property::$nativeAccountAmountsFilteredToDate and
         * Property::$nativeAccountAmountsFilteredNativeAccountIds
         */
        $native_account_id_arr                                  = $ReportTemplateAccountGroupObj->get_native_account_id_arr();
        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateLess1YearObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_account_id_arr;

        $PropertyGroupInQuestionObj =
            $PropertyGroupRepositoryObj
                ->with('properties.nativeAccountAmountsFiltered')
                ->find($property_group_id);

        $total_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceTotal',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
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
            return $this->sendResponse(null, 'No data found for propertyGroup(s)/data range in question', null, null, $total_arr['metadata']);
        };

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($total_budget, $total_actual, $ActualToDateObj) =
            $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                $PropertyGroupInQuestionObj->properties,
                $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                $native_account_id_arr,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );

        $variance = $total_actual - $total_budget;

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($last_year_total_budget, $last_year_total_actual, $ActualToDateObj) =
            $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                $PropertyGroupInQuestionObj->properties,
                $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                $native_account_id_arr,
                $RequestedFromDateLess1YearObj,
                $RequestedToDateLess1YearObj
            );

        $total_arr['data']["trend"]      = null;
        $total_arr['data']["trend_unit"] = null;
        if ($ActualToDateObj)
        {
            $total_arr['data']["trend"]      = $last_year_total_actual ? (($total_actual - $last_year_total_actual) / $last_year_total_actual) * 100 : null;
            $total_arr['data']["trend_unit"] = "percentage";
        }

        $total_arr['data']["actual"]             = $total_actual;
        $total_arr['data']["budget"]             = $total_budget;
        $total_arr['data']["variance"]           = $variance;
        $total_arr['data']['variancePercentage'] = $total_budget ? (100 * ($total_actual - $total_budget)) / $total_budget : null;

        return $this->sendResponse($total_arr['data'], 'ActualBudgetVarianceTotal(s) retrieved successfully', null, null, $total_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param integer $calculated_field_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getCalculatedFieldActualBudgetVarianceTotalForPropertyGroup($client_id, $property_group_id, $calculated_field_id, Request $request)
    {
        /** @var PropertyGroupRepository $PropertyGroupRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        /** @var CalculatedFieldRepository $CalculatedFieldRepositoryObj */
        $CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);

        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $CalculatedFieldRepositoryObj
            ->with('calculatedFieldEquations.calculatedFieldVariables.reportTemplateAccountGroup.reportTemplateAccountGroupChildren')
            ->find($calculated_field_id);

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
        /**
         * since we want trend.....
         */
        $RequestedFromDateLess1YearObj = clone $RequestedFromDateObj;
        $RequestedFromDateLess1YearObj->subYear(1);
        $RequestedToDateLess1YearObj = clone $RequestedToDateObj;
        $RequestedToDateLess1YearObj->subYear(1);

        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateLess1YearObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $CalculatedFieldObj->get_native_account_id_arr();

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
        $PropertyGroupInQuestionObj = $PropertyGroupRepositoryObj
            ->with('properties.nativeAccountAmountsFiltered')
            ->find($property_group_id);

        $total_arr['data'] = [];

        $total_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceTotal',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $property_group_id,
            null,
            $CalculatedFieldObj,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        /**
         * anything???????
         */
        if ( ! $PropertyGroupInQuestionObj
            ->nativeAccountAmountsFilteredObjArr(
                $RequestedFromDateObj,
                $RequestedToDateObj
            )
            ->count())
        {
            return $this->sendResponse(null, 'No data found for propertyGroup(s)/data range in question', null, null, $total_arr['metadata']);
        };

        list($budget, $actual) =
            $this->processTotalsForPropertyArrCalculatedField(
                $PropertyGroupInQuestionObj->properties,
                $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                $CalculatedFieldObj,
                $RequestedFromDateObj,
                $RequestedToDateObj
            );

        $total_arr['data']['budget']             = $budget;
        $total_arr['data']['actual']             = $actual;
        $total_arr['data']["variance"]           = $actual - $budget;
        $total_arr['data']['variancePercentage'] = $budget ? (100 * ($actual - $budget)) / $budget : null;

        /** @noinspection PhpUnusedLocalVariableInspection */
        list($last_year_total_budget, $last_year_total_actual) =
            $this->processTotalsForPropertyArrCalculatedField(
                $PropertyGroupInQuestionObj->properties,
                $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                $CalculatedFieldObj,
                $RequestedFromDateLess1YearObj,
                $RequestedToDateLess1YearObj
            );

        $total_arr['data']["trend"]      = null;
        $total_arr['data']["trend_unit"] = null;
        if ($total_arr['data']['actual'] && $last_year_total_actual)
        {
            $total_arr['data']["trend"]      = (($total_arr['data']['actual'] - $last_year_total_actual) / $last_year_total_actual) * 100;
            $total_arr['data']["trend_unit"] = "percentage";
        }

        return $this->sendResponse($total_arr['data'], 'ActualBudgetVarianceTotal(s) retrieved successfully', null, null, $total_arr['metadata']);
    }
}
