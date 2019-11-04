<?php

namespace App\Waypoint\Http;

use App\Waypoint\Http\Middleware\Authenticate;
use App\Waypoint\Http\Middleware\EncryptCookies;
use App\Waypoint\Http\Middleware\EntrustAbility;
use App\Waypoint\Http\Middleware\EntrustPermission;
use App\Waypoint\Http\Middleware\EntrustRole;
use App\Waypoint\Http\Middleware\RedirectIfAuthenticated;
use App\Waypoint\Http\Middleware\TracingLogger;
use App\Waypoint\Http\Middleware\Rollbar;
use App\Waypoint\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TracingLogger::class,
        Rollbar::class,
        CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],

        'api_with_session'      => [
            'throttle:60,1',
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
        ],
        'apiguard_with_session' => [
            'throttle:60,1',
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            ApiGuard::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'       => Authenticate::class,
        'guest'      => RedirectIfAuthenticated::class,
        'throttle'   => ThrottleRequests::class,

        /**
         * See https://github.com/Zizaco/entrust#usage
         */
        'role'       => EntrustRole::class,
        'permission' => EntrustPermission::class,
        'ability'    => EntrustAbility::class,

        'apiguard' => ApiGuard::class,
    ];
}
