<?php

namespace App\Waypoint\Http;

use \JsonSchema\Validator as JsonSchemaValidator;
use App;
use \App\Waypoint\CanPostJobTrait;
use App\Waypoint\Collection;
use App\Waypoint\Console\Commands\ListUsersCommand;
use App\Waypoint\Events\ControllerCallActionEvent;
use App\Waypoint\Exceptions\AuthServiceException;
use App\Waypoint\Exceptions\DaemonException;
use App\Waypoint\Exceptions\DBQueryException;
use App\Waypoint\Exceptions\DeploymentException;
use App\Waypoint\Exceptions\EntityTagException;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\InvalidJSONException;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Exceptions\PolicyException;
use App\Waypoint\Exceptions\UploadException;
use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\Http\Controller as ControllerBase;
use App\Waypoint\Http\Controllers\API\HeartbeatController;
use App\Waypoint\Model;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Models\Attachment;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Models\Comment;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Permission;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\AttachmentRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Repository;
use App\Waypoint\ResponseUtil;
use App\Waypoint\SpreadsheetCollection;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use Cache;
use Carbon\Carbon;
use Cookie;
use DB;
use Doctrine\DBAL\Driver\PDOException;
use Exception;
use function explode;
use function get_class;
use function implode;
use function is_array;
use function preg_match;
use Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Log;
use Response;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApiController
 * @package App\Waypoint\Http
 */
class ApiController extends ControllerBase
{
    use CanPostJobTrait;
    /**
     * this is a list of routes that (for resaons lost to time)
     * emit non-standard objects this cannot be checked
     * by json validation
     * @var array
     */
    protected $do_not_validate_arr = [
        'App\Waypoint\Http\Controllers\Api\AccessListPropertyPublicController@showAudits'                                    => 1,
        'App\Waypoint\Http\Controllers\Api\AccessListTrimmedSummaryController@getAccessListsPerUserForGivenClient'           => 1,
        'App\Waypoint\Http\Controllers\Api\AccessListTrimmedSummaryDeprecatedController@getAccessListsPerUserForGivenClient' => 1,
        'App\Waypoint\Http\Controllers\Api\AccessListUserPublicController@destroyByUser'                                     => 1,
        'App\Waypoint\Http\Controllers\Api\AdvancedVarianceCommentsController@storeAdvancedVarianceComments'                 => 1,
        'App\Waypoint\Http\Controllers\Api\AdvancedVarianceController@uniqueAdvancedVarianceDatesForClient'                  => 1,
        'App\Waypoint\Http\Controllers\Api\AdvancedVarianceController@uniqueAdvancedVarianceDatesForProperties'              => 1,
        'App\Waypoint\Http\Controllers\Api\AdvancedVarianceReportController@index'                                           => 1,
        'App\Waypoint\Http\Controllers\Api\AttachmentController@destroy'                                                     => 1,
        'App\Waypoint\Http\Controllers\Api\AttachmentController@downloadAttachment'                                          => 1,
        'App\Waypoint\Http\Controllers\Api\CommentDetailController@index'                                                    => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@destroy'                                               => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@getAvailableEnergyUnits'                               => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@getAvailableProjectCategories'                         => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@getAvailableProjectStatuses'                           => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@indexForClient'                                        => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@indexForProperty'                                      => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@indexForPropertyGroup'                                 => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@show'                                                  => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@store'                                                 => 1,
        'App\Waypoint\Http\Controllers\Api\EcmProjectPublicController@update'                                                => 1,
        'App\Waypoint\Http\Controllers\Api\HeartbeatController@index'                                                        => 1,
        'App\Waypoint\Http\Controllers\Api\HeartbeatDetailController@index'                                                  => 1,
        'App\Waypoint\Http\Controllers\Api\OpportunityAttachmentController@showOpportunityAttachments'                       => 1,
        'App\Waypoint\Http\Controllers\Api\OpportunityAttachmentController@storeOpportunityAttachments'                      => 1,
        'App\Waypoint\Http\Controllers\Api\OpportunityCommentController@storeOpportunityComments'                            => 1,
        'App\Waypoint\Http\Controllers\Api\OpportunityController@showAttachments'                                            => 1,
        'App\Waypoint\Http\Controllers\Api\PropertyDetailController@showCustomAttributeUniqueValues'                         => 1,
        'App\Waypoint\Http\Controllers\Api\PropertyDetailController@showStandardAttributes'                                  => 1,
        'App\Waypoint\Http\Controllers\Api\PropertyDetailController@showStandardAttributeUniqueValues'                       => 1,
        'App\Waypoint\Http\Controllers\Api\PropertyGroupPropertyController@destroyByComponents'                              => 1,
        'App\Waypoint\Http\Controllers\Api\Report\AdvancedVarianceReportController@index'                                    => 1,
        'App\Waypoint\Http\Controllers\Api\Report\UserReportController@index'                                                => 1,
        'App\Waypoint\Http\Controllers\Api\ReportTemplateFullController@show'                                                => 1,
        'App\Waypoint\Http\Controllers\Api\UserPublicController@showAccessibleGroups'                                        => 1,
        'App\Waypoint\Http\Controllers\Api\UserPublicDeprecatedController@showAccessibleGroups'                              => 1,
        'App\Waypoint\Http\Controllers\Api\UserReportController@index'                                                       => 1,
    ];

    /** @var array */
    protected $cache_tags = null;

    /** @var null */
    protected $cache_ttl = null;

    /** @var boolean */
    protected $controller_allow_caching = false;

    /** @var boolean */
    protected $skip_policies = false;

    /** @var array */
    protected $needed_configs = [];

    /** @var null|array */
    protected $spreadsheetVisibleColumns = null;

    /** @var null|array */
    protected $spreadsheetColumnsToHide = null;

    /** @var null */
    protected $spreadsheetColumnTitles = null;

    /** @var null */
    protected $spreadsheetColumnsToContainEnergyUnits = null;

    /** @var null */
    protected $spreadsheetCastColumnToType = null;

    /** @var null */
    protected $spreadsheetMetadata = null;

    /** @var array - rules must be formatted according to this: http://www.maatwebsite.nl/laravel-excel/docs/reference-guide#formatting */
    protected $spreadsheetColumnFormattingRules = [];

