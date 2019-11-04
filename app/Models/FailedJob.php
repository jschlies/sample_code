<?php

namespace App\Waypoint\Models;

/**
 * Class FailedJob
 * @package App\Waypoint\Models
 */
class FailedJob extends FailedJobModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        /**
         * @todo - fix this - see HER-3124
         */
        return [
            "id"         => $this->id,
            'connection' => $this->getAttribute('connection'),
            "queue"      => $this->queue,
            "payload"    => $this->payload,
            "failed_at"  => $this->perhaps_format_date($this->failed_at),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
