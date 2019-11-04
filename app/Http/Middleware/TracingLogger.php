<?php

namespace App\Waypoint\Http\Middleware;

use Closure;
use ErrorException;
use Log;
use Webpatser\Uuid\Uuid;
use \Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use DateTime;

/**
 * Middleware for aggregating request data and logging them in a
 * Honeycomb-compatible format.
 *
 * Each thing we want to record statistics about - such as a request,
 * sql query, or function call - is called a "span" (more generally,
 * these are "events" in Honeycomb parlance but let's not overload that
 * terminology here). A span is represented by an associative array
 * which will be logged as a JSON object. These arrays are freeform sets
 * of key-value pairs, though certain keys are required for tracing to
 * work, as covered below. Keys are strings. Values may be any of
 * string, int, float, bool, null.
 *
 * Honeycomb can tie together multiple spans with its tracing feature:
 * see https://docs.honeycomb.io/working-with-your-data/tracing/send-trace-data/#manual-tracing.
 *
 * Summary of the link above:
 * - A bunch of hierarchically-related spans is a "trace"
 * - For us, one HTTP request == one trace
 * - For us, spans can be many things:
 *   - one at the root level for the HTTP request itself
 *   - one child for each SQL query we make
 *   - one child for any function/service we instrument in the future
 * - To be part of a trace, a span must have these six keys defined:
 *   - trace_id: arbitrary string; must be the same for every span;
 *     implicitly defines the trace
 *   - span_id: arbitrary string; unique across spans in the trace
 *   - parent_id: must equal some other span's span_id or be null
 *   - name: a friendly identifier, e.g. 'sql-query' or a function name
 *   - service_name: always 'hermes'
 *   - duration_ms: how long the thing took, in milliseconds
 */
class TracingLogger
{
    // @todo (Ezra) these variables rely on the happy coincidence that
    // the lifetime of a static var is about the same as that of an HTTP
    // request, which may not hold true in e.g. unit tests. There are
    // Laravel conventions to enforce per-request singleton-ness of
    // middlewares - look into those so we don't rely on statics.
    static $root_span_arr = [];
    static $root_span_time = null;
    const ROOT_SPAN_ID = 'root_span';

    /**
     * A convenience method for initializing a span array.
     *
     * @return array All info that must be the same across each span.
     */
    private static function commonSpanArray(): array
    {
        static $common_span_arr = null;
        if ( ! $common_span_arr )
        {
            $common_span_arr = [
                'environment' => env('APP_ENV', 'unknown'),
                'host' => gethostname(),

                'service_name' => 'hermes',
                'trace.trace_id' => Uuid::generate()->__get('string'),
            ];
        }
        return $common_span_arr;
    }

    /**
     * Log a span as a child of the root span.
     *
     * @param  string  $name
     * @param  float   $timestamp
     * @param  float   $duration_ms
     * @param  array   $span_arr
     * @param  ?string $parent_span_id
     * @param  ?string $span_id
     * @return void
     */
    public static function logSpan(string $name, Carbon $timestamp, float $duration_ms, array $span_arr, ?string $parent_span_id = TracingLogger::ROOT_SPAN_ID, ?string $span_id = null): void
    {
        $span_arr['name'] = $name;
        $span_arr['timestamp'] = $timestamp->format(DateTime::RFC3339_EXTENDED);
        $span_arr['duration_ms'] = $duration_ms;
        $span_arr['trace.span_id'] = $span_id ?? Uuid::generate()->__get('string');
        $span_arr['trace.parent_id'] = $parent_span_id;

        $payload = json_encode(TracingLogger::commonSpanArray() + $span_arr);

        TracingLogger::write($payload);
    }

    /**
     * Records the request time.
     *
     * @param  Request  $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('waypoint.tracing_enabled'))
        {
            TracingLogger::$root_span_time = Carbon::now();
        }
        return $next($request);
    }

    /**
     * Adds details to the root span and logs it.
     *
     * @param  Request  $request
     * @param  Response $response
     */
    public function terminate(Request $request, Response $response): void
    {
        if (config('waypoint.tracing_enabled') && TracingLogger::$root_span_time)
        {
            $termination_time = Carbon::now();

            TracingLogger::$root_span_arr['trace.span_id'] = TracingLogger::ROOT_SPAN_ID;
            TracingLogger::$root_span_arr['trace.parent_id'] = '';

            TracingLogger::$root_span_arr['request.path'] = $request->path();
            TracingLogger::$root_span_arr['request.method'] = $request->method();
            $userObj = $request->user();
            if ($userObj)
            {
                TracingLogger::$root_span_arr['request.user_id'] = $userObj->id;
                TracingLogger::$root_span_arr['request.client_id'] = $userObj->client_id;
            }
            $routeObj = $request->route();
            if ($routeObj && isset($routeObj->uri))
            {
                TracingLogger::$root_span_arr['request.route'] = $routeObj->uri;
            }
            if ($request->hasSession())
            {
                TracingLogger::$root_span_arr['request.session_sha1'] = sha1($request->session()->getId());
            }

            TracingLogger::$root_span_arr['response.status_code'] = $response->status();
            TracingLogger::$root_span_arr['response.length'] = strlen($response->content());

            TracingLogger::logSpan(
                'request',
                TracingLogger::$root_span_time,
                TracingLogger::$root_span_time->diffInMilliseconds($termination_time),
                TracingLogger::$root_span_arr,
                null,
                TracingLogger::ROOT_SPAN_ID
            );

            TracingLogger::closeFileHandle();
        }
    }

    // @todo there's probably a more Laravel-idiomatic way to do all the
    // log file opening ceremony.
    static $fileHandle = null;
    private static function write(string $payload): void
    {
        if (TracingLogger::$fileHandle === false)
        {
            // We've already failed once at opening the file and logged
            // the error; give up.
            return;
        }
        try {
            if (TracingLogger::$fileHandle)
            {
                fwrite(TracingLogger::$fileHandle, $payload . "\n");
            }
            else
            {
                // Must open read-write even though we never read.
                // Otherwise fopen can block if targeting a named pipe.
                TracingLogger::$fileHandle = fopen(TracingLogger::getPath(), 'a+');
                if (TracingLogger::$fileHandle)
                {
                    stream_set_blocking(TracingLogger::$fileHandle, false);
                    fwrite(TracingLogger::$fileHandle, $payload . "\n");
                }
                else
                {
                    Log::error('Could not open tracing log file: ' . TracingLogger::getPath());
                }
            }
        }
        catch (ErrorException $err)
        {
            Log::error('Could not write to tracing log file: ' . TracingLogger::getPath() . ' due to error: ' . $err->getMessage());
            TracingLogger::$fileHandle = false;
        }
    }

    private static function closeFileHandle()
    {
        if (TracingLogger::$fileHandle)
        {
            fclose(TracingLogger::$fileHandle);
            TracingLogger::$fileHandle = null;
        }
    }

    /**
     * @return mixed|string|null
     */
    private static function getPath()
    {
        static $path = null;
        if ( ! $path)
        {
            $path = config('waypoint.tracing_log_location');
            if (is_dir($path) || substr($path, -1) == '/')
            {
                $path = $path . '/trace-json-' . get_current_user() . '-' . Carbon::now()->toDateString(). '.log';
            }
        }
        return $path;
    }
}
