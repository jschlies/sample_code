<?php

namespace App\Waypoint\Notifications\Facades;

use Illuminate\Support\Facades\Notification as NotificationFacadeBase;
use App\Waypoint\Tests\NotificationFake;

/**
 * @see \Illuminate\Notifications\ChannelManager
 */
class Notification extends NotificationFacadeBase
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        parent::swap(new NotificationFake);
    }
}
