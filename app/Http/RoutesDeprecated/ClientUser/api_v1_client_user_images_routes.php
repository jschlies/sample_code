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
    [
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
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        /**
         * clients
         */
        Route::get(
            'clients/{client_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.showClientImages',
                'uses' => 'ImageDeprecatedController@showClientImages',
            ]
        );
        Route::post(
            'clients/{client_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.storeClientImage',
                'uses' => 'ImageDeprecatedController@storeClientImage',
            ]
        );
        /**
         * properties
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.showPropertyImages',
                'uses' => 'ImageDeprecatedController@showPropertyImages',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.storePropertyImage',
                'uses' => 'ImageDeprecatedController@storePropertyImage',
            ]
        );
        /**
         * users
         */
        Route::get(
            'clients/{client_id}/users/{user_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.showUserImages',
                'uses' => 'ImageDeprecatedController@showPropertyImages',
            ]
        );
        Route::post(
            'clients/{client_id}/users/{user_id}/images',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ImageDeprecatedController.storeUserImage',
                'uses' => 'ImageDeprecatedController@storeUserImage',
            ]
        );
    }
);
