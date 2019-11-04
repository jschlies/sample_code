<?php

namespace App\Waypoint\Providers;

use App\Waypoint\Events\AccessListDeletedEvent;
use App\Waypoint\Events\AccessListPropertyCreatedEvent;
use App\Waypoint\Events\AccessListPropertyDeletedEvent;
use App\Waypoint\Events\AccessListUserCreatedEvent;
use App\Waypoint\Events\AccessListUserDeletedEvent;
use App\Waypoint\Events\AccessListUserUpdatedEvent;
use App\Waypoint\Events\AdvancedVarianceCreateEvent;
use App\Waypoint\Events\AdvancedVarianceThresholdCreatedEvent;
use App\Waypoint\Events\AdvancedVarianceThresholdDeletedEvent;
use App\Waypoint\Events\AdvancedVarianceThresholdUpdatedEvent;
use App\Waypoint\Events\AdvancedVarianceUpdateEvent;
use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Events\ClearDormantUsersEvent;
use App\Waypoint\Events\ClientUpdatedEvent;
use App\Waypoint\Events\ControllerCallActionEvent;
use App\Waypoint\Events\ControllerCallActionMethodEvent;
use App\Waypoint\Events\OpportunityOpenedEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcHitEvent;
use App\Waypoint\Events\PreCalcMissEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Events\PropertyCreatedEvent;
use App\Waypoint\Events\PropertyDeletedEvent;
use App\Waypoint\Events\PropertyGroupDeletedEvent;
use App\Waypoint\Events\PropertyGroupPropertyCreatedEvent;
use App\Waypoint\Events\PropertyGroupPropertyDeletedEvent;
use App\Waypoint\Events\PropertyGroupPropertyUpdatedEvent;
use App\Waypoint\Events\PropertyUpdatedEvent;
use App\Waypoint\Events\ReadFromS3Event;
use App\Waypoint\Events\SendToS3Event;
use App\Waypoint\Events\UserCreatedEvent;
use App\Waypoint\Events\UserDeletedEvent;
use App\Waypoint\Events\UserUpdatedEvent;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Listeners\CalculateVariousPropertyListsListener;
use App\Waypoint\Listeners\ClearDormantUsersListener;
use App\Waypoint\Listeners\ControllerCallActionListener;
use App\Waypoint\Listeners\ControllerCallActionMethodListener;
use App\Waypoint\Listeners\CacheForgottenListener;
use App\Waypoint\Listeners\CacheHitListener;
use App\Waypoint\Listeners\CacheMissedListener;
use App\Waypoint\Listeners\CacheKeyWrittenListener;
use App\Waypoint\Listeners\NotificationLogListener;
use App\Waypoint\Listeners\PreCalcClientListener;
use App\Waypoint\Listeners\PreCalcHitListener;
use App\Waypoint\Listeners\PreCalcMissListener;
use App\Waypoint\Listeners\PreCalcPropertiesListener;
use App\Waypoint\Listeners\PreCalcPropertyGroupsListener;
use App\Waypoint\Listeners\PreCalcUsersListener;
use App\Waypoint\Listeners\ReadFromS3Listener;
use App\Waypoint\Listeners\SendToS3Listener;
use App\Waypoint\Listeners\UserPasswordChangeEmailListener;
use Exception;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\Events\JobProcessed;
use Queue;

/**
 * Class EventServiceProvider
 * @package App\Waypoint\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        ################################
        #
        # Model/Repository Events
        # these are triggered in respective Repository->create()
        #
        ################################

        /** AccessList */
        AccessListDeletedEvent::class            => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        /** AccessListProperty */
        AccessListPropertyCreatedEvent::class    => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],
        AccessListPropertyDeletedEvent::class    => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        /** AccessListUserProperty */
        AccessListUserCreatedEvent::class        => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],
        AccessListUserUpdatedEvent::class        => [],
        AccessListUserDeletedEvent::class        => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        /** Property */
        PropertyCreatedEvent::class              => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],
        PropertyUpdatedEvent::class              => [],
        PropertyDeletedEvent::class              => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        /** PropertyGroup */
        PropertyGroupDeletedEvent::class         => [],

        /** PropertyGroupProperty */
        PropertyGroupPropertyCreatedEvent::class => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],
        PropertyGroupPropertyUpdatedEvent::class => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],
        PropertyGroupPropertyDeletedEvent::class => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        /** User */
        UserCreatedEvent::class                  => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        UserUpdatedEvent::class => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
        ],

        UserDeletedEvent::class => [
            CalculateVariousPropertyListsListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
        ],

        ClientUpdatedEvent::class => [],

        ################################
        # Notification log

        NotificationSent::class                         => [
            NotificationLogListener::class,
        ],

        ################################
        #
        # Non-Model/Non-Repository Events
        # these are triggered on an ad-hoc basis
        #
        ################################
        AdvancedVarianceCreateEvent::class => [],
        AdvancedVarianceUpdateEvent::class => [
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        AdvancedVarianceThresholdCreatedEvent::class => [
            PreCalcClientListener::class,
        ],
        AdvancedVarianceThresholdUpdatedEvent::class => [
            PreCalcClientListener::class,
        ],
        AdvancedVarianceThresholdDeletedEvent::class => [
            PreCalcClientListener::class,
        ],

        CalculateVariousPropertyListsEvent::class => [
            CalculateVariousPropertyListsListener::class,
        ],

        ClearDormantUsersEvent::class => [
            ClearDormantUsersListener::class,
            PreCalcClientListener::class,
            PreCalcUsersListener::class,
            PreCalcPropertiesListener::class,
            PreCalcPropertyGroupsListener::class,
        ],

        PreCalcClientEvent::class => [
            PreCalcClientListener::class,
        ],

        PreCalcUsersEvent::class => [
            PreCalcUsersListener::class,
        ],

        PreCalcPropertiesEvent::class => [
            PreCalcPropertiesListener::class,
        ],

        PreCalcPropertyGroupsEvent::class => [
            PreCalcPropertyGroupsListener::class,
        ],

        CacheHit::class                        => [
            CacheHitListener::class,
        ],
        CacheMissed::class                     => [
            CacheMissedListener::class,
        ],
        KeyWritten::class                      => [
            CacheKeyWrittenListener::class,
        ],
        KeyForgotten::class                    => [
            CacheForgottenListener::class,
        ],
        ControllerCallActionEvent::class       => [
            ControllerCallActionListener::class,
        ],
        ControllerCallActionMethodEvent::class => [
            ControllerCallActionMethodListener::class,
        ],
        PreCalcHitEvent::class                 => [
            PreCalcHitListener::class,
        ],
        PreCalcMissEvent::class                => [
            PreCalcMissListener::class,
        ],
        ReadFromS3Event::class                 => [
            ReadFromS3Listener::class,
        ],
        SendToS3Event::class                   => [
            SendToS3Listener::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function boot()
    {
        parent::boot();

        Queue::after(
            function (JobProcessed $JobProcessedObj)
            {
                try
                {
                    /**
                     * after a Event/Listener/Job have finished, if we
                     * ever want to trigger an event or
                     * do anything for that matter, put it here
                     */
                }
                catch (ListenerException $e)
                {
                    throw $e;
                }
                catch (Exception $e)
                {
                    throw new ListenerException(__CLASS__ . ' Queue ' . $JobProcessedObj->job->getName() . ' at ' . __FILE__ . ':' . __LINE__, 404, $e);
                }
            }
        );
    }
}