    /** @var User */
    protected $CurrentLoggedInUserObj;

    /** @var string */
    protected $method = null;

    const REGEX_ARRAY_OF_INTEGERS = '^(\d+(,\d+)*)?$';

    /** @var null */
    protected $json_never_strip = ['id', 'model_name'];

    /**
     * ApiController constructor.
     * @param \App\Waypoint\Repository $Repository
     */
    public function __construct(Repository $Repository)
    {
        $this->RepositoryObj = $Repository;
        parent::__construct($Repository);
    }

    /** @return array */
    public function getNeededConfigs()
    {
        return $this->needed_configs;
    }

    /**
     * @return array
     */
    public function getCacheTags()
    {
        if ( ! $this->cache_tags)
        {
            $this->setCacheTags();
        }
        return $this->cache_tags;
    }

    /**
     * @param array $cache_tags
     */
    public function setCacheTags(array $cache_tags = null)
    {
        if ( ! $cache_tags)
        {
            $this->cache_tags = ['Controller', 'Non-Session'];
            return;
        }
        $this->cache_tags = $cache_tags;
    }

    /**
     * @return int
     */
    public function getCacheTtl()
    {
        if ( ! $this->cache_ttl)
        {
            $this->setCacheTtl();
        }
        return $this->cache_ttl;
    }

    /**
     * @param int $cache_ttl
     */
    public function setCacheTtl($cache_ttl = null)
    {
        if ( ! $cache_ttl)
        {
            $this->cache_ttl = config('cache.cache_tags.Controller.ttl');
            return;
        }

        $this->cache_ttl = $cache_ttl;
    }

