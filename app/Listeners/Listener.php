<?php

namespace App\Waypoint\Listeners;

use App\Waypoint\Events\EventBase;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ListenerException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AccessListUserFull;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Graylog;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserAdmin;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Models\UserSummary;
use App\Waypoint\Notifications\AdvancedVarianceLineItemResolvedNotification;
use App\Waypoint\Rollbar;
use App\Waypoint\SQSUtil;
use DB;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;
use Rollbar\Payload\Level;
use \Gelf\Message as Gelf_Message;
use \Psr\Log\LogLevel as Psr_Log_LogLevel;

/**
 * Class Listener
 * @package App\Waypoint\Listeners
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
abstract class Listener
{
    use DispatchesJobs;

    /** @var array */
    protected $model_arr = [];

    /** @var string */
    protected $job_class = null;

    /** @var string */
    public $model_class = null;

    /** @var [] */
    protected $queue = null;

    /**
     * Listener constructor.
     * @throws ListenerException
     */
    public function __construct()
    {
        /**
         * this is OK when $this hhandles the logic itself rather than
         * utilizing a job class & queue
         */
        if ( ! $this->job_class || ! $this->queue)
        {
            return;
        }
        if ( ! ($this->job_class && $this->queue))
        {
            throw new GeneralException(__CLASS__ . ' at ' . __FILE__ . ':' . __LINE__);
        }
    }

    /**
     * Handle the event.
     *
     * Note that since this Listener 'listens' on several events, we cannot specifically typecast the incoming $event
     *
     * @param EventBase $EventObj
     * @throws GeneralException
     * @throws ListenerException
     */
    public function handle($EventObj = null)
    {
        try
        {
            $this->model_arr = $EventObj->getModelArr();
            if ( ! isset($EventObj->options['event_trigger_message']) || ! $EventObj->options['event_trigger_message'])
            {
                $EventObj->options['event_trigger_message'] = 'No message provided';
            }
            $this->send_message($this->build_log_message($EventObj));

            $this->post_job_to_queue();

            return;
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage() . __CLASS__ . ' Event ' . ($EventObj ? get_class($EventObj) : '') . ' at ' . __FILE__ . ':' . __LINE__, 404, $e);
        }
    }

    /**
     * @throws ListenerException
     */
    protected function post_job_to_queue()
    {
        if ( ! $this->queue['QueueName'])
        {
            throw new GeneralException('queue.queue_lane not defined');
        }

        try
        {
            $job_class = $this->job_class;
            /**
             * REMEMBER that there is no real Job associated with PostMigrationListener. 'The work' of CalculateVariousPropertyListsJob* is done in
             * PostMigrationListener->handle().
             */
            /** @noinspection PhpUndefinedMethodInspection */
            $JobObj = (new $job_class($this->model_arr))->onConnection(config('queue.driver'))->onQueue($this->queue[SQSUtil::QUEUE_ATTR_QUEUENAME]);
            /**
             * README README README README
             * README README README README
             * README README README README
             * README README README README
             * if you're looking at this and wondering "If my .env for QUEUE_DRIVER = 'sqs', why is this config('queue.driver') returning a value of 'sync
             * If running a unit test, check your phpunit.xml!!!
             *
             * README README README README
             * README README README README
             *
             * REMEMBER PHPUNIT.XML
             */
            if (config('queue.driver', 'sync') == 'sqs')
            {
                $SQSUtil = new SQSUtil($JobObj, $this->queue);
                $SQSUtil->post_to_sqs();
            }
            elseif (config('queue.driver', 'sync') == 'sync')
            {
                /**
                 * README README README README
                 * README README README README
                 * README README README README
                 * README README README README
                 * if you're looking at this and wondering "If my .env for QUEUE_DRIVER = 'sqs', why is this config('queue.driver') returning a value of 'sync
                 * If running a unit test, check your phpunit.xml!!!
                 *
                 * README README README README
                 * README README README README
                 */
                $this->dispatch($JobObj);
            }
            else
            {
                throw new GeneralException(__CLASS__ . ' at ' . __FILE__ . ':' . __LINE__);
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage() . __CLASS__ . ' at ' . __FILE__ . ':' . __LINE__, 404, $e);
        }
    }

    /**
     * @param $ClientObj
     * @param $wipe_out_list
     * @param EventBase $EventObj
     * @throws Exception
     */
    public function wipe_out_pre_calcs($ClientObj, $wipe_out_list, EventBase $EventObj)
    {
        /**
         *
         * Please take a minute to research, think and understand the environment
         * switches SUPPRESS_PRE_CALC_USAGE, SUPPRESS_PRE_CALC_EVENTS and QUEUE_DRIVER
         * and the client config values SUPPRESS_PRE_CALC_USAGE, SUPPRESS_PRE_CALC_EVENTS.
         * Depending on the which of our environs you'er using, Homestead, Hydra,
         * Staging or prod. Getting these wrong can result in loooooong
         * migration times and/or poor performance and bad moral character
         */

        /**
         * no list????? Burn it down!!!! Burn it all down
         * Everything goes. This should almost never happen.
         * If it does happen, check the Indiv listener class
         */
        if ( ! $wipe_out_list)
        {
            $wipe_out_list = ['clients' => [], 'properties' => [], 'property_groups' => [], 'users' => []];
        }

        foreach ($wipe_out_list as $table => $key_arr)
        {
            /**
             * no list for this table????? Everything from $table goes
             */
            if (isset($key_arr[0]) && $key_arr[0] == 'skip-soiling')
            {
                return;
            }
            if ( ! $key_arr)
            {
                $key_arr   = [];
                $key_arr[] = '.*';
            }

            /** @var Property $PropertyObj */
            /** @var PropertyGroup $PropertyGroupObj */
            /** @var User $UserObj */
            $where_clause = ' AND ( pre_calc_name REGEXP \'' .implode('\' or pre_calc_name REGEXP \'', $key_arr).'\')';
            switch ($table)
            {
                case 'clients':
                    $sql = '
                                UPDATE pre_calc_status 
                                    SET is_soiled = true,
                                         soiled_at = NOW()
                                    where
                                          pre_calc_status.client_id = ' . $ClientObj->id . '
                                           ' . $where_clause . '
                            ';
                    DB::update(
                        DB::raw($sql)
                    );
                    continue;
                case 'properties':
                    if (isset($EventObj->model_arr['launch_job_property_id_arr']) && $EventObj->model_arr['launch_job_property_id_arr'])
                    {
                        $launch_job_property_id_arr = $EventObj->model_arr['launch_job_property_id_arr'];
                    }
                    else
                    {
                        $launch_job_property_id_arr = $ClientObj->properties->pluck('id')->toArray();
                    }
                    $sql = '
                                UPDATE pre_calc_status 
                                    SET is_soiled = true,
                                         soiled_at = NOW()
                                    where
                                          pre_calc_status.property_id in (' . implode(',', $launch_job_property_id_arr) . ') 
                                           ' . $where_clause . '
                            ';
                    DB::update($sql);
                    continue;
                case 'property_groups':
                    if (isset($EventObj->model_arr['launch_job_property_group_id_arr']) && $EventObj->model_arr['launch_job_property_group_id_arr'])
                    {
                        $launch_job_property_group_id_arr = $EventObj->model_arr['launch_job_property_group_id_arr'];
                    }
                    else
                    {
                        $launch_job_property_group_id_arr = $ClientObj->propertyGroups->pluck('id')->toArray();
                    }
                    $sql          = '
                                UPDATE pre_calc_status 
                                    SET is_soiled = true,
                                         soiled_at = NOW()
                                    where
                                          pre_calc_status.property_group_id in (' . implode(',', $launch_job_property_group_id_arr) . ') 
                                           ' . $where_clause . '
                            ';
                    DB::update(
                        DB::raw($sql)
                    );

                    continue;
                case
                'users':
                    if (isset($EventObj->model_arr['launch_job_user_id_arr']) && $EventObj->model_arr['launch_job_user_id_arr'])
                    {
                        $launch_job_user_id_arr = $EventObj->model_arr['launch_job_user_id_arr'];
                    }
                    else
                    {
                        $launch_job_user_id_arr = $ClientObj->users->pluck('id')->toArray();
                    }
                    $sql = '
                                UPDATE pre_calc_status 
                                    SET is_soiled = true,
                                         soiled_at = NOW()
                                    where
                                          pre_calc_status.user_id in (' . implode(',', $launch_job_user_id_arr) . ') 
                                           ' . $where_clause . '
                            ';
                    DB::update(
                        DB::raw($sql)
                    );
                    continue;
                default:
                    throw new Exception('Invalid table at ' . __FILE__ . ':' . __LINE__);
            }
        }
    }

    /**
     *
     */
    public function listenerLogMessage(
        $EventObj
    ) {
        $log_message = 'event_listener_job_trace_message  ';

        $log_message .= isset($EventObj->options['event_trigger_id']) ? 'event_trigger_id=' . $EventObj->options['event_trigger_id'] : ' No event_trigger_id passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_class']) ? ' === event_trigger_class=' . $EventObj->options['event_trigger_class'] : ' No event_trigger_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_class_instance']) ? ' === event_trigger_class_instance=' . $EventObj->options['event_trigger_class_instance'] : ' No event_trigger_class_instance passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_object_class']) ? ' === event_trigger_object_class=' . $EventObj->options['event_trigger_object_class'] : ' No event_trigger_object_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_object_class_id']) ? ' === event_trigger_object_class_id=' . $EventObj->options['event_trigger_object_class_id'] : ' No event_trigger_object_class_id passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_absolute_class']) ? ' === event_trigger_absolute_class=' . $EventObj->options['event_trigger_absolute_class'] : ' No event_trigger_absolute_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_file']) ? ' === event_trigger_file=' . $EventObj->options['event_trigger_file'] : ' No event_trigger_file passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_line']) ? ' === event_trigger_line=' . $EventObj->options['event_trigger_line'] : ' No event_trigger_line passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_event_class']) ? ' === event_trigger_event_class=' . $EventObj->options['event_trigger_event_class'] : ' No event_trigger_event_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message .= isset($EventObj->options['event_trigger_message']) ? ' === event_trigger_message=' . $EventObj->options['event_trigger_message'] : '';
        $log_message .= isset($EventObj->options['wipe_out_list']) ? ' === wipe_out_list=' . print_r($EventObj->options['wipe_out_list'], true) : ' No wipe out list passed';

        if (config('services.laravel_logger.enabled', false))
        {
            Log::error(
                $log_message
            );
        }
        if (config('services.rollbar.enabled', true))
        {
            Rollbar::init(
                config('services.rollbar'),
                false,
                false
            );
            $RollBarResponseObj = Rollbar::log(Level::error(), $log_message);
            if ( ! $RollBarResponseObj->wasSuccessful())
            {
                error_log('At ' . __FILE__ . ':' . __LINE__ . 'logging with Rollbar failed to write the following === ' . $log_message, 500);
            }
        }
    }

    /**
     * @param $EventObj
     */
    public function populate_model_arr(&$EventObj)
    {
        $class_of_event_model = $EventObj->getModelArr()['model_name'];
        $model_arr            = array_merge($EventObj->getModelArr(), $this->model_arr, $EventObj->options);
        $parentage_arr        = class_parents($class_of_event_model);
        if ($class_of_event_model == Client::class || in_array(Client::class, $parentage_arr))
        {
            $EventObj->setModelArr(Client::find($EventObj->getModelArr()['id'])->toArray());
            $model_arr['client_id'] = $model_arr['id'];
        }
        elseif (
            $class_of_event_model == User::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserDetail::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserSummary::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == UserAdmin::class || in_array(User::class, $parentage_arr) ||
            $class_of_event_model == Heartbeat::class || in_array(User::class, $parentage_arr)
        )
        {
            $model_arr['user_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $UserObj                = User::find($model_arr['id']);
                $model_arr['client_id'] = $UserObj->client_id;
            }
        }
        elseif (
            $class_of_event_model == Property::class || in_array(Property::class, $parentage_arr)
        )
        {
            $model_arr['property_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $PropertyObj            = Property::find($model_arr['id']);
                $model_arr['client_id'] = $PropertyObj->client_id;
            }
        }
        elseif ($class_of_event_model == PropertyGroupProperty::class || in_array(PropertyGroupProperty::class, $parentage_arr))
        {
            $model_arr['property_group_property_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {
                $PropertyGroupPropertyObj = PropertyGroupProperty::find($model_arr['id']);
                $model_arr['client_id']   = $PropertyGroupPropertyObj->property->client_id;
            }
        }
        elseif ($class_of_event_model == AccessList::class || in_array(AccessList::class, $parentage_arr))
        {
            $model_arr['access_list_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {
                $AccessListObj          = AccessList::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListObj->client_id;
            }
        }
        elseif ($class_of_event_model == AccessListProperty::class || in_array(AccessListProperty::class, $parentage_arr))
        {
            $model_arr['access_list_property'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $AccessListPropertyObj  = AccessListProperty::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListPropertyObj->property->client_id;
            }
        }
        elseif (
            $class_of_event_model == AccessListUser::class || in_array(AccessListProperty::class, $parentage_arr) ||
            $class_of_event_model == AccessListUserFull::class || in_array(AccessListUserFull::class, $parentage_arr)
        )
        {
            $model_arr['access_list_user_id'] = $model_arr['id'];

            if ( ! isset($model_arr['client_id']))
            {
                $AccessListUserObj      = AccessList::find($model_arr['id']);
                $model_arr['client_id'] = $AccessListUserObj->user->client_id;
            }
        }
        elseif ($class_of_event_model == AdvancedVarianceThreshold::class || in_array(AdvancedVarianceThreshold::class, $parentage_arr))
        {
            $model_arr['advanced_variance_threshold_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $AdvancedVarianceThresholdObj = AdvancedVarianceThreshold::find($model_arr['advanced_variance_threshold_id']);
                $model_arr['client_id']       = $AdvancedVarianceThresholdObj->client_id;
            }
        }
        elseif (
            $class_of_event_model == AdvancedVariance::class || in_array(AdvancedVariance::class, $parentage_arr)
        )
        {
            $model_arr['advanced_variance_id'] = $model_arr['id'];
            if ( ! isset($model_arr['client_id']))
            {
                $AdvancedVarianceObj    = AdvancedVariance::find($model_arr['advanced_variance_id']);
                $model_arr['client_id'] = $AdvancedVarianceObj->property->client_id;
            }
        }
        elseif ($class_of_event_model == AdvancedVarianceLineItemResolvedNotification::class || in_array(AdvancedVarianceLineItemResolvedNotification::class, $parentage_arr))
        {
            /**
             * already filled in AdvancedVarianceLineItemResolvedNotificationEvent
             */
        }
        else
        {
            if ( ! isset($model_arr['client_id']))
            {
                throw new ListenerException('invalid ModelClass of ' . $class_of_event_model . ' at ' . __CLASS__);
            }
        }

        $EventObj->setModelArr($model_arr);
    }

    /**
     * @param $EventObj
     * @return false|string
     */
    private function build_log_message($EventObj): string
    {
        /**
         * @todo add launch_job_property_id_arr launch_job_property_group_id_arr and launch_job_user_id_arr
         */
        $wipe_out_list = ' No wipe out list passed';
        if (isset($EventObj->options['wipe_out_list']))
        {
            $wipe_out_list = $EventObj->options['wipe_out_list'];
        }
        $log_message['event_trigger_id']              = isset($EventObj->options['event_trigger_id'])
            ? $EventObj->options['event_trigger_id']
            : ' No event_trigger_id passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_class']           = isset($EventObj->options['event_trigger_class'])
            ? $EventObj->options['event_trigger_class']
            : ' No event_trigger_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_class_instance']  = isset($EventObj->options['event_trigger_class_instance'])
            ? $EventObj->options['event_trigger_class_instance']
            : ' No event_trigger_class_instance passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_object_class']    = isset($EventObj->options['event_trigger_object_class'])
            ? $EventObj->options['event_trigger_object_class']
            : ' No event_trigger_object_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_object_class_id'] = isset($EventObj->options['event_trigger_object_class_id'])
            ? $EventObj->options['event_trigger_object_class_id']
            : ' No event_trigger_object_class_id passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_absolute_class']  = isset($EventObj->options['event_trigger_absolute_class'])
            ? $EventObj->options['event_trigger_absolute_class']
            : ' No event_trigger_absolute_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_file']            = isset($EventObj->options['event_trigger_file'])
            ? $EventObj->options['event_trigger_file']
            : ' No event_trigger_file passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_line']            = isset($EventObj->options['event_trigger_line'])
            ? $EventObj->options['event_trigger_line']
            : ' No event_trigger_line passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_event_class']     = isset($EventObj->options['event_trigger_event_class'])
            ? $EventObj->options['event_trigger_event_class']
            : ' No event_trigger_event_class passed to ' . self::class . '. Please file a ticket with dev';
        $log_message['event_trigger_message']         = isset($EventObj->options['event_trigger_message'])
            ? $EventObj->options['event_trigger_message']
            : '';
        $log_message['listener_class']                = get_class($this);
        $log_message['wipe_out_list']                 = $wipe_out_list;

        $log_message_json_string = json_encode($log_message, JSON_PRETTY_PRINT);

        return $log_message_json_string;
    }

    /**
     * @param string $log_message_json_string
     */
    private function send_message(string $log_message_json_string)
    {
        /**
         * @todo add launch_job_property_id_arr launch_job_property_group_id_arr and launch_job_user_id_arr
         */
        if (config('services.laravel_logger.enabled', true))
        {
            Log::alert(
                $log_message_json_string
            );
        }

        if (config('services.graylog.enabled', false))
        {
            try
            {
                $GraybarObj     = new Graylog();
                $GraylogMessage = new Gelf_Message();
                $GraylogMessage->setShortMessage($log_message_json_string)
                               ->setLevel(Psr_Log_LogLevel::ALERT)
                               ->setFacility('hermes')
                               ->setAdditional('type', 'event_listener_job_trace_message')
                               ->setHost(gethostname());

                $GraybarObj->publisher->publish($GraylogMessage);
            }
            catch (Exception $ExceptionObj)
            {
                if (config('services.rollbar.enabled', false))
                {
                    try
                    {
                        /** @var \Rollbar\Response $RollBarResponseObj */
                        $RollBarResponseObj = Rollbar::log(Level::error(), $ExceptionObj);
                        if ( ! $RollBarResponseObj->wasSuccessful())
                        {
                            Log::error(
                                'logging with Rollbar failed'
                            );
                        }
                    }
                    catch (Exception $RollBarExceptionObj)
                    {
                        Log::error(
                            'logging with Rollbar failed ' . $RollBarExceptionObj->getMessage()
                        );
                    }
                }
            }
        }
    }
}
