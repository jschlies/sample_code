<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateReportTemplateRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateReportTemplateRequest;
use App\Waypoint\Models\Client;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class ReportTemplateController
 */
class ReportTemplateController extends BaseApiController
{
    /** @var  ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;

    public function __construct(ReportTemplateRepository $ReportTemplateRepositoryObj)
    {
        $this->ReportTemplateRepositoryObj = $ReportTemplateRepositoryObj;
        parent::__construct($ReportTemplateRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showForClient($client_id)
    {
        /** @var ReportTemplate[] $ReportTemplateObjArr */
        $ReportTemplateObjArr = $this->ReportTemplateRepositoryObj
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupChildren')
            ->with('reportTemplateAccountGroups.reportTemplateMappings.nativeAccountDetail')
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($ReportTemplateObjArr, 'ReportTemplate(s) retrieved successfully');
    }

    /**
     * Store a newly created ReportTemplate in storage.
     *
     * @param CreateReportTemplateRequest $ReportTemplateRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(CreateReportTemplateRequest $ReportTemplateRequestObj, $client_id)
    {
        $ClientObj = Client::find($client_id);
        $input = $ReportTemplateRequestObj->all();

        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->create($input);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'    => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id' => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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

        return $this->sendResponse($ReportTemplateObj, 'ReportTemplate saved successfully');
    }

    /**
     * Update the specified ReportTemplate in storage.
     * PUT/PATCH /reportTemplates/{id}
     *
     * @param integer $id
     * @param UpdateReportTemplateRequest $ReportTemplateRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function update(UpdateReportTemplateRequest $ReportTemplateRequestObj, $client_id, $report_template_id)
    {
        $ClientObj = Client::find($client_id);
        $input = $ReportTemplateRequestObj->all();
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($report_template_id);
        if (empty($ReportTemplateObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->update($input, $report_template_id);

        event(
            new PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'    => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id' => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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
                    'event_trigger_object_class'       => get_class($ReportTemplateObj),
                    'event_trigger_object_class_id'    => $ReportTemplateObj->id,
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

        return $this->sendResponse($ReportTemplateObj, 'ReportTemplate updated successfully');
    }

    /**
     * Gets a list of RTAGS and Calculated fields related to a report template which has is_summary property = true
     * @param integer $client_id
     * @param integer $report_template_id // The report template in question\
     * @return JsonResponse|null
     */
    public function getSummaryAccounts($client_id, $report_template_id)
    {
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->findWithoutFail($report_template_id);

        //Gathering the rtags with is_summary = true
        $ReportTemplateAccountGroupsObjArr =
            $ReportTemplateObj->reportTemplateAccountGroups()
                              ->where('is_summary', true)
                              ->get();
        //Gathering the calculated fields with is_summary = true
        $CalculatedFieldsObjArr =
            $ReportTemplateObj->calculatedFields()
                              ->where('is_summary', true)
                              ->get();
        $ResponseObj            = [
            'report_template_account_groups' => $ReportTemplateAccountGroupsObjArr->toArray(),
            'calculated_fields'              => $CalculatedFieldsObjArr->toArray(),
        ];
        return $this->sendResponse($ResponseObj, 'Information loaded successfully');

    }
}