    /**
     * See https://laravel.com/docs/5.1/cache#configuration
     * @param Model|Collection|array|integer|string|User|Permission|Role|ApiKey|null $ObjectOrCollectionObj
     * @param $message
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @todo LoggedInUser should be a attr of Controller not cleared as needed
     *
     */
    public function sendResponse($ObjectOrCollectionObj, $message, $errors = [], $warnings = [], $metadata = [])
    {
        if (env('APP_ENV') !== 'production')
        {
            $metadata['route']['action']     = $this->getCurrentRoute()->action;
            $metadata['route']['parameters'] = $this->getCurrentRoute()->parameters();
            $metadata['route']['uri']        = $this->getCurrentRoute()->uri();
            $metadata['request']['content']  = $this->getRequestObj()->getContent() ?: null;
            $metadata['request']['json']     = $this->getRequestObj()->getContent() ?: null;
        }

        /**
         * $ObjectOrCollectionObj could just about be anything so......
         */
        if (is_object($ObjectOrCollectionObj))
        {
            if (
                /**
                 * Oddball routes that do not fit the typical patterns
                 */
                get_class($ObjectOrCollectionObj) == Attachment::class ||
                is_subclass_of($ObjectOrCollectionObj, Model::class) ||
                is_subclass_of($ObjectOrCollectionObj, User::class) || get_class($ObjectOrCollectionObj) == User::class ||
                is_subclass_of($ObjectOrCollectionObj, Permission::class) || get_class($ObjectOrCollectionObj) == Permission::class ||
                is_subclass_of($ObjectOrCollectionObj, Role::class) || get_class($ObjectOrCollectionObj) == Role::class ||
                is_subclass_of($ObjectOrCollectionObj, ApiKey::class) || get_class($ObjectOrCollectionObj) == ApiKey::class ||
                is_subclass_of($ObjectOrCollectionObj, ApiLog::class) || get_class($ObjectOrCollectionObj) == ApiLog::class ||
                is_subclass_of($ObjectOrCollectionObj, Comment::class) || get_class($ObjectOrCollectionObj) == Comment::class ||
                /**
                 * @todo fix this
                 */
                is_subclass_of($ObjectOrCollectionObj, \Actuallymab\LaravelComment\Models\Comment::class) ||
                get_class($ObjectOrCollectionObj) == \Actuallymab\LaravelComment\Models\Comment::class ||

                /**
                 * or is it a collection
                 */
                is_subclass_of($ObjectOrCollectionObj, \Illuminate\Database\Eloquent\Collection::class) ||
                get_class($ObjectOrCollectionObj) == \Illuminate\Database\Eloquent\Collection::class ||
                is_subclass_of($ObjectOrCollectionObj, \Illuminate\Support\Collection::class) ||
                get_class($ObjectOrCollectionObj) == \Illuminate\Support\Collection::class
            )
            {
                $result = $ObjectOrCollectionObj->toArray();
            }
            elseif (get_class($ObjectOrCollectionObj) == stdClass::class)
            {
                $result = stdToArray($ObjectOrCollectionObj);
            }
            else
            {
                throw new GeneralException('Error with $ObjectOrCollectionObj in sendResponse()');
            }
        }
        elseif (is_scalar($ObjectOrCollectionObj) || is_array($ObjectOrCollectionObj))
        {
            $result = $ObjectOrCollectionObj;
        }
        elseif ($ObjectOrCollectionObj === null)
        {
            /**
             * This null cast to object avoids the "{}" response
             */
            $result = (object) null;
        }
        else
        {
            throw new GeneralException('Error with $ObjectOrCollectionObj in sendResponse()');
        }

        /**
         *  We're making a projects page download spreadsheet
         */
        if (isset($_SERVER['REQUEST_URI']) && preg_match("/ecmProjects\/download/", $_SERVER['REQUEST_URI']))
        {
            (new Spreadsheet())->createECMProjectSpreadsheet($result);
            return null;
        }
        elseif ($this->routeHasDownloadPrefix())
        {
            if ($result instanceof Collection)
            {
                collect_waypoint_spreadsheet($result)->toCSVReport(
                    __CLASS__ . ' Report Generated at ' . date('Y-m-d H:i:s')
                );
                return null;
            }
            elseif ($result instanceof Model)
            {

                $result = new SpreadsheetCollection([$result]);
                $result->toCSVReport(
                    __CLASS__ . ' Report Generated at ' . date('Y-m-d H:i:s')
                );
                return null;
            }
            elseif (is_array($result))
            {
                // if Native chart of accounts spreadsheet
                if (isset($metadata['apiDisplayName']) && strpos($metadata['apiTitle'], 'native') !== false)
                {
                    (new Spreadsheet($this))->createNativeChartOfAccountSpreadsheet($result, $metadata);
                }
                else
                {
                    (new Spreadsheet($this))->createLedgerSpreadsheet($result, $metadata);
                }

                return null;
            }
            throw new GeneralException();
        }
        if ($result instanceof Collection)
        {
            $result = $result->toArray();
        }

        $payload_arr = ResponseUtil::makeResponse($message, $result, $errors, $warnings, $metadata);

        if (
            ! $errors &&
            $this->canWriteToCache() &&
            isset($payload_arr['data']) &&
            is_array($payload_arr['data']) &&
            $payload_arr['data']
        )
        {
            /**
             * note that add() only sets cache key if that key is not currently set
             * See https://laravel.com/docs/5.1/cache#configuration
             */
            try
            {
                Cache::tags($this->getCacheTags())->add($this->getControllerKey(), $payload_arr, Carbon::now()->addMinutes($this->getCacheTtl()));
            }
            catch (Exception $e)
            {
                Log::error($e->getMessage());
            }
        }

        /* ------------------------------------------------------
         |  CODE TO ONLY RUN LOCALLY
         ------------------------------------------------------*/
        if (
            env('APP_ENV') == 'local' &&
            env('JSON_VALIDATION_ENABLED', false) &&
            is_array($payload_arr['data']) &&
            count($payload_arr['data']) > 0 &&
            ! isset($this->do_not_validate_arr[$this->getCurrentRoute()->action['uses']])
        )
        {
            /**
             *
             */
            if (
                env('JSON_VALIDATION_STRIP_NON_REQUIRED', false) &&
                $this->getJSONSchema($payload_arr['data'])
            )
            {
                if ($this->object_is_list($payload_arr['data']))
                {
                    foreach ($payload_arr['data'] as $property_name => $property)
                    {
                        $payload_arr['data'][$property_name] = $this->json_validation_strip_non_required($property);
                    }
                }
                else
                {
                    $payload_arr['data'] = $this->json_validation_strip_non_required($payload_arr['data']);
                }
            }

            /**
             * first validate the $payload_arr
             */
            $JsonSchemaValidatorObj = new JsonSchemaValidator;
            if (isset($payload_arr['data']) && $this->getJSONSchema($payload_arr['data']))
            {
                /**
                 * now let's validate the data portion
                 * of response. we use $this->getJSONSchema() to calculate the
                 * schema that is germane
                 */
                $JsonSchemaValidatorObj->validate(
                    $payload_arr['data'],
                    (object) ['$ref' => 'file://' . $this->getJSONSchema($payload_arr['data'])]
                );
                if ( ! $JsonSchemaValidatorObj->isValid())
                {
                    $error_string = "JSON does not validate. Violations:" . PHP_EOL;
                    foreach ($JsonSchemaValidatorObj->getErrors() as $error)
                    {
                        $error_string .= sprintf("[%s] %s\n", $error['property'], $error['message']) . ' in schema ' . $this->getJSONSchema($payload_arr['data']) . PHP_EOL;
                    }
                    throw new GeneralException('Validation failed:' . $error_string . PHP_EOL);
                }
            }
            /**
             * let's leave this here for later testing
             */
            else
            {
                throw new GeneralException('No Json validation file found failed ' . $this->getCurrentRoute()->action['uses'] . PHP_EOL);
            }
        }
        /**
         * See https://stackoverflow.com/questions/42981409/php7-1-json-encode-float-issue/43056278
         *
         * WHy 14???? We don't expect a number greater that 15 digits - incl cents. Thus this
         * works for floats < 999.999,999,999.99
         */
        if (version_compare(phpversion(), '7.1', '>='))
        {
            ini_set('serialize_precision', 14);
        }
        $response_payload = Response::json($payload_arr);
        /* ------------------------------------------------------
         |  CODE TO ONLY RUN LOCALLY
         ------------------------------------------------------*/
        if (
            env('APP_ENV') == 'local' &&
            env('JSON_VALIDATION_ENABLED')
        )
        {
            try
            {
                $response_payload_json = $response_payload->getData(true);

                /**
                 * second, let's validate the general structure of response
                 */
                $JsonSchemaValidatorObj = new JsonSchemaValidator;
                $JsonSchemaValidatorObj->validate(
                    $response_payload_json,
                    (object) ['$ref' => 'file://' . resource_path('json_schemas/standard_response.schema.json')]
                );
                if ( ! $JsonSchemaValidatorObj->isValid())
                {
                    $error_string = "JSON does not validate. Violations:" . PHP_EOL;
                    foreach ($JsonSchemaValidatorObj->getErrors() as $error)
                    {
                        $error_string .= sprintf("[%s] %s\n", $error['property'], $error['message']) . PHP_EOL;
                    }
                    throw new GeneralException('Validation failed:' . $error_string . PHP_EOL);
                }
            }
            catch (GeneralException $e)
            {
                throw $e;
            }
            catch (Exception $e)
            {
                throw new GeneralException('validation failed' . PHP_EOL, 500, $e);
            }
        }

        return $response_payload;
    }

    /**
     * @param $payload_arr
     * @return bool|string
     * @throws GeneralException
     */
    private function getJSONSchema($payload_arr)
    {
        if ( ! isset($payload_arr['model_name']))
        {
            $object_name_saved = null;
            foreach ($payload_arr as $property_name => $property)
            {
                if (preg_match("/^([A-z]+)_\d+/", $property_name, $gleaned))
                {
                    if ($object_name_saved && ($object_name_saved !== $gleaned[1]))
                    {
                        throw new GeneralException('Invalid object encountered');
                    }
                    $object_name_saved = $gleaned[1];
                }
                else
                {
                    return false;
                }
                $object_name_plural = $this->pluralize_object_name($object_name_saved);
                $schema_file_name   = resource_path('json_schemas/' . $object_name_plural . '.schema.json');

                return file_exists($schema_file_name) ? $schema_file_name : false;
            }
            return false;
        }
        $pieces           = explode("\\", $payload_arr['model_name']);
        $schema_file_name = resource_path('json_schemas/' . array_pop($pieces) . '.schema.json');

        return file_exists($schema_file_name) ? $schema_file_name : false;
    }

