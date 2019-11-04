<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateMappingRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateMappingRequest;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Repositories\ReportTemplateMappingRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReportTemplateMappingDeprecatedController
 * @codeCoverageIgnore
 */
class ReportTemplateMappingDeprecatedController extends BaseApiController
{
    /** @var  ReportTemplateMappingRepository */
    private $ReportTemplateMappingRepositoryObj;

    public function __construct(ReportTemplateMappingRepository $ReportTemplateMappingRepositoryObj)
    {
        $this->ReportTemplateMappingRepositoryObj = $ReportTemplateMappingRepositoryObj;
        parent::__construct($ReportTemplateMappingRepositoryObj);
    }

    /**
     * Display a listing of the ReportTemplateMapping.
     * GET|HEAD /reportTemplateMappings
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id, $report_template_id, $report_template_account_group_id)
    {
        $this->ReportTemplateMappingRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ReportTemplateMappingRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ReportTemplateMappingObjArr = $this->ReportTemplateMappingRepositoryObj->findWhere(
            [
                'report_template_account_group_id' => $report_template_account_group_id,
            ]
        );

        return $this->sendResponse($ReportTemplateMappingObjArr, 'ReportTemplateMapping(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplateMapping in storage.
     *
     * @param CreateReportTemplateMappingRequest $ReportTemplateMappingRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(CreateReportTemplateMappingRequest $ReportTemplateMappingRequestObj, $client_id, $report_template_id, $report_template_account_group_id)
    {
        $ClientObj = Client::find($client_id);
        $input = $ReportTemplateMappingRequestObj->all();

        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->create($input);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                ]
            )
        );
        event(
            new PreCalcClientEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id' => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'clients' => [],
                        ],
                ]
            )
        );
        event(
            new PreCalcPropertiesEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'properties' => []
                        ],
                    'launch_job_property_id_arr'       => $ClientObj->properties->pluck('id')->toArray()
                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'property_groups' => [],
                        ],
                    'launch_job_property_group_id_arr' => $ClientObj->propertyGroups->pluck('id')->toArray()
                ]
            )
        );

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping saved successfully');
    }

    /**
     * Display the specified ReportTemplateMapping.
     * GET|HEAD /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $report_template_id, $report_template_account_group_id, $report_template_mapping_id)
    {
        /** @var ReportTemplateMapping $reportTemplateMapping */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($report_template_mapping_id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping retrieved successfully');
    }

    /**
     * Update the specified ReportTemplateMapping in storage.
     * PUT/PATCH /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @param UpdateReportTemplateMappingRequest $ReportTemplateMappingRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function update(
        $client_id,
        $report_template_id,
        $report_template_account_group_id,
        $report_template_mapping_id,
        UpdateReportTemplateMappingRequest $ReportTemplateMappingRequestObj
    ) {
        $ClientObj = Client::find($client_id);
        $input = $ReportTemplateMappingRequestObj->all();
        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($report_template_mapping_id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->update($input, $report_template_mapping_id);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                ]
            )
        );
        event(
            new PreCalcClientEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id' => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'clients' => [],
                        ],
                ]
            )
        );
        event(
            new PreCalcPropertiesEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'properties' => []
                        ],
                    'launch_job_property_id_arr'       => $ClientObj->properties->pluck('id')->toArray()
                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'property_groups' => [],
                        ],
                    'launch_job_property_group_id_arr' => $ClientObj->propertyGroups->pluck('id')->toArray()
                ]
            )
        );

        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping updated successfully');
    }

    /**
     * Remove the specified ReportTemplateMapping from storage.
     * DELETE /reportTemplateMappings/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $report_template_id, $report_template_account_group_id, $report_template_mapping_id)
    {
        $ClientObj = Client::find($client_id);
        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
        $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWithoutFail($report_template_mapping_id);
        if (empty($ReportTemplateMappingObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }

        $this->ReportTemplateMappingRepositoryObj->delete($report_template_mapping_id);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                ]
            )
        );
        event(
            new PreCalcClientEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id' => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'clients' => [],
                        ],
                ]
            )
        );
        event(
            new PreCalcPropertiesEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'properties' => []
                        ],
                    'launch_job_property_id_arr'       => $ClientObj->properties->pluck('id')->toArray()
                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateMappingObj),
                    'event_trigger_object_class_id'    => $ReportTemplateMappingObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'property_groups' => [],
                        ],
                    'launch_job_property_group_id_arr' => $ClientObj->propertyGroups->pluck('id')->toArray()
                ]
            )
        );

        return $this->sendResponse($report_template_mapping_id, 'ReportTemplateMapping deleted successfully');
    }
}
