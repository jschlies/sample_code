<?php

namespace App\Waypoint\Http\Middleware;

use Zizaco\Entrust\Middleware\EntrustPermission as EntrustPermissionBase;
use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class EntrustPermission
 * @package App\Waypoint\Http\Middleware
 *
 * See https://github.com/Zizaco/entrust#usage
 */
class EntrustPermission extends EntrustPermissionBase
{
    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        parent::__construct($auth);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        return parent::handle($request, $next, $permissions);
    }
}
