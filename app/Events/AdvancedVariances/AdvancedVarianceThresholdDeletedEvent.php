<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\AdvancedVarianceThreshold;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class AdvancedVarianceThresholdDeletedEvent extends RepositoryEventBase
{
    use SerializesModels;

    /**
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
