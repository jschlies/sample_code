<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NotificationLog;
use Carbon\Carbon;
use Prettus\Validator\Exceptions\ValidatorException;

class NotificationLogRepository extends NotificationLogRepositoryBase
{
    /**
     * Save a new NotificationLog in repository
     *
     * @param array $attributes
     * @return NotificationLog
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $attributes['notification_time'] = Carbon::now()->format('Y-m-d H:i:s');
        if ( ! isset($attributes['data_json']) || ! $attributes['data_json'])
        {
            $attributes['data_json'] = json_encode(['a' => 123]);
        }
        if ( ! isset($attributes['user_json']) || ! $attributes['user_json'])
        {
            $attributes['user_json'] = json_encode(['a' => 123]);
        }
        return parent::create($attributes);
    }
}
