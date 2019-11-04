<?php

namespace App\Waypoint\Providers;

use Illuminate\Log\LogServiceProvider;
use Illuminate\Log\Writer;

class CustomLogServiceProvider extends LogServiceProvider
{

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Illuminate\Log\Writer $log
     * @return void
     */
    protected function configureSingleHandler(Writer $log)
    {
        //sets the path to custom app/log/single-xxxx-xx-xx.log file.
        $log->useFiles($this->_getLogPath() . '/single.log');
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Illuminate\Log\Writer $log
     * @return void
     */
    protected function configureDailyHandler(Writer $log)
    {
        //sets the path to custom app/log/daily-xxxx-xx-xx.log file.
        $log->useDailyFiles($this->_getLogPath() . '/daily-' . get_current_user() . '.log', 0, config('app.log_level', 'debug'));
    }

    private function _getLogPath()
    {
        $storage_path = base_path(env('LARAVEL_LOG_LOCATION', 'storage/logs'));
        return $storage_path;
    }
}
