<?php

namespace App\Waypoint\Exceptions;

/**
 * Class DBQueryException
 * @package App\Waypoint\Exceptions
 */
class DBQueryException extends GeneralException
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
