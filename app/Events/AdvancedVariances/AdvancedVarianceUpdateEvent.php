<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceUpdateEvent extends EventBase
{
    use SerializesModels;

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     * @throws GeneralException
     */
    public function __construct(AdvancedVariance $AdvancedVarianceObj = null, $options = [])
    {
        parent::__construct($AdvancedVarianceObj, $options, self::class, get_class($this));

        $this->model_arr['client_id']            = $AdvancedVarianceObj->property->client_id;
        $this->model_arr['property_id']          = $AdvancedVarianceObj->property_id;
        $this->model_arr['advanced_variance_id'] = $AdvancedVarianceObj->id;
        $this->model_arr['as_of_month']          = $AdvancedVarianceObj->as_of_month;
        $this->model_arr['as_of_year']           = $AdvancedVarianceObj->as_of_year;

        $purge_list = EventBase::build_advanced_varance_purge_list($AdvancedVarianceObj, $options);

        if (isset($options['launch_job_property_id_arr']))
        {
            $this->model_arr['launch_job_property_id_arr'] = $options['launch_job_property_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_id_arr'] = $purge_list['properties'];
        }
        if (isset($options['launch_job_property_group_id_arr']))
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $options['launch_job_property_group_id_arr'];
        }
        else
        {
            $this->model_arr['launch_job_property_group_id_arr'] = $purge_list['property_groups'];
        }

        $this->model_arr['wipe_out_list'] = EventBase::build_advanced_variance_wipe_out_list($AdvancedVarianceObj, $options);
    }
}
