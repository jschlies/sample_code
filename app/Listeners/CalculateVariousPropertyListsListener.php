<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Jobs\CalculateVariousPropertyListsJob;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AccessListUserFull;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserAdmin;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Models\UserSummary;

/**
 * Class CalculateVariousPropertyListsListener
 * @package App\Waypoint\Listeners
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class CalculateVariousPropertyListsListener extends Listener
{
    /** @var array */
    public $model_class = Client::class;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->job_class = CalculateVariousPropertyListsJob::class;
        $this->queue     = ['QueueName' => config('queue.queue_lanes.CalculateVariousPropertyLists', false)];
        parent::__construct();
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param CalculateVariousPropertyListsEvent|\App\Waypoint\Events\EventBase $EventObj
     * @throws GeneralException
     * @throws ListenerException
     */
    public function handle($EventObj = null)
    {
        $class_of_event_model = $EventObj->getModelArr()['model_name'];
        $parentage_arr        = class_parents($class_of_event_model);
        $model_arr            = array_merge($EventObj->getModelArr(), $this->model_arr, $EventObj->options);

        if ($class_of_event_model == Client::class || in_array(Client::class, $parentage_arr))
        {
            $EventObj->setModelArr(Client::find($model_arr['id'])->toArray());
        }
        elseif (
            $class_of_event_model == User::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserDetail::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserSummary::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserAdmin::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == Heartbeat::class || in_array(User::class, $parentage_arr)
        )
        {
            $model_arr['user_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $UserObj                = User::find($model_arr['id']);
                $model_arr['client_id'] = $UserObj->client_id;
            }

        }
        elseif (
            $class_of_event_model == Property::class || in_array(Property::class, $parentage_arr)
        )
        {
            $model_arr['property_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $PropertyObj            = Property::find($model_arr['id']);
                $model_arr['client_id'] = $PropertyObj->client_id;
            }

        }
        elseif (
            $class_of_event_model == PropertyGroupProperty::class || in_array(PropertyGroupProperty::class, $parentage_arr)
        )
        {
            $model_arr['property_group_property_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {

                $PropertyGroupPropertyObj = PropertyGroupProperty::find($model_arr['id']);
                $model_arr['client_id']   = $PropertyGroupPropertyObj->property->client_id;
            }
        }
        elseif (
            $class_of_event_model == AccessList::class || in_array(AccessList::class, $parentage_arr)
        )
        {
            $model_arr['access_list_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {
                $AccessListObj          = AccessList::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListObj->client_id;
            }
        }
        elseif (
            $class_of_event_model == AccessListProperty::class || in_array(AccessListProperty::class, $parentage_arr)
        )
        {
            $model_arr['access_list_property'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $AccessListPropertyObj  = AccessListProperty::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListPropertyObj->property->client_id;
            }
        }
        elseif (
            $class_of_event_model == AccessListUser::class || in_array(AccessListProperty::class, $parentage_arr) ||
            $class_of_event_model == AccessListUserFull::class || in_array(AccessListUserFull::class, $parentage_arr)
        )
        {
            $model_arr['access_list_user_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {
                $AccessListUserObj      = AccessList::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListUserObj->user->client_id;
            }
        }
        else
        {
            throw new ListenerException('invalid ModelClass of ' . $class_of_event_model . ' at ' . __CLASS__);
        }

        $EventObj->setModelArr($model_arr);
        $this->model_arr = $EventObj->getModelArr();

        parent::handle($EventObj);
    }
}
