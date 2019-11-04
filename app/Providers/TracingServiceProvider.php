<?php

namespace App\Waypoint\Providers;

use Illuminate\Support\ServiceProvider;
use App\Waypoint\Http\Middleware\TracingLogger;
use Illuminate\Database\Events\QueryExecuted;
use DB;
use Carbon\Carbon;

class TracingServiceProvider extends ServiceProvider
{
    /**
     * Setup the DB event listener to add Honeycomb spans.
     *
     * @return void
     */
    public function boot()
    {
        if (config('waypoint.tracing_enabled'))
        {
            DB::setEventDispatcher(app('events'));
            DB::listen(
                function (QueryExecuted $event)
                {
                    $span_arr = ['query' => (string) $event->sql];
                    $duration_ms = $event->time;

                    // best estimate start time - it would be better if the ORM gave this to us
                    $start_time = Carbon::createFromTimestampMs(1000 * microtime(true) - $duration_ms);
                    TracingLogger::logSpan(
                        'sql-query',
                        $start_time,
                        $duration_ms,
                        $span_arr
                    );

                    if (isset(TracingLogger::$root_span_arr['sql_queries']))
                    {
                        TracingLogger::$root_span_arr['sql_queries'] += 1;
                    }
                    else
                    {
                        TracingLogger::$root_span_arr['sql_queries'] = 1;
                    }
                    if (isset(TracingLogger::$root_span_arr['sql_duration_ms']))
                    {
                        TracingLogger::$root_span_arr['sql_duration_ms'] += $duration_ms;
                    }
                    else
                    {
                        TracingLogger::$root_span_arr['sql_duration_ms'] = $duration_ms;
                    }
                }
            );
        }
    }
}
