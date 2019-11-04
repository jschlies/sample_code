<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceSlim;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class EventBase implements ShouldBroadcast
{
    /**
     * @var []
     */
    public $model_arr;

    /**
     * @var \stdClass
     */
    public $DiagnosticObj;

    /**
     * @var string
     */
    protected $action;

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

    public $options = [];

    public function __construct($ModelObj = null, $options = [], $event_trigger_event_class = null, $event_trigger_event_class_instance = null)
    {
        if ( ! is_object($ModelObj))
        {
            throw new GeneralException('Event Model must be an object');
        }

        $this->options                                       = $options;
        $this->options['event_trigger_event_class']          = $event_trigger_event_class;
        $this->options['event_trigger_event_class_instance'] = $event_trigger_event_class_instance;

        $this->model_arr['wipe_out_list'] = [];
        $this->model_arr['id']            = $ModelObj->id;
        $this->model_arr['model_name']    = get_class($ModelObj);
    }

    /**
     * @return []
     */
    public function getModelArr()
    {
        return $this->model_arr;
    }

    /**
     * @param array $model_arr
     */
    public function setModelArr($model_arr)
    {
        $this->model_arr = $model_arr;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    /**
     * @param AdvancedVariance | AdvancedVarianceSlim $AdvancedVarianceObj
     * @param array $options
     * @return array
     */
    static public function build_advanced_varance_purge_list($AdvancedVarianceObj, array $options = [])
    {
        if (isset($options['purge_list']))
        {
            $return_me_purge_list = $options['purge_list'];
            if ( ! isset($return_me_purge_list['properties']))
            {
                $return_me_purge_list['properties'] = $AdvancedVarianceObj->client->properties->pluck('id')->toArray();
            }
            if ( ! isset($return_me_purge_list['property_groups']))
            {
                $return_me_purge_list['properties'] = $AdvancedVarianceObj->client->propertyGroups->pluck('id')->toArray();
            }
        }
        else
        {
            $return_me_purge_list = [
                'properties'      => [$AdvancedVarianceObj->property_id],
                'property_groups' => $AdvancedVarianceObj->property->propertyGroups->pluck('id')->toArray(),
            ];
        }

        return $return_me_purge_list;
    }

    static public function build_advanced_variance_wipe_out_list(AdvancedVariance $AdvancedVarianceObj, array $options = [])
    {
        $return_me_wipe_out_list = [];
        if (isset($options['wipe_out_list']))
        {
            $return_me_wipe_out_list = $options['wipe_out_list'];
        }
        else
        {
            $padded_month                            = str_pad($AdvancedVarianceObj->as_of_month, 2, "0", STR_PAD_LEFT);
            $return_me_wipe_out_list['properties']   = [];
            $key                                     = 'advancedVarianceSummaries_property_' . $AdvancedVarianceObj->property_id . '_' . $AdvancedVarianceObj->as_of_year . '_' . $padded_month;
            $return_me_wipe_out_list['properties'][] = $key;
            $key                                     = 'advancedVarianceSummaries_property_' . $AdvancedVarianceObj->property_id;
            $return_me_wipe_out_list['properties'][] = $key;

            $return_me_wipe_out_list['property_groups'] = [];
            foreach ($AdvancedVarianceObj->property->propertyGroups as $PropertyGroupObj)
            {
                $key                                          = 'AdvancedVarianceSummaryByPropertyGroupId_' . $PropertyGroupObj->id;
                $return_me_wipe_out_list['property_groups'][] = $key;
                $key                                          = 'AdvancedVarianceSummaryByPropertyGroupId_' . $padded_month . '_' . $AdvancedVarianceObj->as_of_year . '_' . $PropertyGroupObj->id;
                $return_me_wipe_out_list['property_groups'][] = $key;
                $key                                          = 'unique_advanced_variance_dates_property_group_' . $PropertyGroupObj->id;
                $return_me_wipe_out_list['property_groups'][] = $key;
            }
        }
        return $return_me_wipe_out_list;
    }
}
