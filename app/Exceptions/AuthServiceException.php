<?php

namespace App\Waypoint\Exceptions;

use Exception;

/**
 * Class ValidationException
 * @package App\Waypoint\Exceptions
 */
class AuthServiceException extends GeneralException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        if ( ! $code)
        {
            $code = 403;
        }
        return parent::__construct($message, $code, $previous);
    }
}
