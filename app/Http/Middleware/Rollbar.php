<?php

namespace App\Waypoint\Http\Middleware;

use Closure;

class Rollbar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('services.rollbar.enabled', true))
        {
            \App\Waypoint\Rollbar::init(
                config('services.rollbar'),
                false,
                false
            );
        }
        return $next($request);
    }
}
