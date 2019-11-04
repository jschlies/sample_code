<?php

namespace App\Waypoint\Events;

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcHitEvent extends EventBase implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @param \stdClass $DiagnosticObj
     * @throws GeneralException
     */
    public function __construct($DiagnosticObj = null, $options = [])
    {
        $this->DiagnosticObj = $DiagnosticObj;
    }
}
