<?php

namespace App\Waypoint\Http\Middleware;

use Zizaco\Entrust\Middleware\EntrustRole as EntrustRoleBase;
use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class EntrustRole
 * @package App\Waypoint\Http\Middleware
 *
 * See https://github.com/Zizaco/entrust#usage
 */
class EntrustRole extends EntrustRoleBase
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
     * @param  $roles
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        return parent::handle($request, $next, $roles);
    }
}
