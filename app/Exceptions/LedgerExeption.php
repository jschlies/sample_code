<?php

namespace App\Waypoint\Exceptions;

/**
 * Class LedgerException
 * @package App\Waypoint\Exceptions
 */
class LedgerException extends GeneralException
{
    public $api_class_name = null;
    public $warning_message = null;
    public $error_message = null;
    public $api_display_name = null;

    public function __construct($message, $code = 404, $class_object = null, \Exception $previous = null)
    {
        if ($class_object)
        {
            $this->api_display_name = $class_object->apiDisplayName;
        }
        parent::__construct($message, $code, $previous);
    }
}
