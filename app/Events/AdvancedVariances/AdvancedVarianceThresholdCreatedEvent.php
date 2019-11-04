<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use Illuminate\Queue\SerializesModels;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceThresholdCreatedEvent extends RepositoryEventBase
{
    /**
     * @var AccessList
     */
    protected $AdvancedVarianceThresholdObj;

    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * AccessListCreatedEvent constructor.
     *
     * @param AdvancedVarianceThreshold $AdvancedVarianceThresholdObj
     * @throws GeneralException
     */
    public function __construct(AdvancedVarianceThreshold $AdvancedVarianceThresholdObj, $options = [])
    {
        parent::__construct($AdvancedVarianceThresholdObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $AdvancedVarianceThresholdObj->client_id;

        $this->model_arr['wipe_out_list'] = [
            'clients' => [
                'defaultAdvancedVarianceThresholds_',
            ],
        ];
    }
}
