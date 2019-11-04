<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * |--------------------------------------------------------------------------
 * | Auth Routes
 * |--------------------------------------------------------------------------
 */

Route::post(
    'update_password_with_token',
    [
        'as'   => 'api.v1.Auth0Controller.updatePasswordWithToken',
        'uses' => 'Auth0Controller@updatePasswordWithToken',
    ]
);
/**
 * by design, we allow anyone to 'logout'. This helps with the multi-tab use cases
 */
Route::get(
    'logout',
    [
        'as'   => 'api.v1.Auth0Controller.logout',
        'uses' => 'Auth0Controller@logout',
    ]
);

Route::get(
    'logout/clearState',
    [
        'as'   => 'api.v1.Auth0Controller.logoutClearState',
        'uses' => 'Auth0Controller@logoutClearState',
    ]
);

/**
 * by design, we allow anyone to 'logout'. This helps with the multi-tab use cases
 */
Route::group(
    ['middleware' => ['web']],
    function ()
    {
        Route::get(
            'logout',
            [
                'as'   => 'api.v1.Auth0Controller.logout',
                'uses' => 'Auth0Controller@logout',
            ]
        );
        /**
         * @todo Have Nicholas stop hitting this route
         */
        Route::get(
            'api/v1/logout',
            [
                'as'   => 'api.v1.Auth0Controller.logout',
                'uses' => 'Auth0Controller@logout',
            ]
        );

        Route::get(
            'logout/clearState',
            [
                'as'   => 'api.v1.Auth0Controller.logoutClearState',
                'uses' => 'Auth0Controller@logoutClearState',
            ]
        );
        /**
         * @todo Have Nicholas stop hitting this route
         */
        Route::get(
            'api/v1/logout/clearState',
            [
                'as'   => 'api.v1.Auth0Controller.logoutClearState',
                'uses' => 'Auth0Controller@logoutClearState',
            ]
        );
    }
);

Route::group(
    ['prefix' => 'api'],
    function ()
    {
        Route::group(
            [
                'prefix'     => 'v1',
                'middleware' => ['api_with_session'],
            ],
            function ()
            {
                /**
                 * use a one_time_token and expiry to update password
                 */
                Route::post(
                    '/updatePasswordWithToken',
                    [
                        'as'   => 'api.v1.Auth0Controller.updatePasswordWithToken',
                        'uses' => 'Auth0Controller@updatePasswordWithToken',
                    ]
                );
            }
        );
    }
);

Route::group(
    ['middleware' => ['web']],
    function ()
    {
        require Config::get('waypoint.api_v1_api_auth0_routes');
        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            require Config::get('waypoint.api_v1_api_auth0_routes_deprecated');

        }
    }
);

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware' => ['web'], 'namespace' => 'Api', 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::get(
                    'heartbeat',
                    [
                        'as'   => 'api.v1.HeartbeatController.index',
                        'uses' => 'HeartbeatController@index',
                    ]
                );
                Route::get(
                    'heartbeatDetail',
                    [
                        'as'   => 'api.v1.HeartbeatDetailController.index',
                        'uses' => 'HeartbeatDetailController@index',
                    ]
                );
                Route::get(
                    'setClientCookie/{client_cookie_value}',
                    [
                        'as'   => 'api.v1.ClientCookieController.index',
                        'uses' => 'ClientCookieController@index',
                    ]
                );
                require Config::get('waypoint.api_v1_admin_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_admin_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_comments_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_comments_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_commentsDetail_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_commentsDetail_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_attachments_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_attachments_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_related_users_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_related_users_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_audits_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_audits_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_advanced_variance_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_advanced_variance_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_advanced_variance_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_advanced_variance_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_images_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_images_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_ecm_projects_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_ecm_projects_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_user_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_user_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_coa_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_coa_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_user_custom_report_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_user_custom_report_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_custom_report_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_custom_report_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_coa_routes');

                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_coa_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_report_template_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_report_template_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_favorites_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_favorites_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_user_lease_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_user_lease_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_authenticating_entity_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_authenticating_entity_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_client_admin_ecm_projects_routes');
                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                {
                    require Config::get('waypoint.api_v1_client_admin_ecm_projects_routes_deprecated');
                }
                require Config::get('waypoint.api_v1_waypoint_associate_routes');

                require Config::get('waypoint.api_v1_native_chart_routes');
            }
        );
    }
);

/*
|--------------------------------------------------------------------------
| Ledger routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware' => ['web'], 'prefix' => 'api', 'namespace' => 'Api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1', 'namespace' => 'Ledger'],
            function ()
            {
                Route::group(
                    ['prefix' => 'ledger'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_ledger_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_ledger_routes_deprecated');
                        }
                    }
                );
            }
        );
        /**
         * README README README README README README README README README
         * See ApiController. download ion the URI triggers csv download
         * README README README README README README README README README
         */
        Route::group(
            ['prefix' => 'v1', 'namespace' => 'Ledger'],
            function ()
            {
                Route::group(
                    ['prefix' => 'ledger'],
                    function ()
                    {
                        Route::group(
                            ['prefix' => 'download'],
                            function ()
                            {
                                require Config::get('waypoint.api_v1_ledger_routes');
                                if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                                {
                                    require Config::get('waypoint.api_v1_ledger_routes_deprecated');
                                }
                            }
                        );
                    }
                );
            }
        );
    }
);

/*
|--------------------------------------------------------------------------
| Report routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware' => ['web'], 'namespace' => 'Api', 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    ['prefix' => 'report', 'namespace' => 'Report'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_client_admin_report_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_client_admin_report_routes_deprecated');
                        }
                        require Config::get('waypoint.api_v1_client_user_report_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_client_user_report_routes_deprecated');
                        }
                    }
                );
            }
        );
    }
);

/*
|--------------------------------------------------------------------------
| Waypoint_hermes_master_bridge routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware' => ['apiguard'], 'namespace' => 'Api', 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    ['prefix' => 'waypointMasterBridge', 'namespace' => 'Report'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_waypoint_hermes_master_bridge_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_waypoint_hermes_master_bridge_routes_deprecated');
                        }
                    }
                );
            }
        );
    }
);

Route::group(
    ['middleware' => ['apiguard'], 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    ['prefix' => 'apiKey'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_apikey_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_apikey_routes_deprecated');

                        }
                    }
                );
            }
        );
    }
);

Route::group(
    ['middleware' => ['apiguard_with_session'], 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    ['prefix' => 'apiKey'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_apikey_login_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_apikey_login_routes_deprecated');
                        }
                    }
                );
            }
        );
    }
);

/*
|--------------------------------------------------------------------------
| api_v1_admin_api_key_routes routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware' => ['apiguard'], 'namespace' => 'Api', 'prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    ['prefix' => 'artisan'],
                    function ()
                    {
                        require Config::get('waypoint.api_v1_artisan_routes');
                        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
                        {
                            require Config::get('waypoint.api_v1_artisan_routes_deprecated');
                        }
                    }
                );
            }
        );
    }
);

/*
|--------------------------------------------------------------------------
| V2 API routes
|--------------------------------------------------------------------------
*/
Route::group(
    [
        'middleware' => ['web'],
        'namespace'  => 'Api'
    ],
    function ()
    {
        require Config::get('waypoint.api_v2_client_user_routes');
    }
);
