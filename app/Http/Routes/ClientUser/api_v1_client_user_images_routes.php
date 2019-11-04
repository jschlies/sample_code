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
    ],
    function ()
    {
        /**
         * clients
         */
        Route::get(
            'clients/{client_id}/images',
            [
                'as'   => 'api.v1.ImageController.showClientImages',
                'uses' => 'ImageController@showClientImages',
            ]
        );
        Route::post(
            'clients/{client_id}/images',
            [
                'as'   => 'api.v1.ImageController.storeClientImage',
                'uses' => 'ImageController@storeClientImage',
            ]
        );
        /**
         * properties
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/images',
            [
                'as'   => 'api.v1.ImageController.showPropertyImages',
                'uses' => 'ImageController@showPropertyImages',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/images',
            [
                'as'   => 'api.v1.ImageController.storePropertyImage',
                'uses' => 'ImageController@storePropertyImage',
            ]
        );
        /**
         * users
         */
        Route::get(
            'clients/{client_id}/users/{user_id}/images',
            [
                'as'   => 'api.v1.ImageController.showUserImages',
                'uses' => 'ImageController@showUserImages',
            ]
        );
        Route::post(
            'clients/{client_id}/users/{user_id}/images',
            [
                'as'   => 'api.v1.ImageController.storeUserImage',
                'uses' => 'ImageController@storeUserImage',
            ]
        );
    }
);
