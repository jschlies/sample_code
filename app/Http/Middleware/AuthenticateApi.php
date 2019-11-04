<?php

namespace App\Waypoint\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest())
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return response(['success' => false], 401);
                //return redirect()->guest('login');
                /**
                 * later use this
                 * return response(['success' => false], 401);
                 */
            }
        }

        return $next($request);
    }
}