    /**
     * @return boolean
     */
    public function isControllerAllowCaching()
    {
        return $this->controller_allow_caching;
    }

    /**
     * @param boolean $controller_allow_caching
     */
    public function setControllerAllowCaching($controller_allow_caching)
    {
        $this->controller_allow_caching = $controller_allow_caching;
    }

    /**
     * @return string
     * @throws \LogicException
     */
    private function getControllerKey()
    {
        $cache_key = [
            $this->getCurrentRoute()->getAction(),
            $this->getCurrentRoute()->parameters(),
            $this->getCurrentLoggedInUserObj()->email,
        ];
        return md5(
            print_r(
                $cache_key, 1
            )
        );
    }

    /**
     * @return bool
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    private function canReadCache()
    {
        return config('cache.cache_on', false) &&
               config('cache.default', false) == 'memcached' &&
               $this->isControllerAllowCaching() &&
               in_array('GET', $this->getCurrentRoute()->methods()) &&
               $this->getCurrentRoute()->getActionName() !== 'Closure' &&
               ! ($this->getRequestObj()->header('Cache-Control') == 'no-cache') &&
               ! ($this->getRequestObj()->input('no-cache'));
    }

    /**
     * @return bool
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    private function canWriteToCache()
    {
        return config('cache.cache_on', false) &&
               config('cache.default', false) == 'memcached' &&
               $this->isControllerAllowCaching() &&
               in_array('GET', $this->getCurrentRoute()->methods()) &&
               $this->getCurrentRoute()->getActionName() !== 'Closure' &&
               ! ($this->getRequestObj()->header('Cache-Control') == 'no-store') &&
               ! ($this->getRequestObj()->input('no-store'));
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AuthServiceException
     * @throws AuthorizationException
     * @throws DBQueryException
     * @throws DaemonException
     * @throws DeploymentException
     * @throws EntityTagException
     * @throws GeneralException
     * @throws HttpException
     * @throws InvalidJSONException
     * @throws ModelNotFoundException
     * @throws PolicyException
     * @throws UploadException
     * @throws ValidationException
     */
    public function callAction($method, $parameters)
    {
        if (
            env('APP_ENV', false) == 'local' &&
            config('waypoint.use_mock_objects', false)
        )
        {
            if (config('waypoint.use_auth0apimanagementusermock', false))
            {
                ListUsersCommand::setAuth0ManagementUsersObj(new Auth0ApiManagementUserMock());
                UserRepository::setAuth0ApiManagementUserObj(new Auth0ApiManagementUserMock());
            }
            if (config('waypoint.use_auth0apimanagementconnectionmock', false))
            {
                PasswordRuleRepository::setAuth0ApiManagementConnectionObj(new Auth0ApiManagementConnectionMock());
            }
            if (config('waypoint.use_rentrollmockrepository', false))
            {
                LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
            }
            if (config('waypoint.use_nativecoaledgermockrepository', false))
            {
                AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
            }
        }

        try
        {
            /**
             * NEVER EVER USE Route::resource() for non-root routes!!!!!!!!
             */

            /**
             * check the user's active_status
             */
            if (Gate::denies('user_active_status_access_policy'))
            {
                Cookie::queue(
                    Cookie::forget('CLIENT_ID_COOKIE')
                );
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            /**
             * check config to see if entries needed for this controller are present
             */
            foreach ($this->getNeededConfigs() as $needed_config)
            {
                if (Gate::denies('controller_licenses_access_policy', $needed_config))
                {
                    throw new PolicyException('Licenses policy failure ' . $needed_config . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
                }
            }

            /**
             * Remember that $parameters_arr[] can contain params like /_id$/ that can
             * in turn potentially contain a comma delimited set of ID's. Each must be checked
             * against policies
             * but remember they have to be re-parsed in controller
             */
            $parameters_arr = array_merge(Route::current()->parameters(), $parameters);
            foreach ($parameters_arr as $param_name => $param_value)
            {
                if (is_object($param_value) || $param_value instanceof ApiRequest)
                {
                    /**
                     * check both the params coming off the URI, from
                     * the $param_value->getContent() (AKA JSON content)
                     * and traditional for vars
                     *
                     * The reason you are here is that the $RequestObj is a param of the
                     * Controller::method() in question. If not, calling $this->getRequestObj() will
                     * still yield the request obj. IUAM, this is a quirk in Lavarel.
                     */
                    $this->setRequestObj($param_value);
                    $post_content_arr = array_merge(
                        (array) json_decode($param_value->getContent()),
                        $param_value->request->all()
                    );
                    foreach ($post_content_arr as $post_param_name => $post_param_value)
                    {
                        /**
                         * does $post_param_value appear in both URI and $param_value->getContent() (AKA JSON content). If yes, better match
                         */
                        if (isset($parameters_arr[$post_param_name]))
                        {
                            if (is_array($post_param_value))
                            {
                                $post_param_value = implode(',', $post_param_value);
                            }

                            if ($parameters_arr[$post_param_name] != $post_param_value)
                            {
                                throw new GeneralException('Param mismatch between route and POST', 500);
                            }
                        }

                        if (preg_match("/_id$/", $post_param_name) || preg_match("/_id_arr$/", $post_param_name))
                        {
                            if (preg_match("/^([a-z_]*_id)_arr$/", $post_param_name, $gleaned))
                            {
                                $post_param_name = $gleaned[1];
                            }
                            /**
                             * params names /_id$/ can potentially be of the for 97,98,99
                             *
                             * we split them and check_policy_against_param against each
                             * but remember they hae to be re-parsed in controller
                             */
                            if ( ! is_array($post_param_value))
                            {
                                if (preg_match('/' . ApiController::REGEX_ARRAY_OF_INTEGERS . '/', $post_param_value))
                                {
                                    $post_param_value = array_map('intval', explode(',', $post_param_value));
                                }
                                else
                                {
                                    $post_param_value = [$post_param_value];
                                }
                            }

                            foreach ($post_param_value as $inner_param_value)
                            {
                                $this->check_policy_against_param($post_param_name, $inner_param_value, true, $post_content_arr);
                            }
                        }
                        else
                        {
                            $this->check_policy_against_param($post_param_name, $post_param_value, true, $post_content_arr);
                        }

                    }
                }
                elseif (preg_match("/^(.*_id)_arr/", $param_name, $gleaned))
                {
                    foreach (explode(',', $param_value) as $inner_param_value)
                    {
                        $this->check_policy_against_param($gleaned[1], $inner_param_value, false, $parameters_arr);
                    }
                }
                elseif (preg_match("/^(.*_id)$/", $param_name, $gleaned))
                {
                    foreach (explode(',', $param_value) as $inner_param_value)
                    {
                        $this->check_policy_against_param($gleaned[1], $inner_param_value, false, $parameters_arr);
                    }
                }
                else
                {
                    $this->check_policy_against_param($param_name, $param_value, true, $parameters_arr);
                }
            }

            /**
             * NOTE NOTE NOTE
             * Since Lavarel 5.3, the logged-in-user is not available in the controller constructor
             * thus we grab it here and save it to the controller for convenience
             */
            if ( ! $this->getCurrentLoggedInUserObj())
            {
                /** @var  UserRepository $UserRepositoryObj */
                $UserRepositoryObj = App::make(UserRepository::class);
                if ($CurrentLoggedInUserObj = $UserRepositoryObj->getLoggedInUser())
                {
                    $this->setCurrentLoggedInUserObj($CurrentLoggedInUserObj);
                }
                else
                {
                    /**
                     *  if heartbeat fails with with a auth issue - wipe cookies
                     */
                    if (get_class($this) == HeartbeatController::class)
                    {
                        event(
                            new   ControllerCallActionEvent(
                                arrayToObject(
                                    [
                                        'var1' => 1,
                                        'var2' => 1,
                                    ]
                                ),
                                [

                                ]
                            )
                        );
                        return redirect('/logout/clearState');
                    }
                    throw new GeneralException('auth issue');
                }
            }

            /**
             * Provide the Models some context...
             *
             * this is needed to filter on $UserObj->is_hidden and leases, think $Property->tenants->leases. Also relatedUsers
             */
            Model::$requesting_user_role = $this->getCurrentLoggedInUserObj()->getHighestRole();

            /**
             * for each model that uses LeaseAsOfDateTrait.
             * This is needed in the phpunit context
             */
            Lease::$use_as_of_date = null;

            if ($waypoint_last_access = $this->getRequestObj()->session()->get('waypoint_last_access'))
            {
                if ($this->getRequestObj()
                         ->session()
                         ->get('waypoint_last_access') < time() - ($this->getCurrentLoggedInUserObj()->client->session_ttl ?: config('session.lifetime') / 60))
                {
                    Cookie::queue(
                        Cookie::forget('XSRF-TOKEN')
                    );
                    Cookie::queue(
                        Cookie::forget('laravel_session')
                    );
                }
            }
            $this->getRequestObj()->session()->put('waypoint_last_access', time());

            if ($this->canReadCache())
            {
                if ($payload_arr = Cache::tags($this->getCacheTags())->get($this->getControllerKey()))
                {
                    if (config('cache.show_cache_stats_as_metadata', false))
                    {
                        $payload_arr['metadata']['cache_stats'] = ResponseUtil::get_cache_stats();
                    }
                    return $payload_arr;
                }
            }
            /**
             * nothing in cache so........
             */
            try
            {
                /**
                 * this if check is to get through api unit tests
                 */
                if (isset($_SERVER['REQUEST_METHOD']))
                {
                    $this->method = $_SERVER['REQUEST_METHOD'];
                }
                if (in_array($this->method, ['POST', 'PUT', 'DELETE']))
                {
                    DB::beginTransaction();
                }

                $action_result = parent::callAction($method, $parameters);

                event(
                    new App\Waypoint\Events\ControllerCallActionMethodEvent(
                        arrayToObject(
                            [
                                'var1' => 1,
                                'var2' => 1,
                            ]
                        ),
                        [
                            'option1' => 1,
                            'option2' => 1,
                        ]
                    )
                );

                if (in_array($this->method, ['POST', 'PUT', 'DELETE']))
                {
                    DB::commit();
                }
                return $action_result;
            }
            catch (Exception $e)
            {
                if (in_array($this->method, ['POST', 'PUT', 'DELETE']))
                {
                    DB::rollBack();
                }
                throw $e;
            }
        }
            /**
             *
             *
             *  README README README README README README README README
             * This section regulates what the controller want to do with exceptions.
             * HOWEVER, if you recover from the Exception by 'return'ing something, the Exception will not get processed by
             * the ExceptionHandler and thus not reported to RollLog or GrayBar or whatever
             * In this section, for example, you can choose to:
             * - recover from Exception and return a response --- return $this->sendResponse(777, 'Lucky you');
             * - redirect the user someplace --- return \Redirect::intended($_REQUEST['state']);
             * - invalidate session and redirect the user someplace --- return \Redirect::intended($_REQUEST['state']);$this->getRequestObj()->session()->invalidate();
             *
             * If you really want to both report to RollLog or GrayBar or whatever AND you want to respond with something pretty,
             * ExceptionHandler::render
             * README README README README README README
             *
             * @todo this needs love
             */

        catch (ModelNotFoundException $e)
        {
            throw $e;
        }
        catch (AuthorizationException $AuthorizationExceptionObj)
        {
            /**
             * destroy session
             */
            if ($this->getRequestObj()->hasSession())
            {
                $this->getRequestObj()->session()->invalidate();
            }
            throw $AuthorizationExceptionObj;
        }
        catch (HttpException $e)
        {
            throw $e;
        }
        catch (ValidationException $e)
        {
            throw $e;
        }
        catch (UploadException $e)
        {
            throw $e;
        }
        catch (EntityTagException $e)
        {
            throw $e;
        }
        catch (DBQueryException $e)
        {
            throw $e;
        }
        catch (AuthServiceException $e)
        {
            throw $e;
        }
        catch (InvalidJSONException $e)
        {
            throw $e;
        }
        catch (DeploymentException $e)
        {
            throw $e;
        }
        catch (DaemonException $e)
        {
            throw $e;
        }
        catch (PolicyException $e)
        {
            throw $e;
        }
        catch (LedgerException $e)
        {
            return $this->sendLedgerErrorResponse($e);
        }
        catch (GeneralException $e)
        {
            if (strpos(get_parent_class($this), 'Ledger') !== false)
            {
                return $this->sendLedgerErrorResponse($e);
            }
            throw $e;
        }
            /**
             * below here, catch and other exceptions that DO NOT extend
             * GeneralException and convert them to GeneralException
             */
        catch (PDOException $e)
        {
            throw new GeneralException($e->getMessage(), 500, $e);
        }
        catch (QueryException $e)
        {
            throw new GeneralException($e->getMessage(), 500, $e);
        }
        catch (Exception $e)
        {
            /**
             * if we get here, it means something, somewhere is throwing an exception of
             * a type not listed below GeneralException and above here
             */
            throw new GeneralException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @param $param_name
     * @param $param_value
     * @param bool $is_post_param
     * @param array $related_params_arr
     * @return bool
     * @throws PolicyException
     */
    private function check_policy_against_param($param_name, $param_value, $is_post_param = false, $related_params_arr = [])
    {
        /**
         * in case there are are defaulted params in the controllor->method() call
         * see https://stackoverflow.com/questions/6843030/why-does-php-consider-0-to-be-equal-to-a-string
         */
        if ( ! $param_name)
        {
            return true;
        }
        if ( ! $param_value)
        {
            return true;
        }
        /**
         * @todo - this needs an overhaul when we start to use this for APIClient policies. Very frustrating
         */
        if ($this->skip_policies)
        {
            return true;
        }

        /**
         * id's passed as arrays
         */
        if (
            preg_match("/(^.)_arr$/", $param_name, $gleaned) &&
            preg_match(ApiController::REGEX_ARRAY_OF_INTEGERS, $param_value)
        )
        {
            $param_value_arr = explode(',', $param_value);
            foreach ($param_value_arr as $param_value)
            {
                $this->check_policy_against_param($gleaned[1] . '_id', $param_value, $is_post_param, $related_params_arr = []);
            }
        }
        /**
         * id's passed as integers
         */
        elseif ($param_name == 'client_id' || $param_name == 'clients')
        {
            if (Gate::denies('clients_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Client::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'user_id' || $param_name == 'users')
        {
            if (Gate::denies('users_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! User::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'calculated_field_id' || $param_name == 'calculatedFields')
        {
            if (Gate::denies('calculated_fields_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CalculatedField::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'calculated_field_equation_id' || $param_name == 'calculatedFieldEquations')
        {
            if (Gate::denies('calculated_fields_equation_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CalculatedFieldEquation::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'calculated_field_equation_property_id' || $param_name == 'calculatedFieldEquationProperties')
        {
            if (Gate::denies('calculated_fields_equation_property_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CalculatedFieldEquationProperty::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * because Laravel has 32 char limit on var param names
         */
        elseif ($param_name == 'cfep_id' || $param_name == 'calculatedFieldEquationProperties')
        {
            if (Gate::denies('calculated_fields_equation_property_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CalculatedFieldEquationProperty::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'created_by_user_id' || $param_name == 'createdByUser')
        {
            if (Gate::denies('users_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! User::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'assigned_to_user_id' || $param_name == 'assignedToUser')
        {
            if (Gate::denies('users_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! User::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'property_group_id' || $param_name == 'property_groups')
        {
            if (Gate::denies('property_groups_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ');
            }
            if ( ! $param_value === null && ! PropertyGroup::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'property_group_property_id' || $param_name == 'property_group_properties')
        {
            if (Gate::denies('property_group_properties_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! PropertyGroupProperty::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'property_id' || $param_name == 'properties')
        {
            if (Gate::denies('properties_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Property::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'favorite_id')
        {
            if (Gate::denies('favorites_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Favorite::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'entity_id')
        {
            return true;
        }
        elseif ($param_name == 'entity_tag_id')
        {
            if ( ! EntityTag::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            return true;
        }
        elseif ($param_name == 'entity_tag_entity_id')
        {
            if ( ! EntityTagEntity::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if (Gate::denies('entity_tag_entities_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'ecm_project_id' || $param_name == 'ecm_projects')
        {
            if (Gate::denies('ecm_projects_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! EcmProject::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'access_list_id' || $param_name == 'access_lists')
        {
            if (Gate::denies('access_lists_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AccessList::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'access_list_property_id' || $param_name == 'access_list_properties')
        {
            if (Gate::denies('access_list_properties_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AccessListProperty::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'access_list_user_id' || $param_name == 'access_list_users')
        {
            if (Gate::denies('access_list_users_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AccessListUser::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'opportunity_id' || $param_name == 'opportunities')
        {
            if (Gate::denies('opportunities_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Opportunity::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'client_category_id' || $param_name == 'clientCategories')
        {
            if (Gate::denies('client_category_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! ClientCategory::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_coa_id' || $param_name == 'nativeCoas')
        {
            if (Gate::denies('native_coa_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeCoa::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'custom_report_type_id' || $param_name == 'customReportType')
        {
            if (Gate::denies('custom_report_type_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CustomReportType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'custom_report_id' || $param_name == 'customReport')
        {
            if (Gate::denies('custom_reports_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! CustomReport::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * @todo See HER-2158
         */
        elseif ($param_name == 'comments')
        {
            return true;
        }
        elseif ($param_name == 'comment_id' || $param_name == 'comments')
        {
            if (Gate::denies('comments_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Comment::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'commentable_id' || $param_name == 'commentable')
        {
            if (empty($related_params_arr['commentable_type']))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            $policy_in_question = str_replace('\\', '', Str::snake(Str::plural(class_basename($related_params_arr['commentable_type'])))) . '_access_policy';

            if (Gate::denies($policy_in_question, $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            $model_in_question = $related_params_arr['commentable_type'];
            if ( ! $param_value === null && ! $model_in_question::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'attachable_id' || $param_name == 'attachable')
        {
            if (empty($related_params_arr['attachable_type']))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            $policy_in_question = str_replace('\\', '', Str::snake(Str::plural(class_basename($related_params_arr['attachable_type'])))) . '_access_policy';

            if (Gate::denies($policy_in_question, $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * @todo make sure that these access policies should be named like 'comments_access_policy', note that the object in question should be plurl,
         *       for example 'related_user_type_access_policy' should be 'related_user_types_access_policy'. This is an issue if we ever allow
         *       attachments to related_user_types
         */
        elseif ($param_name == 'attachment_id' || $param_name == 'attachment')
        {
            $AttachmentRepositoryObj = App::make(AttachmentRepository::class);
            /** @var Attachment $AttachmentObj */
            if ( ! $AttachmentObj = $AttachmentRepositoryObj->find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            $policy_in_question = str_replace('\\', '', Str::snake(Str::plural(class_basename($AttachmentObj->model_type)))) . '_access_policy';

            if (Gate::denies($policy_in_question, $AttachmentObj->model_id))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'related_user_type_id' || $param_name == 'relatedUserType')
        {
            if (Gate::denies('related_user_type_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! RelatedUserType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'advanced_variance_id' || $param_name == 'advancedVariance')
        {
            if (Gate::denies('advanced_variances_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVariance::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'advanced_variance_line_item_id' || $param_name == 'advancedVarianceLineItem')
        {
            if (Gate::denies('advanced_variance_line_items_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVarianceLineItem::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'advanced_variance_approval_id' || $param_name == 'advancedVarianceApproval')
        {
            if (Gate::denies('advanced_variance_approval_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVarianceApproval::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * NOTE NOTE NOTE that Laravel has issues with long param names so we shorten things here, explanation_type_id vs. advanced_variance_explanation_type_id
         * This is here if explanation_type_id (aka advanced_variance_explanation_type_id) is passed in via POST
         */
        elseif ($param_name == 'advanced_variance_explanation_type_id' || $param_name == 'advancedVarianceExplanationType')
        {
            if (Gate::denies('advanced_variance_explanation_type_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVarianceExplanationType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * NOTE NOTE NOTE that Laravel has issues with long param names so we shorten things here, explanation_type_id vs. advanced_variance_explanation_type_id
         * This is here if explanation_type_id (aka advanced_variance_explanation_type_id) is passed in on URL
         */
        elseif ($param_name == 'explanation_type_id' || $param_name == 'advancedVarianceExplanationType')
        {
            if (Gate::denies('advanced_variance_explanation_type_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVarianceExplanationType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'advanced_variance_threshold_id' || $param_name == 'advancedVarianceExplanationType')
        {
            if (Gate::denies('advanced_variance_threshold_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! AdvancedVarianceExplanationType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'related_user_id' || $param_name == 'relatedUser')
        {
            if (Gate::denies('related_user_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! RelatedUser::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'assigned_to_user_id' || $param_name == 'assignedToUser')
        {
            if (Gate::denies('assigned_user_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! User::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'created_by_user_id' || $related_params_arr == 'createdByUser')
        {
            if (Gate::denies('created_by_user_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! User::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        /**
         * fix this to match technique used in assigned_user_access_policy
         */
        elseif ($param_name == 'related_object_id' || $param_name == 'relatedObject')
        {
            if (empty($related_params_arr['related_user_type_id']))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            $related_user_type_arr = DB::select(
                DB::raw(
                    'SELECT *
                        FROM related_user_types
                        WHERE id=' . $related_params_arr['related_user_type_id']
                )
            );

            $policy_in_question = str_replace('\\', '', Str::snake(Str::plural(class_basename($related_user_type_arr[0]->related_object_type)))) . '_access_policy';

            if (Gate::denies($policy_in_question, $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }

            if ( ! $param_value === null && ! Comment::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_account_id' || $param_name == 'nativeAccount')
        {
            if (Gate::denies('native_account_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeAccount::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_account_type_id' || $param_name == 'nativeAccountType')
        {
            if (Gate::denies('native_account_type_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeAccountType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_account_type_trailer_id' || $param_name == 'nativeAccountTypeTrailer')
        {
            if (Gate::denies('native_account_type_trailer_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeAccountTypeTrailer::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_account_type_trailer_id' || $param_name == 'nativeAccountTypeTrailer')
        {
            if (Gate::denies('native_account_type_trailer_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeAccountType::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'native_account_type_trailer_id' || $param_name == 'nativeAccountTypeTrailer')
        {
            if (Gate::denies('native_account_type_trailer_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! NativeAccountTypeTrailer::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif (
            $param_name == 'report_template_id' ||
            $param_name == 'reportTemplate' ||
            $param_name == 'report_template'
        )
        {
            if (Gate::denies('report_template_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! ReportTemplate::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif (
            $param_name == 'rtag_id' ||
            $param_name == 'report_template_account_group_id' ||
            $param_name == 'reportTemplateAccountGroup'
        )
        {
            if (Gate::denies('report_template_account_group_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! ReportTemplateAccountGroup::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'report_template_mapping_id' || $param_name == 'report_template_mapping')
        {
            if (Gate::denies('report_template_mapping_access_policy', $param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! ReportTemplateAccountGroup::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'property_native_coa_id' || $param_name == 'propertyNativeCoa')
        {
            if (Gate::denies('property_native_coa_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! PropertyNativeCoa::find($param_value))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'lease_id' || $param_name == 'lease')
        {
            if (Gate::denies('leases_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure ' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
            if ( ! $param_value === null && ! Lease::find($param_value))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'tenant_id' || $param_name == 'tenant')
        {
            if (Gate::denies('tenants_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'suite_id' || $param_name == 'suite')
        {
            if (Gate::denies('suites_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'asset_type_id' || $param_name == 'asset_type')
        {
            if (Gate::denies('asset_types_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'authenticating_entity_id' || $param_name == 'authenticating_entity')
        {
            if (Gate::denies('authenticating_entities_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'tenant_industry_id' || $param_name == 'tenant_industry')
        {
            if (Gate::denies('tenant_industry_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'tenant_attribute_id' || $param_name == 'tenant_attribute')
        {
            if (Gate::denies('tenant_attributes_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif ($param_name == 'tenant_tenant_attribute_id' || $param_name == 'tenant_tenant_attribute')
        {
            if (Gate::denies('tenant_tenant_attributes_access_policy', [$param_value, $related_params_arr]))
            {
                throw new PolicyException('Access policy failure' . ( ! App::environment() === 'production') ? __FILE__ . ':' . __LINE__ : '', 403);
            }
        }
        elseif (preg_match("/_id$/", $param_name) && $is_post_param)
        {
            /**
             * in the case of post params, we only check against current policies.
             * in otherwords, if the id in question is not dealt with above and
             * is in the post, it passes
             */
            return true;
        }
        elseif (preg_match("/_id$/", $param_name))
        {
            /**
             * in the case of get id params, we must check check a policy
             */
            throw new PolicyException('Failed to determine policy for parameter ' . $param_name);
        }
        return true;
    }

    /**
     * @return int
     */
    protected function routeHasDownloadPrefix()
    {
        /**
         * @todo deal w/ this in the unittest context
         */
        if (isset($_SERVER['REQUEST_URI']))
        {
            return preg_match("/ledger\/download/", $_SERVER['REQUEST_URI']);
        }
        return false;
    }

    /**
     * @return int
     */
    protected function getLedgerDownloadLink()
    {
        /**
         * @todo deal w/ this in the unittest context
         */
        if (isset($_SERVER['REQUEST_URI']))
        {
            return preg_replace("/\/ledger\//", "/ledger/download/", $_SERVER['REQUEST_URI']);
        }
        return false;
    }

    /**
     * @return User
     */
    public function getCurrentLoggedInUserObj()
    {
        return $this->CurrentLoggedInUserObj;
    }

    /**
     * @param User $CurrentLoggedInUserObj
     */
    public function setCurrentLoggedInUserObj($CurrentLoggedInUserObj)
    {
        $this->CurrentLoggedInUserObj = $CurrentLoggedInUserObj;
    }

    /**
     * @param GeneralException $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLedgerErrorResponse(GeneralException $e)
    {
        $metadata         = [
            'class'       => $this->getUnqualifiedClassName(current($e->getTrace())['class']),
            'apiTitle'    => $this->apiTitle,
            'displayName' => $this->apiDisplayName,
            'count'       => 0,
        ];
        $response_package = ResponseUtil::makeError(
            'a problem occurred while generating your data, please contact ' . App\Waypoint\Notifications\Notification::SUPPORT_EMAIL_ADDRESS,
            [],
            [$e->getMessage()],
            [],
            $metadata
        );
        return Response::json($response_package, $e->getCode());
    }

    /**
     * @param $fully_qualified_class_name string
     * @return mixed
     */
    private function getUnqualifiedClassName($fully_qualified_class_name)
    {
        $array = explode('\\', $fully_qualified_class_name);
        return array_pop($array);
    }

    /**
     * @param array $object_in_question string
     * @return mixed
     */
    private function json_validation_strip_non_required($object_in_question): array
    {
        if ( ! is_array($object_in_question))
        {
            return $object_in_question;
        }
        if ($this->getJSONSchema($object_in_question))
        {
            if ($this->object_is_list($object_in_question))
            {
                foreach ($object_in_question as $property_name => $property)
                {
                    $this->json_validation_strip_non_required($object_in_question);
                }
            }
            else
            {
                $json_schema = json_decode(file_get_contents($this->getJSONSchema($object_in_question)));
                if (isset($json_schema->required))
                {
                    foreach ($object_in_question as $property_name => $property)
                    {
                        if (in_array($property_name, $this->json_never_strip))
                        {
                            continue;
                        }
                        if ( ! in_array($property_name, $json_schema->required))
                        {
                            unset($object_in_question[$property_name]);
                        }
                    }
                }
            }
        }
        return $object_in_question;
    }

    /**
     * @param $object_name_in_question
     * @return string
     * @throws GeneralException
     */
    private function pluralize_object_name($object_name_in_question)
    {
        switch ($object_name_in_question)
        {
            case 'AccessListDetail':
                return "AccessListsDetail";
            case 'AccessListFull':
                return "AccessListsFull";
            case 'AccessListProperty':
                return "AccessListProperties";
            case 'AccessListPropertyFull':
                return "AccessListPropertiesFull";
            case 'AccessListPropertySummary':
                return "AccessListPropertiesSummary";
            case 'AccessListSummary':
                return "AccessListsSummary";
            case 'AdvancedVarianceLineItemDetail':
                return "AdvancedVarianceLineItemsDetail";
            case 'AdvancedVarianceSummary':
                return "AdvancedVarianceSummaries";
            case 'AuthenticatingEntity':
                return "AuthenticatingEntities";
            case 'CalculatedFieldEquationProperty':
                return "CalculatedFieldEquationProperties";
            case 'ClientCategory':
                return "ClientCategories";
            case 'CustomReportDetail':
                return "CustomReportsDetail";
            case 'DownloadHistory':
                return "DownloadHistories";
            case 'NativeAccountTypeDetail':
                return "NativeAccountTypesDetail";
            case 'NativeCoaFull':
                return "NativeCoasFull";
            case 'Opportunity':
                return "Opportunities";
            case 'Property':
                return "Properties";
            case 'PropertyDetail':
                return "PropertiesDetail";
            case 'PropertyFull':
                return "PropertiesFull";
            case 'PropertyGroupFull':
                return "PropertyGroupsFull";
            case 'PropertyGroupProperty':
                return "PropertyGroupProperties";
            case 'PropertySummary':
                return "PropertiesSummary";
            case 'ReportTemplateDetail':
                return "ReportTemplatesDetail";
            case 'ReportTemplateMappingFull':
                return "ReportTemplateMappingsFull";
            case 'TenantIndustry':
                return "TenantIndustries";
            case 'UserDetail':
                return "UsersDetail";
            case 'UserSummary':
                return "UsersSummary";

            default:
                return $object_name_in_question . 's';
        }
    }

    /**
     * @param $object_name_in_question
     * @return string
     * @throws GeneralException
     */
    private function object_is_list($object_in_question)
    {
        if ( ! is_array($object_in_question))
        {
            return $object_in_question;
        }
        $object_name_saved = null;
        foreach ($object_in_question as $property_name => $property)
        {
            if (in_array($property_name, $this->json_never_strip))
            {
                continue;
            }
            if (preg_match("/^([A-z]+)_\d+/", $property_name, $gleaned))
            {
                if ($object_name_saved && ($object_name_saved !== $gleaned[1]))
                {
                    throw new GeneralException('Invalid object encountered' . __FILE__ . ':' . __LINE__);
                }
                /** @noinspection PhpUnusedLocalVariableInspection */
                $object_name_saved = $gleaned[1];
            }
            else
            {
                return false;
            }
            return true;
        }
        return false;
    }
}
