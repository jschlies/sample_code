<?php

namespace App\Waypoint;

use Illuminate\Session\FileSessionHandler as FileSessionHandlerBase;

class FileSessionHandler extends FileSessionHandlerBase
{
    public function read($sessionId)
    {
        if ($sessionData = parent::read($sessionId))
        {
            if ( ! $sessionData)
            {

            }
        }
    }
}