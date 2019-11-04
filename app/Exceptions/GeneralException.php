<?php

namespace App\Waypoint\Exceptions;

use App;
use function method_exists;
use RuntimeException;

/**
 * Class GeneralException
 * @package App\Waypoint\Exceptions
 */
class GeneralException extends RuntimeException
{
    protected $exception_as_string;

    /**
     * @return string
     */
    public function getExceptionAsString(): string
    {
        if ( ! $this->exception_as_string)
        {
            $this->exception_as_string = self::get_exception_as_string($this);
        }
        return $this->exception_as_string;
    }

    /**
     * @param string $exception_as_string
     */
    public function setExceptionAsString(string $exception_as_string)
    {
        $this->exception_as_string = $exception_as_string;
    }

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->exception_as_string = self::get_exception_as_string($this);
    }

    /**
     * @param \Exception $e
     * @param $caller
     * @return string
     */
    public static function standard_exception_message(\Exception $e, $caller)
    {
        $message = 'Exception ' . get_class($e) . ' encountered at ' .
                   get_class($caller) . ' ' . __FILE__ . ':' . __LINE__ . ' ' . $e->getMessage() . ' ' . $e->getTraceAsString();
        return $message;
    }

    /**
     * @param bool $previous
     * @param string $message
     * @return string
     */
    private static function get_exception_as_string(\Exception $ExceptionObj, $previous = false, $message = '')
    {
        if ( ! $previous)
        {
            $message .= '*******************************' . PHP_EOL;
        }
        $message .= 'Exception of class ' . get_class($ExceptionObj) . ' has been detected:' . PHP_EOL;
        $message .= 'Message:' . $ExceptionObj->getMessage() . PHP_EOL;
        $message .= '*******************************' . PHP_EOL;
        $message .= 'File: ' . $ExceptionObj->getFile() . PHP_EOL;
        $message .= 'Line: ' . $ExceptionObj->getLine() . PHP_EOL;
        $message .= '*******************************' . PHP_EOL;
        $message .= $ExceptionObj->getTraceAsString() . PHP_EOL;
        if ($ExceptionObj->getPrevious())
        {
            $message .= '*******************************' . PHP_EOL;
            if ($ExceptionObj->getPrevious())
            {
                if (method_exists($ExceptionObj->getPrevious(), 'get_exception_as_string'))
                {
                    $message .= 'Previous Exception: ' . GeneralException::get_exception_as_string($ExceptionObj->getPrevious(), $message) . PHP_EOL;
                }
                else
                {
                    $message .= 'Previous Exception: ' . $ExceptionObj->getPrevious()->getMessage() . PHP_EOL;
                    $message .= 'Trace: ' . $ExceptionObj->getPrevious()->getTraceAsString() . PHP_EOL;
                }
            }
        }
        if ( ! $previous)
        {
            $message .= '*******************************' . PHP_EOL;
        }
        return $message;
    }

    /**
     * @return array
     */
    public
    function toArray()
    {
        $error = [
            'message'        => $this->getMessage() ?: 'Exception of type ' . get_class($this) . ' has occurred',
            'code'           => $this->getCode(),
            'exception_type' => get_class($this),
            'environment'    => App::environment(),
        ];
        if (App::environment() !== 'production' && config('waypoint.detailed_exceptions', false))
        {
            /**
             * this should only happen in a development context
             */
            $error['trace'] = $this->getTrace();
            $error['file']  = $this->getFile();
            $error['line']  = $this->getLine();
            if ($this->getPrevious())
            {
                if (method_exists($this->getPrevious(), 'toArray'))
                {
                    $error['previous'] = $this->getPrevious()->toArray();
                }
                else
                {
                    $error['previous']['message'] = $this->getPrevious()->getMessage();
                    $error['previous']['file']    = $this->getPrevious()->getFile();
                    $error['previous']['line']    = $this->getPrevious()->getLine();
                    $error['previous']['trace']   = $this->getPrevious()->getTraceAsString();
                }
            }
        }
        return $error;
    }
}
