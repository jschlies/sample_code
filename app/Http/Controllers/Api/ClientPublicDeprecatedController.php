<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use Carbon\Carbon;
use Response;

/**
 * Class ClientPublicDeprecatedController
 * @codeCoverageIgnore
 */
class ClientPublicDeprecatedController extends BaseApiController
{
    /** @var  ClientRepository */
    private $ClientRepositoryObj;

    public function __construct(ClientRepository $ClientRepository)
    {
        $this->ClientRepositoryObj = $ClientRepository;
        parent::__construct($ClientRepository);
    }

    /**
     * @param integer $client_id
     * @param null $property_group_calc_value
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function set_property_group_calc($client_id, $property_group_calc_value = null)
    {
        /** @var Client $ClientObj */
        $ClientObj = $this->ClientRepositoryObj->find($client_id);
        if (empty($ClientObj))
        {
            return Response::json(ResponseUtil::makeError('Client not found'), 404);
        }
        $ClientObj = $this->ClientRepositoryObj->update(
            [
                'property_group_calc_status'         => $property_group_calc_value,
                'property_group_calc_last_requested' => Client::PROPERTY_GROUP_CALC_STATUS_IDLE == $property_group_calc_value ? Carbon::now() : null,
            ],
            $ClientObj->id
        );

        /**
         * trigger the events we need to trigger
         * @todo Is this readdy needed????
         */
        event(
            new CalculateVariousPropertyListsEvent(
                $ClientObj,
                [
                    'event_trigger_message'         => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($ClientObj),
                    'event_trigger_object_class_id' => $ClientObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                ]

            )
        );

        return $this->sendResponse($ClientObj, 'Client created');
    }
}