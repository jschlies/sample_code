<?php

namespace App\Waypoint\Events;

use App\Waypoint\Models\User;
use Illuminate\Queue\SerializesModels;
use App\Waypoint\Exceptions\GeneralException;

/**
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class UserCreatedEvent extends RepositoryEventBase
{
    /**
     * @var User
     */
    protected $UserObj;

    use SerializesModels;

    /**
     * @param User $UserObj
     * @throws GeneralException
     */
    public function __construct(User $UserObj, $options = [])
    {
        parent::__construct($UserObj, $options, self::class, get_class($this));

        $this->model_arr['client_id'] = $UserObj->client_id;
        $this->model_arr['user_id']   = $UserObj->id;
    }
}
