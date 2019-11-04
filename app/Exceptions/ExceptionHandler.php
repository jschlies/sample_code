<?php

namespace App\Waypoint\Exceptions;

use App\Waypoint\Graylog;
use \Gelf\Message as Gelf_Message;
use \Psr\Log\LogLevel as Psr_Log_LogLevel;
use App;
use App\Waypoint\Http\ApiGuardAuth;
use App\Waypoint\Models\User;
use App\Waypoint\ResponseUtil;
use App\Waypoint\Rollbar;
use Auth;
use Exception;
use function in_array;
use function json_encode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandlerBase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Exception\NotFoundException;
use Redirect;
use Response;
use Rollbar\Payload\Level;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler
 * @package App\Waypoint\Exceptions
 */
class ExceptionHandler extends ExceptionHandlerBase
{
    /**
     * A list of the exception types that should not be reported.
     * @var array
     *
     * DO NOT report to Laravel Log
     * DO NOT report to Rollbar
     * DO NOT report to Graylog
     */
    protected $dontReport = [
        ModelNotFoundException::class,
        ValidationException::class,
        CommandNotFoundException::class,
    ];

    /**
     * A list of the exception types that should not be reported.
     * - this list also suppresses Rollbar logging
     * @var array
     *
     * DO NOT report to Rollbar
     *
     * list either the fq class name in question or a
     * exception_class/exception_message ordered pair
     */
    protected $dontReportToRollbar = [
        PolicyException::class,
        AuthServiceException::class,
        [
            'exception_class'   => GeneralException::class,
            'exception_message' => 'auth issue',
        ],
        NotFoundException::class,
        NotFoundHttpException::class,
        HttpException::class,
    ];

    /**
     * A list of the exception types that should not be reported
     * to either Laravel log, Rollbar or Graylog
     *
     * @var array
     *
     * DO NOT report to Laravel DAILY Log
     */
    protected $dontReportToLaravelLogger = [
    ];

    /**
     * A list of the exception types that should not be reported
     * to either Laravel log, Rollbar or Graylog
     *
     * @var array
     *
     * DO NOT report to Laravel DAILY Log
     */
    protected $dontReportToGraylog = [];

    /** @var Gelf_Message */
    protected $GraylogMessage = null;

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $ExceptionObj
     */
    public function report(Exception $ExceptionObj)
    {
        if (
            ! config('services.laravel_logger.enabled', false) &&
            ! config('services.rollbar.enabled', false) &&
            ! config('services.graylog.enabled', false)
        )
        {
            return;
        }

        /**
         * save off $ExceptionObj to $OriginalExceptionObj so we can determine if
         * $OriginalExceptionObj is or is not the type of exception we want to report
         * to Lavarel/RollLog/Rollbar/Graylog/Greybar..........
         */
        $OriginalExceptionObj = $ExceptionObj;
        /**
         * make sure we have a GeneralException here
         */
        if ( ! $ExceptionObj instanceof GeneralException)
        {
            $ExceptionObj = new GeneralException($ExceptionObj->getMessage(), 404, $ExceptionObj);
        }

        if (config('services.laravel_logger.enabled', false))
        {
            if ($this->shouldReportToLaravelLogger($OriginalExceptionObj))
            {
                try
                {
                    Log::error($ExceptionObj->getExceptionAsString());
                }
                catch (Exception $ExceptionObj)
                {
                    error_log('logging with Laravel failed ' . $ExceptionObj->getMessage());
                }
            }
        }

        /**
         * do not send to Rollbar when we have chosen to suppress in the logs using $dontReport above
         */
        if (config('services.rollbar.enabled', false))
        {
            try
            {
                if ($this->shouldReportToRollbar($OriginalExceptionObj))
                {
                    /** @var \Rollbar\Response $RollBarResponseObj */
                    $RollBarResponseObj = Rollbar::log(Level::error(), $ExceptionObj);
                    if ( ! $RollBarResponseObj->wasSuccessful())
                    {
                        error_log('logging with Rollbar failed', 500);
                    }
                }
            }
            catch (Exception $ExceptionObj)
            {
                error_log('logging with Rollbar failed ' . $ExceptionObj->getMessage(), 500);
            }
        }

        /**
         * do not send to Rollbar when we have chosen to suppress in the logs using $dontReport above
         */
        if (config('services.graylog.enabled', false))
        {
            if ($this->shouldReportToGraylog($OriginalExceptionObj))
            {
                try
                {
                    $GraylogObj = new Graylog();

                    $this->GraylogMessage = new Gelf_Message();
                    $this->GraylogMessage->setShortMessage($ExceptionObj->getMessage())
                                         ->setLevel(Psr_Log_LogLevel::ALERT)
                                         ->setFullMessage($ExceptionObj->getExceptionAsString())
                                         ->setFacility('hermes')
                                         ->setFile($ExceptionObj->getFile())
                                         ->setLine($ExceptionObj->getLine())
                                         ->setHost(gethostname());

                    $this->addAdditionalGraylogData();
                    $GraylogObj->publisher->publish($this->GraylogMessage);
                }
                catch (Exception $ExceptionObj)
                {
                    error_log('logging with Graylog failed ' . $ExceptionObj->getMessage());
                }
            }
        }
    }

