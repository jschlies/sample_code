<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Model;
use App\Waypoint\Models\Property;
use ArrayObject;
use Illuminate\Support\Str;
use App\Waypoint\Command;
use Route;

/**
 * Class PostmanCollectionCommand
 * @package App\Waypoint\Console\Commands
 */
class PostmanCollectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:postman_collection 
                        {--client_id=} 
                        {--property_id=} 
                        {--property_group_id=}
                        {--report_template_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate generated postman collections';

    /**
     * PostmanCollectionCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        /*
         * since this can crash, particularly in a unstable DEV environment, all Exceptions are recovered with
         * a msg to console
         */
        $routeCollection = Route::getRoutes();

        $json_str = file_get_contents(
            config('waypoint.postman_template_path')
        );

        // create directory to hold generated files
        if ( ! file_exists(resource_path('postman/generated')))
        {
            mkdir(resource_path('postman/generated'), 0755, true);
        }

        /**
         * clear out any old collections
         */
        foreach (glob(config('waypoint.postman_target_path') . "/*.*") as $filename)
        {
            if (is_file($filename))
            {
                unlink($filename);
            }
        }

        $collection_template_arr = json_decode($json_str, true);
        $new_collection_arr      = [];

        /**
         * clear out any old collections
         */
        foreach (glob(config('waypoint.postman_target_path') . "/*.*") as $filename)
        {
            if (is_file($filename))
            {
                unlink($filename);
            }
        }

        $object_hash = [];
        /** @var \Illuminate\Routing\Route $routeObj */
        foreach ($routeCollection as $routeObj)
        {
            $request_item_name = $routeObj->uri();
            if (preg_match("/[api|report]\/v1(\/.*)$/", $routeObj->uri(), $gleaned))
            {
                $request_item_name = $gleaned[1];
            }

            foreach ($routeObj->methods() as $method)
            {
                if ($method == 'HEAD')
                {
                    continue;
                }
                if ($method == 'PATCH')
                {
                    continue;
                }
                $prefix_to_use = $routeObj->getPrefix() ?: 'no_prefix';
                foreach ($collection_template_arr['requests'] as $request_item)
                {
                    $new_request = [];
                    foreach ($request_item as $item_name => $request_line_item)
                    {
                        $new_request[$item_name] = $request_line_item;
                        $new_request[$item_name] = preg_replace('/__METHOD__/', $method, $new_request[$item_name]);
                        $new_request[$item_name] = preg_replace('/__PATH__/', $routeObj->uri(), $new_request[$item_name]);
                        $new_request[$item_name] = preg_replace('/__NAME__/', $request_item_name, $new_request[$item_name]);
                        $new_request[$item_name] = preg_replace('/__ACTIONNAME__/', $routeObj->getActionName(), $new_request[$item_name]);
                    }
                    $new_request['id'] = $this->guidv4();
                    if ( ! isset($new_collection_arr[$prefix_to_use]))
                    {
                        $new_collection_arr[$prefix_to_use]                = [];
                        $new_collection_arr[$prefix_to_use]['id']          = $this->guidv4();
                        $new_collection_arr[$prefix_to_use]['name']        = 'Generated ' . $collection_template_arr['name'] . $prefix_to_use;
                        $new_collection_arr[$prefix_to_use]['description'] = $collection_template_arr['description'];
                        $new_collection_arr[$prefix_to_use]['folders']     = $collection_template_arr['folders'];
                        $new_collection_arr[$prefix_to_use]['timestamp']   = $collection_template_arr['timestamp'];
                        $new_collection_arr[$prefix_to_use]['owner']       = $collection_template_arr['owner'];
                        $new_collection_arr[$prefix_to_use]['public']      = $collection_template_arr['public'];
                        $new_collection_arr[$prefix_to_use]['requests']    = [];
                    }
                    $new_request['collectionId'] = $new_collection_arr[$prefix_to_use]['id'];
                    /**
                     * cause I'm lazy - if the param ends with 'id', pre-set it to 2. Why 2, don't know
                     */

                    $new_request['url'] = preg_replace(
                        '/\/\{client_id\}/', '/' . ($this->option('client_id') ? $this->option('client_id') : 119), $new_request['url']
                    );
                    $new_request['url'] = preg_replace(
                        '/\/\{property_id\}/', '/' . ($this->option('property_id') ? $this->option('property_id') : 5823), $new_request['url']
                    );
                    $new_request['url'] = preg_replace(
                        '/\/\{property_group_id\}/', '/' . ($this->option('property_group_id') ? $this->option('property_group_id') : 6688),
                        $new_request['url']
                    );
                    $new_request['url'] = preg_replace(
                        '/\/\{report_template_id\}/', '/' . ($this->option('report_template_id') ? $this->option('report_template_id') : 36),
                        $new_request['url']
                    );
                    $new_request['url'] = preg_replace('/\{[A-z|0-9_]*_id\}/', '2', $new_request['url']);
                    if (preg_match("/\/ledger\//", $prefix_to_use))
                    {
                        $new_request['url'] = preg_replace('/\/\{report\}/', '/ACTUAL', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{year\}/', '/2015', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{period\}/', '/CY', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{area\}/', '/RENTABLE', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{account_code\}/', '/41 000', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{start_year\}/', '/2015', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{start_month\}/', '/01', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{end_year\}/', '/2015', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{end_month\}/', '/11', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{account_header_code\}/', '/41 000', $new_request['url']);
                        $new_request['url'] = preg_replace('/\/\{account_code\}/', '/41 000', $new_request['url']);
                    }
                    else
                    {
                        $new_request['url']  = preg_replace('/\/\{.*?\}/', '/2', $new_request['url']);
                        $new_request['name'] = preg_replace('/\/\{.*?_id\}/', '/{id}', $new_request['name']);
                        $new_request['name'] = preg_replace('/ClientUser/', 'CU', $new_request['name']);
                        $new_request['name'] = preg_replace('/ClientAdmin/', 'CA', $new_request['name']);
                        $new_request['name'] = preg_replace('/Root/', 'R', $new_request['name']);
                        $new_request['name'] = preg_replace('/\/clients\//', '/c/', $new_request['name']);
                        $new_request['name'] = preg_replace('/\/properties\//', '/p/', $new_request['name']);
                        $new_request['name'] = preg_replace('/\/advancedVariances\//', '/av/', $new_request['name']);
                        $new_request['name'] = preg_replace('/\/reportTemplateAccountGroup\//', '/rtag/', $new_request['name']);
                        $new_request['name'] = preg_replace('/\/advancedVarianceLineItems\//', '/avli/', $new_request['name']);
                    }
                    if ($prefix_to_use == 'api/v1/waypointMasterBridge/Root' || 'api/v1/aritsan/Root')
                    {
                        $new_request['headers'] .= 'X-Authorization: {{apiKey}}' . PHP_EOL;
                    }
                    if (in_array($method, ['PUT', 'POST']))
                    {
                        $action = $routeObj->getAction();

                        if (
                            isset($action['controller']) &&
                            ! preg_match("/Favorite/", $action['controller']) &&
                            ! preg_match("/License/", $action['controller']) &&
                            ! preg_match("/Config/", $action['controller']) &&
                            ! preg_match("/Style/", $action['controller']) &&
                            ! preg_match("/Image/", $action['controller']) &&
                            ! preg_match("/ReportTemplate/", $action['controller'])
                        )
                        {
                            $controller_arr              = explode("\\", $action['controller']);
                            $unpathed_name_of_controller = array_pop($controller_arr);

                            if (preg_match("/^(.*?)((Public|Detail|Summary)?Controller){1}\@/", $unpathed_name_of_controller, $gleaned))
                            {
                                $model_name = "App\\Waypoint\\Models\\" . $gleaned[1];
                                if (class_exists($model_name))
                                {
                                    /** @var Model $anObject */
                                    if (isset($object_hash[$model_name]))
                                    {
                                        $anObject = $object_hash[$model_name];
                                    }
                                    else
                                    {
                                        /** @noinspection PhpUndefinedMethodInspection */
                                        $anObject                 = $model_name::first();
                                        $object_hash[$model_name] = $anObject;
                                    }
                                    if ($anObject)
                                    {
                                        $model_json                 = $anObject->toArray();
                                        $model_json                 = self::unset_if_set(
                                            $model_json,
                                            ['id', 'model_name', 'created_at', 'updated_at']
                                        );
                                        $model_json                 = self::strip_arrays($model_json);
                                        $new_request['rawModeData'] = json_encode($model_json, JSON_PRETTY_PRINT);
                                    }
                                }
                            }
                        }

                        if (
                            isset($action['controller']) &&
                            preg_match("/Image/", $action['controller'])
                        )
                        {
                            $new_request['rawModeData'] = [];
                        }

                        if (
                            isset($action['controller']) &&
                            (
                                /**
                                 * note that Image is not here on purpose. Remember that
                                 * sincce it req's a file upload, it's a form req, not a 'GET'
                                 */
                                preg_match("/Favorite/", $action['controller']) ||
                                preg_match("/License/", $action['controller']) ||
                                preg_match("/Config/", $action['controller']) ||
                                preg_match("/Style/", $action['controller'])
                            )
                        )
                        {
                            $new_request['rawModeData'] = '
                            {
                                "entity_model": "' . Property::class . '",
                                "user_id": 259,
                                "entity_tag_id" : 1,
                                "entity_id" : 7,
                                "data" : {}
                            }';
                        }
                    }
                    else
                    {
                        $new_request['rawModeData'] = json_encode(new ArrayObject());
                    }

                    $new_collection_arr[$prefix_to_use]['requests'][] = $new_request;
                }
            }
        }

        foreach ($new_collection_arr as $collection_prefix => $new_collection)
        {
            $myFile = config('waypoint.postman_target_path') . preg_replace("/[ |\/]/", '.', $collection_prefix) . '.json';
            $fh = fopen($myFile, 'w') or die("can't open file");
            fwrite($fh, json_encode($new_collection, JSON_PRETTY_PRINT));
            fclose($fh);
        }

        return true;
    }

    /**
     * @return string
     */
    protected function guidv4()
    {
        if (function_exists('com_create_guid') === true)
        {
            /** @noinspection PhpUndefinedFunctionInspection */
            return trim(com_create_guid(), json_encode(new ArrayObject()));
        }

        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @param $modelName
     * @return string
     */
    protected function getPluralFunctionName($modelName)
    {
        $modelName = lcfirst($modelName);
        return $this->str_plural($modelName);
    }

    /**
     * Get the plural form of an English word.
     *
     * @param string $value
     * @param int $count
     * @return string
     */
    protected function str_plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }

    /**
     * @param array $array_to_clean_up
     * @param array $element_to_delete_arr
     * @return array
     */
    public static function unset_if_set(array $array_to_clean_up, array $element_to_delete_arr)
    {
        foreach ($element_to_delete_arr as $element_to_delete)
        {
            $array_to_clean_up[$element_to_delete] = null;
            unset($array_to_clean_up[$element_to_delete]);
        }
        return $array_to_clean_up;
    }

    /**
     * @param array $array_to_clean_up
     * @return array
     */
    public static function strip_arrays(array $array_to_clean_up)
    {
        foreach ($array_to_clean_up as $i => $element_to_clean_up)
        {
            if (is_array($element_to_clean_up) || is_object($element_to_clean_up))
            {
                unset($array_to_clean_up[$i]);
            }
        }
        return $array_to_clean_up;
    }
}
