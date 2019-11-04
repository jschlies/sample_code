<?php

namespace App\Waypoint\Models;

/**
 * Class NotificationLog
 * @package App\Waypoint\Models
 */
class NotificationLog extends NotificationLogModelBase
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                => $this->id,
            "notification_time" => $this->notification_time->format('Y-m-d H:i:s'),
            "notification_uuid" => $this->notification_uuid,
            "user_id"           => $this->user_id,
            "user_json"         => $this->user_json,
            "data_json"         => $this->data_json,
            "channel"           => $this->channel,
            "queue"             => $this->queue,
            "response"          => $this->response,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'notification_time' => 'required',
        'notification_uuid' => 'sometimes',
        'user_id'           => 'required|integer',
        'user_json'         => 'sometimes',
        'channel'           => 'sometimes',
        'queue'             => 'sometimes',
        'response'          => 'sometimes',
        'data_json'         => 'sometimes',
    ];
}
