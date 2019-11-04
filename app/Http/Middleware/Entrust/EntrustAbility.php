<?php

namespace App\Waypoint\Http\Middleware;

use Zizaco\Entrust\Middleware\EntrustAbility as EntrustAbilityBase;
use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class EntrustAbility
 * @package App\Waypoint\Http\Middleware
 *
 * See https://github.com/Zizaco/entrust#usage
 */
class EntrustAbility extends EntrustAbilityBase
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
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $roles
     * @param $permissions
     * @param bool $validateAll
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions, $validateAll = false)
    {
        return parent::handle($request, $next, $roles, $permissions, $validateAll);
    }
}