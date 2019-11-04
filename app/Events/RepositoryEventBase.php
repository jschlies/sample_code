<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\Entrust\User as EntrustUser;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class RepositoryEventBase extends EventBase
{
    /**
     * Validation rules
     *
     * @var array
     *
     * Remember that only certain
     * repository models have events defined. This is that list.
     * See (for example) AccessListPropertyRepositoryBase->create().
     *
     * Note that this only comes into play for repository generated events.
     *
     * @todo FIX ME deal with the entrust objects, User, Role and Permission CANNOT be in this list
     */
    private static $enabled_model_repository_events = [
        Client::class,
        Property::class,
        AccessList::class,
        AccessListProperty::class,
        AccessListUser::class,
        PropertyGroup::class,
        PropertyGroupProperty::class,
        AdvancedVarianceThreshold::class,
    ];

    /**
     * NOTE NOTE NOTE
     * PostMigrationEvent had no param to pass in
     * Depending on how we use events in the future, this may need attention
     *
     * Remember that only certain
     * repository models have events defined. This is that list.
     * See (for example) AccessListPropertyRepositoryBase->create().
     *
     * Note that this only comes into play for repository generated events.
     *
     * EventBase constructor.
     * @param $ModelObj
     * @throws GeneralException
     */
    public function __construct($ModelObj = null, $options = [], $event_trigger_event_class = null, $event_trigger_event_class_instance = null)
    {
        if (
            $ModelObj &&
            ! $ModelObj instanceof Model &&
            ! $ModelObj instanceof EntrustUser
        )
        {
            throw new GeneralException('Event Model not User or Client');
        }
        parent::__construct($ModelObj, $options, $event_trigger_event_class, $event_trigger_event_class_instance);
    }

    /**
     * @return array
     */
    public static function getEnabledModelRepositoryEvents()
    {
        return self::$enabled_model_repository_events;
    }
}
