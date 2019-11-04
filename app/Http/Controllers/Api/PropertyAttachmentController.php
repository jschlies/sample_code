<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\Property;
use App\Waypoint\ResponseUtil;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Response;
use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyRequest;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;

class PropertyAttachmentController extends BaseApiController
{
    use Notifiable;

    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;

    public function __construct(PropertyRepository $PropertyRepositoryObj)
    {
        $this->PropertyRepositoryObj = $PropertyRepositoryObj;
        parent::__construct($this->PropertyRepositoryObj);
    }

    /**
     * @param $client_id
     * @param $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showPropertyAttachments($client_id, $property_id)
    {
        $PropertyObj = $this->PropertyRepositoryObj->find($property_id);
        if (empty($PropertyObj))
        {
            return Response::json(ResponseUtil::makeError('Opportunity not found'), 404);
        }

        return $this->sendResponse($PropertyObj->getAttachments(), 'Attachment(s) retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param CreatePropertyRequest $PropertyRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function storePropertyAttachments($client_id, $property_id, CreatePropertyRequest $PropertyRequestObj)
    {
        /** @var Property $PropertyObj */
        if ( ! $PropertyObj = $this->PropertyRepositoryObj->find($property_id))
        {
            throw new ModelNotFoundException('No such Opportunity');
        }

        $input = $PropertyRequestObj->all();
        if (count($PropertyRequestObj->allFiles()) !== 1)
        {
            throw new GeneralException('Please upload one attachment at a time');
        }

        try
        {
            $input['attachable_type'] = Property::class;

            /** @var SymfonyUploadedFile $FileObj */
            foreach ($PropertyRequestObj->allFiles() as $FileObj)
            {
                $PropertyObj->attach(
                    $FileObj->getRealPath(),
                    [
                        'disk'               => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                        'title'              => $FileObj->getFilename(),
                        'description'        => $FileObj->getFilename(),
                        'key'                => $FileObj->getClientOriginalName(),
                        'created_by_user_id' => $this->getCurrentLoggedInUserObj()->id,
                    ]
                );
            }
            event(
                new PreCalcPropertiesEvent(
                    $PropertyObj->client,
                    [
                        'event_trigger_message'         => '',
                        'event_trigger_id'              => waypoint_generate_uuid(),
                        'event_trigger_class'           => self::class,
                        'event_trigger_class_instance'  => get_class($this),
                        'event_trigger_object_class'    => get_class($PropertyObj),
                        'event_trigger_object_class_id' => $PropertyObj->id,
                        'event_trigger_absolute_class'  => __CLASS__,
                        'event_trigger_file'            => __FILE__,
                        'event_trigger_line'            => __LINE__,
                        'wipe_out_list'                 =>
                            [
                                'properties' => [],
                            ],
                        'launch_job_property_id_arr'    => [$PropertyObj->id],
                    ]
                )
            );

            event(
                new PreCalcPropertyGroupsEvent(
                    $PropertyObj->client,
                    [
                        'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PropertyObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                        'wipe_out_list'                =>
                            [
                                'property_groups' => [],
                            ],
                        'launch_job_property_group_id_arr'    => $PropertyObj->propertyGroups->pluck('id')->toArray(),
                    ]
                )
            );
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($PropertyObj->attachment($FileObj->getClientOriginalName()), 'Attachment saved successfully');
    }

}
