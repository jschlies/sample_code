<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where all API routes are defined.
|
*/

/**
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE's and Role::WAYPOINT_ASSOCIATE_ROLE's,
 * Role::CLIENT_ADMINISTRATIVE_USER_ROLE's and Role::CLIENT_GENERIC_USER_ROLL's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here
 */

use App\Waypoint\Models\Role;

Route::group(
    ['prefix' => 'api'],
    function ()
    {
        Route::group(
            [
                'prefix'     => 'v2',
                'middleware' => [
                    'role:' . implode(
                        '|',
                        [
                            Role::WAYPOINT_ROOT_ROLE,
                            Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
                            Role::WAYPOINT_ASSOCIATE_ROLE,
                            Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                            Role::CLIENT_GENERIC_USER_ROLE,
                        ]
                    ),
                ],
            ],
            function ()
            {
                Route::get(
                    '/clients/{client_id}/users/{user_id}/accessibleProperties',
                    [
                        'as'   => 'api.v2.AccessListDetailV2Controller.getAccessiblePropertiesForUser',
                        'uses' => 'AccessListDetailV2Controller@getAccessiblePropertiesForUser',
                    ]
                );
                Route::get(
                    '/clients/{client_id}/userDetails/{user_id}/accessibleGroups',
                    [
                        'as'   => 'api.v2.UserPublicV2Controller.showAccessibleGroups',
                        'uses' => 'UserPublicV2Controller@showAccessibleGroups',
                    ]
                );
                Route::get(
                    '/clients/{client_id}/users/{user_id}/accessibleGroups',
                    [
                        'as'   => 'api.v2.UserPublicV2Controller.showAccessibleGroups',
                        'uses' => 'UserPublicV2Controller@showAccessibleGroups',
                    ]
                );
            }
        );
    }
);