    public function addAdditionalGraylogData()
    {
        $SystemInformationRepositoryObj = App::make(App\Waypoint\Repositories\SystemInformationRepository::class);

        $system_info = $SystemInformationRepositoryObj->generate_system_information();
        foreach ($system_info['laravel_config'] as $i => $value)
        {
            if (in_array($i, ['ide-helper', 'database']))
            {
                continue;
            }
            $this->GraylogMessage->setAdditional(
                'laravel_config_' . $i,
                json_encode(
                    $value
                )
            );
        }
        foreach ($system_info['php']['phpinfo'] as $i => $value)
        {
            if (in_array($i, ['laravel_config', 'Core']))
            {
                continue;
            }
            $this->GraylogMessage->setAdditional(
                'phpinfo_' . $i,
                json_encode(
                    $value
                )
            );
        }

        if (isset($_SERVER['REQUEST_URI']))
        {
            $this->GraylogMessage->setAdditional("uri", $_SERVER['REQUEST_URI']);
        }

        if (isset($_SERVER['REQUEST_METHOD']))
        {
            $this->GraylogMessage->setAdditional("method", $_SERVER['REQUEST_METHOD']);
        }
        if (Auth::getUser() && Auth::getUser()->id)
        {
            /** @var User $UserObj */
            $UserObj = Auth::getUser();
            $this->GraylogMessage->setAdditional("user_id", $UserObj->id);
            $this->GraylogMessage->setAdditional("client_id", $UserObj->client_id);
            $this->GraylogMessage->setAdditional("roles", $UserObj->getRolesAsString());
        }
        elseif (ApiGuardAuth::getUser() && ApiGuardAuth::getUser()->id)
        {
            /** @var User $UserObj */
            $UserObj = ApiGuardAuth::getUser();
            $this->GraylogMessage->setAdditional("user_id", $UserObj->id);
            $this->GraylogMessage->setAdditional("client_id", $UserObj->client_id);
            $this->GraylogMessage->setAdditional("roles", $UserObj->getRolesAsString());

        }
        $this->GraylogMessage->setAdditional("environment", env('APP_ENV'));
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $RequestObj
     * @param \Exception $ExceptionObj
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($RequestObj, Exception $ExceptionObj)
    {
        if ($this->isApiCall($RequestObj))
        {
            /**
             * HEY YOU - YEA YOU!!!!
             * don't forget to update app/Http/Controllers/BaseControllers/ApiController.php method callAction
             */
            if ($ExceptionObj instanceof ModelNotFoundException)
            {
                return $this->getJsonResponseForModelNotFoundException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof AuthorizationException)
            {
                return Redirect::intended('/');
            }
            elseif ($ExceptionObj instanceof HttpException)
            {
                return $this->getJsonResponseForHttpException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof ValidationException)
            {
                return $this->getJsonResponseForValidationException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof UploadException)
            {
                return $this->getJsonResponseForUploadException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof EntityTagException)
            {
                return $this->getJsonResponseForEntityTagException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof DBQueryException)
            {
                return $this->getJsonResponseForDBQueryException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof AuthServiceException)
            {
                return $this->getJsonResponseForAuthServiceException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof InvalidJSONException)
            {
                return $this->getJsonResponseForInvalidJSONException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof ImageRepositoryException)
            {
                return $this->getJsonResponseForImageRepositoryException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof DeploymentException)
            {
                return $this->getJsonResponseForDeploymentException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof SmartyStreetsException)
            {
                return $this->getJsonResponseForSmartyStreetsException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof DaemonException)
            {
                return $this->getJsonResponseForDaemonException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof EventException)
            {
                return $this->getJsonResponseForEventException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof ListenerException)
            {
                return $this->getJsonResponseForListenerException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof QueueException)
            {
                return $this->getJsonResponseForQueueException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof JobException)
            {
                return $this->getJsonResponseForJobException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof PolicyException)
            {
                return $this->getJsonResponseForPolicyException($ExceptionObj);
            }
            elseif ($ExceptionObj instanceof GeneralException)
            {
                return $this->getJsonResponseForGeneralException($ExceptionObj);
            }
            /**
             * HEY YOU - YEA YOU!!!!
             * don't forget to update app/Http/Controllers/BaseControllers/ApiController.php method callAction
             */
        }

        return parent::render($RequestObj, $ExceptionObj);
    }

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        parent::renderForConsole($output, $e);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param HttpException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForHttpException(HttpException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Route unavailable', 401);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param ModelNotFoundException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForModelNotFoundException(ModelNotFoundException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Object unavailable', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param AuthorizationException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForAuthorizationException(AuthorizationException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Invalid user', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param ValidationException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForValidationException(ValidationException $ExceptionObj)
    {
        $errors = [];
        if ($ExceptionObj->getValidationErrors())
        {
            $errors = $ExceptionObj->getValidationErrors()->toArray();
        }
        return $this->getJsonResponseForException($ExceptionObj, 'Validation Error', 404, $errors);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param UploadException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForUploadException(UploadException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Upload error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param InvalidJSONException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForInvalidJSONException(InvalidJSONException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'InvalidJSONException error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param EntityTagException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForEntityTagException(EntityTagException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'EntityTag error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param DBQueryException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForDBQueryException(DBQueryException $ExceptionObj)
    {
        $Response_message = $ExceptionObj->getMessage();
        if ($ExceptionObj->getPrevious())
        {
            $Response_message .= ' - ' . $ExceptionObj->getPrevious()->getMessage();
        }
        return $this->getJsonResponseForException($ExceptionObj, $Response_message, 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param AuthServiceException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForAuthServiceException(AuthServiceException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'AuthService error', $ExceptionObj->getCode());
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param ImageRepositoryException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForImageRepositoryException(ImageRepositoryException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'ImageRepository error', $ExceptionObj->getCode());
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param DeploymentException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForDeploymentException(DeploymentException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Deployment error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param SmartyStreetsException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForSmartyStreetsException(SmartyStreetsException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'SmartyStreets error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param DaemonException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForDaemonException(DaemonException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Daemon error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param EventException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForEventException(EventException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Event error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param ListenerException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForListenerException(ListenerException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Listener error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param JobException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForJobException(JobException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Job error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     * @param QueueException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForQueueException(QueueException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'Queue error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param PolicyException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForPolicyException(PolicyException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'PolicyException error', 404);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param GeneralException $ExceptionObj
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForGeneralException(GeneralException $ExceptionObj)
    {
        return $this->getJsonResponseForException($ExceptionObj, 'General error', 404);
    }

    /**
     * Returns json response for generic bad request.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest($message = 'Bad request', $statusCode = 400)
    {
        return $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound($message = 'Record not found', $statusCode = 404)
    {
        return $this->jsonResponse(['error' => [$message]], $statusCode);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(array $payload = null, $statusCode = 404)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isModelNotFoundException(Exception $e)
    {
        return $e instanceof ModelNotFoundException;
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isHttpException(Exception $e)
    {
        return $e instanceof HttpException;
    }

    /**
     * Determines if the given exception is an Eloquent model not found.
     *
     * @param Exception $e
     * @return bool
     */
    protected function isValidationException(Exception $e)
    {
        return $e instanceof ValidationException;
    }

    /**
     * Determines if request is an api call.
     *
     * If the request URI contains '/api/v'.
     *
     * @param Request $request
     * @return bool
     */
    protected function isApiCall(Request $request)
    {
        return
            (strpos($request->getUri(), '/api/v') !== false) ||
            (strpos($request->getUri(), 'auth0/callback') !== false);
    }

    /**
     * @param $result
     * @param $message
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse'
     */
    public function sendResponse($result, $message, $errors = [], $warnings = [], $metadata = [], $statusCode = 404)
    {
        return Response::json(ResponseUtil::makeError($message, $result, $errors, $warnings, $metadata), $statusCode);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Exception $ExceptionObj
     * @param null $Response_message
     * @param int $statusCode
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getJsonResponseForException(
        Exception $ExceptionObj,
        $Response_message = null,
        $statusCode = 404,
        $errors = [],
        $warnings = [],
        $metadata = []
    ) {
        if (
            ! is_a($ExceptionObj, GeneralException::class) &&
            ! is_a($ExceptionObj, HttpException::class)
        )
        {
            /** @var GeneralException $ExceptionObj */
            $ExceptionObj = new GeneralException($ExceptionObj->getMessage(), 404, $ExceptionObj);
        }
        if ( ! $Response_message)
        {
            /**
             * if we're here then that means that method getJsonResponseFor?????????Exception() is not formatting an
             * apropiate message. This may be good or bad. Probably bad. If you are
             */
            $Response_message = $ExceptionObj->getMessage();
            if ($ExceptionObj->getPrevious())
            {
                $Response_message .= ' - ' . $ExceptionObj->getPrevious()->getMessage();
            }
        }
        $metadata[] = [
            'time_of_error' => date('D, d M Y H:i:s'),
        ];

        $data = [];
        if (App::environment() !== 'production' && config('waypoint.detailed_exceptions', false))
        {
            if (isset($_FILES) && $_FILES)
            {
                $data['FILES'] = array_keys($_FILES);
            }
            if (isset($_POST) && $_POST)
            {
                $data['POST'] = $_POST;
            }
            if (isset($_GET) && $_GET)
            {
                $data['GET'] = $_GET;
            }
        }

        if (is_a($ExceptionObj, HttpException::class))
        {
            $errors = $this->toArrayHttpException($ExceptionObj);
        }

        $JSONResponseObj = Response::json(
            ResponseUtil::makeError(
                $Response_message,
                $data,
                $errors ?: [$ExceptionObj->toArray()],
                $warnings,
                $metadata
            ),
            $statusCode
        );

        return $JSONResponseObj;
    }

    /**
     * @param \Exception $ExceptionObj
     * @return array
     */
    protected function formatJsonResponseForException(Exception $ExceptionObj)
    {
        $error = [
            'message'        => $ExceptionObj->getMessage() ?: 'Exception of type ' . get_class($ExceptionObj) . ' has occurred',
            'code'           => $ExceptionObj->getCode(),
            'exception_type' => get_class($ExceptionObj),
            'environment'    => App::environment(),
        ];
        if (App::environment() !== 'production' && config('waypoint.detailed_exceptions', false))
        {
            /**
             * this should only happen in a development context
             */
            $error['trace'] = $ExceptionObj->getTrace();
            $error['file']  = $ExceptionObj->getFile();
            $error['line']  = $ExceptionObj->getLine();
            if ($ExceptionObj->getPrevious())
            {
                $error['previous'] = $this->formatJsonResponseForException($ExceptionObj->getPrevious());
            }
        }
        return $error;
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Exception $e
     * @return bool
     */
    protected function shouldntReportToLaravelLogger(Exception $e)
    {
        $dontReportToLaravelLogger = array_merge($this->dontReportToLaravelLogger, [HttpResponseException::class]);

        return ! is_null(
            collect($dontReportToLaravelLogger)->first(
                function ($type) use ($e)
                {
                    return $e instanceof $type;
                }
            )
        );

    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function shouldReportToLaravelLogger(Exception $e)
    {
        return ! $this->shouldntReportToLaravelLogger($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Exception $e
     * @return bool
     */
    protected function shouldntReportToRollbar(Exception $e)
    {
        $dontReportToRollbar = array_merge(
            $this->dontReport,
            $this->dontReportToRollbar,
            [HttpResponseException::class]
        );

        return ! is_null(
            collect($dontReportToRollbar)->first(
                function ($type) use ($e)
                {
                    if (is_array($type))
                    {
                        return $e instanceof $type['exception_class'] && $e->getMessage() == $type['exception_message'];
                    }
                    return $e instanceof $type;
                }
            )
        );

    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function shouldReportToRollbar(Exception $e)
    {
        return ! $this->shouldntReportToRollbar($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Exception $e
     * @return bool
     */
    protected function shouldntReportToGraylog(Exception $e)
    {
        $dontReportToGraylog = array_merge(
            $this->dontReport,
            $this->dontReportToGraylog,
            [HttpResponseException::class]
        );

        return ! is_null(
            collect($dontReportToGraylog)->first(
                function ($type) use ($e)
                {
                    if (is_array($type))
                    {
                        return $e instanceof $type['exception_class'] && $e->getMessage() == $type['exception_message'];
                    }
                    return $e instanceof $type;
                }
            )
        );
    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function shouldReportToGraylog(Exception $e)
    {
        return ! $this->shouldntReportToGraylog($e);
    }

    /**
     * @return array
     */
    public function toArrayHttpException($HttpExceptionObj)
    {
        $error = [
            'message'        => $HttpExceptionObj->getMessage() ?: 'Exception of type ' . get_class($HttpExceptionObj) . ' has occurred',
            'code'           => 401, // per Peter B
            'exception_type' => get_class($HttpExceptionObj),
            'environment'    => App::environment(),
        ];
        if (App::environment() !== 'production' && config('waypoint.detailed_exceptions', false))
        {
            /**
             * this should only happen in a development context
             */
            $error['trace'] = $HttpExceptionObj->getTrace();
            $error['file']  = $HttpExceptionObj->getFile();
            $error['line']  = $HttpExceptionObj->getLine();
            if ($HttpExceptionObj->getPrevious())
            {
                if (method_exists($HttpExceptionObj->getPrevious(), 'toArray'))
                {
                    $error['previous'] = $HttpExceptionObj->getPrevious()->toArray();
                }
                else
                {
                    $error['previous']['message'] = $HttpExceptionObj->getPrevious()->getMessage();
                    $error['previous']['file']    = $HttpExceptionObj->getPrevious()->getFile();
                    $error['previous']['line']    = $HttpExceptionObj->getPrevious()->getLine();
                    $error['previous']['trace']   = $HttpExceptionObj->getPrevious()->getTraceAsString();
                }
            }
        }
        return $error;
    }
}
