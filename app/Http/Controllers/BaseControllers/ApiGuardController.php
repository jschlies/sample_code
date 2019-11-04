<?php

namespace App\Waypoint\Http;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Models\Permission;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repository;
use App\Waypoint\ResponseUtil;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController as ApiGuardControllerBase;
use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\QueryException;
use Response;

/**
 * Class ApiController
 * @package App\Waypoint\Http
 */
class ApiGuardController extends ApiGuardControllerBase
{

    /**
     * ApiController constructor.
     * @param Repository $Repository
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * See https://laravel.com/docs/5.1/cache#configuration
     * @param Model|Collection|array|integer|string|User|Permission|Role|ApiKey $ObjectOrCollectionObj
     * @param $message
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @todo LoggedInUser should be a attr of Controller not cleared as needed
     *
     */
    public function sendResponse($ObjectOrCollectionObj, $message, $errors = [], $warnings = [], $metadata = [])
    {
        /**
         * $ObjectOrCollectionObj could just about be anything so......
         */
        if (is_object($ObjectOrCollectionObj))
        {
            if (
                /**
                 * Oddball routes that do not fit the typical patters
                 */
                is_subclass_of($ObjectOrCollectionObj, Model::class) ||
                is_subclass_of($ObjectOrCollectionObj, User::class) || get_class($ObjectOrCollectionObj) == User::class ||
                is_subclass_of($ObjectOrCollectionObj, Permission::class) || get_class($ObjectOrCollectionObj) == Permission::class ||
                is_subclass_of($ObjectOrCollectionObj, Role::class) || get_class($ObjectOrCollectionObj) == Role::class ||
                is_subclass_of($ObjectOrCollectionObj, ApiKey::class) || get_class($ObjectOrCollectionObj) == ApiKey::class ||
                is_subclass_of($ObjectOrCollectionObj, ApiLog::class) || get_class($ObjectOrCollectionObj) == ApiLog::class
            )
            {
                try
                {
                    $result = $ObjectOrCollectionObj->toArray();
                }
                catch (GeneralException $e)
                {
                    throw $e;
                }
            }

            /**
             * We do not add audits to the metadata of collections
             */
            elseif (
                is_subclass_of($ObjectOrCollectionObj, \Illuminate\Database\Eloquent\Collection::class) ||
                get_class($ObjectOrCollectionObj) == \Illuminate\Database\Eloquent\Collection::class
            )
            {
                $result = $ObjectOrCollectionObj->toArray();
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
        else
        {
            throw new GeneralException('Error with $ObjectOrCollectionObj in sendResponse()');
        }

        $payload_arr = ResponseUtil::makeResponse($message, $result, $errors, $warnings, $metadata);

        return Response::json($payload_arr);
    }

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function callAction($method, $parameters)
    {
        try
        {
            /**
             * NEVER EVER USE Route::resource() for non-root routes!!!!!!!!
             */
            return parent::callAction($method, $parameters);
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
            /**
             * below here, catch and other exceptions that DO NOT extend
             * GeneralException and convert them to the most
             * appropriate exception that does
             * extend GeneralException
             */
        catch (PDOException $e)
        {
            throw new GeneralException('PDOException Exception', 404, $e);
        }
        catch (QueryException $e)
        {
            throw new GeneralException('DBQueryException Exception', 404, $e);
        }
        catch (\Exception $e)
        {
            /**
             * if we get here, it means something, somewhere is throwing an exception of
             * a type not listed below GeneralException and above here
             */
            throw new GeneralException('General Exception', 404, $e);
        }
    }
}
