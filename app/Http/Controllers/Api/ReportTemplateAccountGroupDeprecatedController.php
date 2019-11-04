<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\ReportTemplateMappingRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateAccountGroupRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateAccountGroupRequest;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use App;

/**
 * Class ReportTemplateAccountGroupDeprecatedController
 * @codeCoverageIgnore
 */
class ReportTemplateAccountGroupDeprecatedController extends BaseApiController
{
    /** @var  ReportTemplateAccountGroupRepository */
    private $ReportTemplateAccountGroupRepositoryObj;

    /** @var  ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;

    /** @var  ReportTemplateMappingRepository */
    private $ReportTemplateMappingRepositoryObj;

    public function __construct(ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj)
    {
        $this->ReportTemplateAccountGroupRepositoryObj = $ReportTemplateAccountGroupRepositoryObj;
        $this->ReportTemplateRepositoryObj             = App::make(ReportTemplateRepository::class);
        $this->ReportTemplateMappingRepositoryObj      = App::make(ReportTemplateMappingRepository::class);
        parent::__construct($ReportTemplateAccountGroupRepositoryObj);
    }

    /**
     * Display a listing of the ReportTemplateAccountGroup.
     * GET|HEAD /reportTemplateAccountGroups
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index($client_id, $report_template_id, Request $RequestObj)
    {
        $this->ReportTemplateAccountGroupRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ReportTemplateAccountGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $ReportTemplateAccountGroupObjArr = $this->ReportTemplateAccountGroupRepositoryObj->findWhere(
            [
                ['report_template_id', '=', $report_template_id],
            ]
        );

        return $this->sendResponse($ReportTemplateAccountGroupObjArr, 'ReportTemplateAccountGroup(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplateAccountGroup in storage.
     *
     * @param integer $client_id
     * @param $report_template_id
     * @param CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store($client_id, $report_template_id, CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj)
    {
        $ClientObj = Client::find($client_id);
        $input                         = $ReportTemplateAccountGroupRequestObj->all();
        $input['report_template_id']   = $report_template_id;
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->create($input);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'    => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id' => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup saved successfully');
    }

    /**
     * Display the specified ReportTemplateAccountGroup.
     * GET|HEAD /reportTemplateAccountGroups/{id}
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $report_template_id, $report_template_account_group_id)
    {
        /** @var ReportTemplateAccountGroup $reportTemplateAccountGroup */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($report_template_account_group_id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup retrieved successfully');
    }

    /**
     * Update the specified ReportTemplateAccountGroup in storage.
     * PUT/PATCH /reportTemplateAccountGroups/{id}
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @param UpdateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update($client_id, $report_template_id, $report_template_account_group_id, UpdateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj)
    {
        $ClientObj = Client::find($client_id);
        $input = $ReportTemplateAccountGroupRequestObj->all();
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($report_template_account_group_id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->update($input, $report_template_account_group_id);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'    => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id' => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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

        return $this->sendResponse($ReportTemplateAccountGroupObj, 'ReportTemplateAccountGroup updated successfully');
    }

    /**
     * Remove the specified ReportTemplateAccountGroup from storage.
     * DELETE /reportTemplateAccountGroups/{id}
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $report_template_id, $report_template_account_group_id)
    {
        $ClientObj = Client::find($client_id);
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->findWithoutFail($report_template_account_group_id);
        if (empty($ReportTemplateAccountGroupObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroup not found'), 404);
        }

        $this->ReportTemplateAccountGroupRepositoryObj->delete($report_template_account_group_id);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'    => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id' => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateAccountGroupObj),
                    'event_trigger_object_class_id'    => $ReportTemplateAccountGroupObj->id,
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

        return $this->sendResponse($report_template_account_group_id, 'ReportTemplateAccountGroup deleted successfully');
    }

    /**
     * Store a newly created ReportTemplateAccountGroup in storage.
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @param integer $native_account_id
     * @param CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show_native_account_mapping(
        $client_id,
        $report_template_id,
        $report_template_account_group_id,
        $native_account_id,
        CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
    ) {
        if ( ! $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWhere(
            [
                ['report_template_account_group_id', '=', $report_template_account_group_id],
                ['native_account_id', '=', $native_account_id],
            ]
        )->first())
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping not found'), 404);
        }

        return $this->sendResponse($ReportTemplateMappingObj->id, 'ReportTemplateMapping retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplateAccountGroup in storage.
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @param integer $native_account_id
     * @param CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store_native_account_mapping(
        $client_id,
        $report_template_id,
        $report_template_account_group_id,
        $native_account_id,
        CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
    ) {
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($report_template_id);
        if (in_array($native_account_id, $ReportTemplateObj->getAllNativeAccounts()->pluck('id')->toArray()))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate mapping to this native_account already exists'), 404);
        }

        if ( ! $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->create(
            [
                'report_template_account_group_id' => $report_template_account_group_id,
                'native_account_id'                => $native_account_id,
            ]
        ))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateMapping create failed'), 404);
        }
        return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping saved successfully');
    }

    /**
     * Store a newly created ReportTemplateAccountGroup in storage.
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $report_template_account_group_id
     * @param integer $native_account_id
     * @param CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy_native_account_mapping(
        $client_id,
        $report_template_id,
        $report_template_account_group_id,
        $native_account_id,
        CreateReportTemplateAccountGroupRequest $ReportTemplateAccountGroupRequestObj
    ) {
        if ( ! $ReportTemplateMappingObj = $this->ReportTemplateMappingRepositoryObj->findWhere(
            [
                ['report_template_account_group_id', '=', $report_template_account_group_id],
                ['native_account_id', '=', $native_account_id],
            ]
        )->first())
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }

        if ( ! $this->ReportTemplateMappingRepositoryObj->delete($ReportTemplateMappingObj->id))
        {
            return $this->sendResponse($ReportTemplateMappingObj, 'ReportTemplateMapping saved successfully');
        }

        return $this->sendResponse($ReportTemplateMappingObj->id, 'ReportTemplateMapping deleted successfully');
    }

}
