<?php

use App\Waypoint\Models\Role;

Route::group(
    [
        'prefix' => Role::WAYPOINT_ROOT_ROLE,
    ],
    function ()
    {
        Route::get(
            '/clients/{client_id}/apikey/login',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.Auth0Controller.login_via_apiKey',
                'uses' => 'Auth0Controller@login_via_apiKey',
            ]
        );
    }
);