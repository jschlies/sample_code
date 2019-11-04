<?php

use \App\Waypoint\Models\Role;

/**
 * |--------------------------------------------------------------------------
 * | Auth Routes
 * |--------------------------------------------------------------------------
 */

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
    'setup-password',
    [
        'as'   => 'api.v1.Auth0Controller.renderSetupPasswordPage',
        'uses' => 'Auth0Controller@renderSetupPasswordPage',
    ]
);

Route::get(
    'logout/clearState',
    [
        'as'   => 'api.v1.Auth0Controller.logoutClearState',
        'uses' => 'Auth0Controller@logoutClearState',
    ]
);

Route::group(
    ['middleware' => ['web']],
    function ()
    {
        /**
         * @todo clean this up
         *       Auth0Controller should be in a  Auth0 namespace
         *       prefix of api/v1??????
         */
        Route::get('/auth0/callback', 'Auth0Controller@callbackWithRequest');
    }
);

Route::group(
    ['prefix' => 'api'],
    function ()
    {
        Route::group(
            ['prefix' => 'v1'],
            function ()
            {
                Route::group(
                    [
                        'middleware' => [
                            'role:' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                        ],
                        'prefix'     => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                    ],
                    function ()
                    {
                        /**
                         * add a user to Hermes and (if not already) Auth0
                         */
                        Route::post(
                            'clients/{client_id}/users/auth0',
                            [
                                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.Auth0Controller.store',
                                'uses' => 'Auth0Controller@store',
                            ]
                        );
                    }
                );
                Route::group(
                    [
                        'middleware' => [
                            'role:' . implode(
                                '|',
                                [
                                    Role::WAYPOINT_ROOT_ROLE,
                                    Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                                    Role::CLIENT_GENERIC_USER_ROLE,
                                ]
                            ),
                        ],
                        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
                    ],
                    function ()
                    {
                        /**
                         * get a one_time_token and expiry
                         */
                        Route::post(
                            '/clients/{client_id}/users/{user_id}/auth0/passwordToken',
                            [
                                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Auth0Controller.generatePasswordToken',
                                'uses' => 'Auth0Controller@generatePasswordToken',
                            ]
                        );
                    }
                );
            }
        );
    }
);