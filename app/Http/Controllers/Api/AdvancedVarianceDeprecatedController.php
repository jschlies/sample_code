<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVarianceWorkflow;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\AdvancedVarianceWorkflowRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceRequest;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceDeprecatedController
 * @codeCoverageIgnore
 */
class AdvancedVarianceDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceWorkflowRepository */
    private $AdvancedVarianceWorkflowRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceRepositoryObj = $AdvancedVarianceRepositoryObj;
        parent::__construct($AdvancedVarianceRepositoryObj);

        $this->AdvancedVarianceWorkflowRepositoryObj = App::make(AdvancedVarianceWorkflowRepository::class);
    }

    /**
     * Display a listing of the AdvancedVariance.
     * GET|HEAD /advancedVariances
     *
     * @param Request $RequestObj
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id, $property_id)
    {
        $this->AdvancedVarianceRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceObjArr = $this->AdvancedVarianceRepositoryObj
            ->all()
            ->filter(
                function ($item) use ($property_id)
                {
                    return $item->property_id == $property_id;
                }
            );

        return $this->sendResponse($AdvancedVarianceObjArr, 'AdvancedVariance(s) retrieved successfully');
    }

    /**
     * Store a newly created AdvancedVariance in storage.
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     */
    public function store($client_id, $property_id, CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        $input                = $AdvancedVarianceRequestObj->all();
        $input['property_id'] = $property_id;

        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create($input);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance saved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id, $advanced_variance_id)
    {
        /** @var $AdvancedVarianceObj $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceObj, 'AdvancedVariance retrieved successfully');
    }

    /**
     * Remove the specified AdvancedVariance from storage.
     * DELETE /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->findWithoutFail($advanced_variance_id);
        if (empty($AdvancedVarianceObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVariance not found'), 404);
        }
        $AdvancedVarianceObj->delete();

        return $this->sendResponse($advanced_variance_id, 'AdvancedVariance deleted successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard responce - fix me
     */
    public function uniqueAdvancedVarianceDatesForClient($client_id)
    {
        /** @var $AdvancedVarianceObj $AdvancedVarianceObj */
        $dateObjArr = $this->AdvancedVarianceRepositoryObj->get_unique_advanced_variance_dates_client($client_id);
        return $this->sendResponse($dateObjArr, 'AdvancedVariance dates retrieved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard responce - fix me
     */
    public function uniqueAdvancedVarianceDatesForProperties($client_id, $property_id_arr = null)
    {
        /** @var $AdvancedVarianceObj $AdvancedVarianceObj */
        $dateObjArr = $this->AdvancedVarianceRepositoryObj->get_unique_advanced_variance_dates_properties($client_id, $property_id_arr);
        return $this->sendResponse($dateObjArr, 'AdvancedVariance dates retrieved successfully');
    }

    /**
     * Display the specified AdvancedVariance.
     * GET|HEAD /advancedVariances/{id}
     *
     * @param integer $client_id
     * @param integer $property_id
     * @param $advanced_variance_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function uniqueAdvancedVarianceDatesForPropertyGroup($client_id, $property_group_id)
    {
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = PropertyGroup::find($property_group_id);

        $key                                           = 'unique_advanced_variance_dates_property_group_' . $property_group_id;
        $unique_advanced_variance_dates_property_group = $PropertyGroupObj->getPreCalcValue($key);
        if ($unique_advanced_variance_dates_property_group === null)
        {
            $unique_advanced_variance_dates_property_group =
                $this->AdvancedVarianceRepositoryObj->get_unique_advanced_variance_dates_property_group($client_id, $property_group_id);

            $PropertyGroupObj->updatePreCalcValue(
                $key,
                $unique_advanced_variance_dates_property_group->toArray()
            );
        }

        return $this->sendResponse($unique_advanced_variance_dates_property_group, 'AdvancedVariance dates retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @return JsonResponse|null
     */
    public function advancedVarianceWorkflow($client_id, $property_group_id, CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = PropertyGroup::with('propertyGroupProperties')->find($property_group_id);

        $input = $AdvancedVarianceRequestObj->all();
        if (
            ! array_key_exists('as_of_month', $input) ||
            ! $input['as_of_month'] ||
            ! array_key_exists('as_of_year', $input) ||
            ! $input['as_of_year']
        )
        {
            throw new GeneralException('Invalid as_of_month/as_of_year encountered', 404);
        }
        $property_id_arr = $PropertyGroupObj->propertyGroupProperties->pluck('property_id')->toArray();

        $AdvancedVarianceWorkflowObjArr =
            $this->AdvancedVarianceWorkflowRepositoryObj
                ->findWhereIn('property_id', $property_id_arr)
                ->where('as_of_month', $input['as_of_month'])
                ->where('as_of_year', $input['as_of_year']);

        return $this->sendResponse($AdvancedVarianceWorkflowObjArr->toArray(), 'AdvancedVarianceWorkflow(s) retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @return JsonResponse|null
     */
    public function advancedVarianceLineItemWorkflow($client_id, $property_id, $advanced_variance_id)
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceWorkflowObj = AdvancedVarianceWorkflow::find($advanced_variance_id);

        $return_me                                      = $AdvancedVarianceWorkflowObj->toArray();
        $return_me['AdvancedVarianceLineItemWorkflows'] = $AdvancedVarianceWorkflowObj->advancedVarianceLineItemWorkflows->toArray();
        return $this->sendResponse($return_me, 'advancedVarianceLineItemSummaryWorkflow(s) retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @return JsonResponse|null
     */
    public function advancedVarianceLineItemRTAGWorkflow($client_id, $property_id, $report_template_account_group_id)
    {
        /** @var Collection $AdvancedVarianceObjArr */
        $AdvancedVarianceObjArr = AdvancedVariance::where('property_id', $property_id)->get();

        $AdvancedVarianceLineItemSummaryWorkflowObjArr =
            $AdvancedVarianceObjArr
                ->map(
                    function (AdvancedVariance $AdvancedVarianceObj) use ($report_template_account_group_id)
                    {
                        return $AdvancedVarianceObj->advancedVarianceLineItemSummaryWorkflows->where('report_template_account_group_id', $report_template_account_group_id);
                    }
                )
                ->flatten();

        return $this->sendResponse($AdvancedVarianceLineItemSummaryWorkflowObjArr->toArray(), 'AdvancedVarianceWorkflow retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_group_id
     * @return JsonResponse|null
     */
    public function advancedVarianceLineItemCalculatedFieldWorkflow($client_id, $property_id, $calculated_field_id)
    {
        /** @var Collection $AdvancedVarianceObjArr */
        $AdvancedVarianceObjArr = AdvancedVariance::where('property_id', $property_id)->get();

        $AdvancedVarianceLineItemSummaryWorkflowObjArr =
            $AdvancedVarianceObjArr
                ->map(
                    function (AdvancedVariance $AdvancedVarianceObj) use ($calculated_field_id)
                    {
                        return $AdvancedVarianceObj->advancedVarianceLineItemSummaryWorkflows->where('calculated_field_id', $calculated_field_id);
                    }
                )
                ->flatten();

        return $this->sendResponse($AdvancedVarianceLineItemSummaryWorkflowObjArr->toArray(), 'AdvancedVarianceWorkflow retrieved successfully');
    }
}
