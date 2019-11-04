<?php

namespace App\Waypoint\Exceptions;

use Illuminate\Support\MessageBag;

/**
 * Class ValidationException
 * @package App\Waypoint\Exceptions
 */
class ValidationException extends GeneralException
{
    protected $validation_errors = null;

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return MessageBag
     */
    public function getValidationErrors()
    {
        return $this->validation_errors;
    }

    /**
     * @param MessageBag $validation_errors
     */
    public function setValidationErrors($validation_errors)
    {
        $this->validation_errors = $validation_errors;
    }
}
