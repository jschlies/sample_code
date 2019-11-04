<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class NativeChartActualBudgetVarianceBreakdownController
 */
class ActualBudgetVarianceBreakdownController extends NativeChartAmountController
{
    /**
     * @param integer $client_id
     * @param $property_id
     * @param $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForProperty($client_id, $property_id, $report_template_account_group_id, Request $request)
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

        $breakdown_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->find($report_template_account_group_id);

        $native_account_id_arr = $ReportTemplateAccountGroupObj->get_native_account_id_arr();
        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $native_account_id_arr;

        $PropertyInQuestionObj = $PropertyRepositoryObj
            ->with('nativeAccountAmountsFiltered')
            ->find($property_id);

        $breakdown_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceBreakdown',
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
            return $this->sendResponse(null, 'No data found for property(s)/data range in question', null, null, $breakdown_arr['metadata']);
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
        $PropertyObjArr = $PropertyRepositoryObj
            ->with('nativeAccountAmountsFiltered')
            ->findWhere(
                [
                    'id' => $property_id,
                ]
            );

        $breakdown_arr['data'] = [];

        /** @var ReportTemplateAccountGroup $ChildReportTemplateAccountGroupObj */
        foreach ($ReportTemplateAccountGroupObj->getChildren() as $ChildReportTemplateAccountGroupObj)
        {
            $child_breakdown_arr['report_template_account_group_id']          = $ChildReportTemplateAccountGroupObj->id;
            $child_breakdown_arr['report_template_account_group_name']        = $ChildReportTemplateAccountGroupObj->report_template_account_group_name;
            $child_breakdown_arr['parent_report_template_account_group_id']   =
                $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->id
                    : null;
            $child_breakdown_arr['parent_report_template_account_group_name'] =
                $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->report_template_account_group_name
                    : null;

            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            list($budget, $actual) =
                $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                    $PropertyObjArr,
                    [$property_id],
                    $ChildReportTemplateAccountGroupObj->get_native_account_id_arr(),
                    $RequestedFromDateObj,
                    $RequestedToDateObj
                );

            $child_breakdown_arr['actual']           = null;
            $child_breakdown_arr['budget']           = null;
            $child_breakdown_arr['variance']         = null;
            $child_breakdown_arr['variance_percent'] = null;
            if (is_numeric($actual) && is_numeric($budget))
            {
                $child_breakdown_arr['actual']           = $actual;
                $child_breakdown_arr['budget']           = $budget;
                $child_breakdown_arr['variance']         = $actual - $budget;
                $child_breakdown_arr['variance_percent'] = $budget != 0 ? (($actual - $budget) / $budget) * 100 : 0;
            }
            $breakdown_arr['data'][] = $child_breakdown_arr;
        }

        return $this->sendResponse($breakdown_arr['data'], 'ActualBudgetVarianceBreakdown(s) retrieved successfully', null, null, $breakdown_arr['metadata']);
    }

    /**
     * @param integer $client_id
     * @param integer $property_group_id
     * @param $report_template_account_group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForPropertyGroup(
        $client_id,
        $property_group_id,
        $report_template_account_group_id,
        Request $request
    ) {

        /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        /** @var PropertyGroupRepository $PropertyRepositoryObj */
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

        $breakdown_arr = [];

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->with('nativeAccounts')
                ->with('reportTemplateAccountGroupChildren.reportTemplateAccountGroupChildren.reportTemplateAccountGroupChildren')
                ->find($report_template_account_group_id);

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
        Property::$nativeAccountAmountsFilteredFromDate         = $RequestedFromDateObj;
        Property::$nativeAccountAmountsFilteredToDate           = $RequestedToDateObj;
        Property::$nativeAccountAmountsFilteredNativeAccountIds = $ReportTemplateAccountGroupObj->get_native_account_id_arr();

        /** @var PropertyGroup $PropertyGroupInQuestionObj */
        $PropertyGroupInQuestionObj =
            $PropertyGroupRepositoryObj
                ->with('properties.nativeAccountAmountsFiltered')
                ->find($property_group_id);

        $breakdown_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceBreakdown',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $property_group_id,
            $ReportTemplateAccountGroupObj,
            null,
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
            ->count()
        )
        {
            return $this->sendResponse(null, 'No data found for propertyGroup(s)/data range in question', null, null, $breakdown_arr['metadata']);
        };

        $breakdown_arr['data'] = [];

        /** @var ReportTemplateAccountGroup $ChildReportTemplateAccountGroupObj */
        foreach ($ReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren as $ChildReportTemplateAccountGroupObj)
        {
            $child_breakdown_arr['report_template_account_group_id']          = $ChildReportTemplateAccountGroupObj->id;
            $child_breakdown_arr['report_template_account_group_name']        = $ChildReportTemplateAccountGroupObj->report_template_account_group_name;
            $child_breakdown_arr['parent_report_template_account_group_id']   =
                $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->id
                    : null;
            $child_breakdown_arr['parent_report_template_account_group_name'] =
                $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ChildReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->report_template_account_group_name
                    : null;

            $NativeAccountAmountRepositoryObj = App::make(NativeAccountAmountRepository::class);
            list($budget, $actual, $ActualToDateObj) =
                $NativeAccountAmountRepositoryObj->processTotalsForPropertyArrActualBudget(
                    $PropertyGroupInQuestionObj->properties,
                    $PropertyGroupInQuestionObj->properties->pluck('id')->toArray(),
                    $ChildReportTemplateAccountGroupObj->get_native_account_id_arr(),
                    $RequestedFromDateObj,
                    $RequestedToDateObj
                );

            $child_breakdown_arr['actual']           = null;
            $child_breakdown_arr['budget']           = null;
            $child_breakdown_arr['variance']         = null;
            $child_breakdown_arr['variance_percent'] = null;
            if ($ActualToDateObj)
            {
                $child_breakdown_arr['actual']           = $actual;
                $child_breakdown_arr['budget']           = $budget;
                $child_breakdown_arr['variance']         = $actual - $budget;
                $child_breakdown_arr['variance_percent'] = $budget != 0 ? (($actual - $budget) / $budget) * 100 : 0;
            }
            $breakdown_arr['data'][] = $child_breakdown_arr;
        }

        $breakdown_arr['metadata'] = $this->generateNativeChartMetadata(
            'actualBudgetVarianceBreakdown',
            $input,
            $BenchmarkGenerationDateObj,
            null,
            $property_group_id,
            $ReportTemplateAccountGroupObj,
            null,
            $RequestedFromDateObj,
            $RequestedToDateObj
        );

        return $this->sendResponse($breakdown_arr['data'], 'ActualBudgetVarianceBreakdown(s) retrieved successfully', null, null, $breakdown_arr['metadata']);
    }
}
