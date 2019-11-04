<?php

namespace App\Waypoint;

use App;
use function collect_waypoint;
use function is_array;
use function strtoupper;

/**
 * Class CanConfigJSONTrait
 */
trait CanConfigJSONTrait
{
    /**
     * @param bool $return_format_arr
     * @return array|object
     */
    public function getConfigJSON($return_format_arr = false)
    {
        if ($return_format_arr)
        {
            return stdToArray(json_decode($this->config_json));
        }
        return (object) json_decode($this->config_json);
    }

    /**
     * @param array|object $config_json
     * @throws Exceptions\GeneralException
     */
    public function setConfigJSON($config_json = [])
    {
        if ( ! is_array($config_json))
        {
            $config_json = stdToArray($config_json);
        }
        /**
         * enforce rule that all config values (at top level) be UPPER CASE
         */
        foreach ($config_json as $key => $value)
        {
            if ( ! preg_match("/^[A-Z_0-9]*$/", $key))
            {
                throw new App\Waypoint\Exceptions\GeneralException('invalid config key name ' . $key);
            }
            $config_json[strtoupper($key)] = $value;
        }

        $this->config_json = json_encode($config_json);
        $this->save();
    }

    /**
     * @param $config_name
     * @param $config_value
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function updateConfig($config_name, $config_value)
    {
        $config_JSON = $this->getConfigJSON(true);
        if ($config_value === null || $config_value === 'null')
        {
            /**
             * enforce rule that all config values (at top level) be UPPER CASE
             */
            if (isset($config_JSON[strtoupper($config_name)]))
            {
                unset($config_JSON[$config_name]);
            }
        }
        else
        {
            if (is_numeric($config_value))
            {
                $config_JSON[strtoupper($config_name)] = intval($config_value);
            }
            else
            {
                $config_JSON[strtoupper($config_name)] = $config_value;
            }
        }
        $this->setConfigJSON($config_JSON);
    }

    /**
     * @param $config_value
     * @throws Exceptions\GeneralException
     * @throws \Exception
     */
    public function setAllAdvancedVarianceNotificationConfigs($config_value)
    {
        $variance_notification_types = collect_waypoint(App\Waypoint\Models\User::$user_notification_flags)->filter(function ($item)
        {
            return stripos($item, 'variance') !== false;
        });

        foreach ($variance_notification_types->toArray() as $notification_type)
        {
            $this->updateConfig($notification_type, $config_value);
        }
    }

    /**
     * @param $config_value
     * @throws Exceptions\GeneralException
     * @throws \Exception
     */
    public function setAllOpportunitiesNotificationConfigs($config_value)
    {
        $variance_notification_types = collect_waypoint(App\Waypoint\Models\User::$user_notification_flags)->filter(function ($item)
        {
            return stripos($item, 'opportunities') !== false;
        });

        foreach ($variance_notification_types->toArray() as $notification_type)
        {
            $this->updateConfig($notification_type, $config_value);
        }
    }

    /**
     * @param $config_value
     * @throws Exceptions\GeneralException
     * @throws \Exception
     */
    public function setAllNotificationConfigs($config_value)
    {
        foreach (App\Waypoint\Models\User::$user_notification_flags as $notification_type)
        {
            $this->updateConfig($notification_type, $config_value);
        }
    }

    /**
     * @param $config_name
     * @return mixed
     */
    public function getConfigValue($config_name)
    {
        return $this->getConfigJSON()->{$config_name} ?? null;
    }

    /**
     * @return bool
     */
    public function nativeCoaAnalyticsFeatureIsEnabled()
    {
        return (bool) $this->getConfigValue(self::CUSTOM_REPORT_TEMPLATE_ANALYTICS_FLAG);
    }

    /**
     * @return bool
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function canSendNotification()
    {
        if ( ! isset($this->getConfigJSON(true)['NOTIFICATIONS']))
        {
            $this->updateConfig('NOTIFICATIONS', true);
        }
        return (bool) $this->getConfigJSON()->NOTIFICATIONS;
    }

    /**
     * @return bool
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function canUseOpportunities()
    {
        if ( ! isset($this->getConfigJSON()->FEATURE_OPPORTUNITIES))
        {
            $this->updateConfig('FEATURE_OPPORTUNITIES', true);
        }
        return (bool) $this->getConfigJSON()->FEATURE_OPPORTUNITIES;
    }

}
