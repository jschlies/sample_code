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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::CLIENT_ADMINISTRATIVE_USER_ROLE's. Note they are
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
        Route::get(
            'favoriteGroups/available',
            [
                'as'   => 'api.v1.FavoriteGroupController.getAvailable',
                'uses' => 'FavoriteGroupController@getAvailable',
            ]
        );

        Route::get(
            '/clients/{client_id}/favoriteGroups',
            [
                'as'   => 'api.v1.FavoriteGroupController.getFavoriteGroupsForClient',
                'uses' => 'FavoriteGroupController@getFavoriteGroupsForClient',
            ]
        );
        /**
         * @todo remove this route once HER-378 is pushed
         */
        Route::get(
            '/clients/{client_id}/user/{user_id}/favoriteGroups',
            [
                'as'   => 'api.v1.FavoriteGroupController.getFavoriteGroupsForUser',
                'uses' => 'FavoriteGroupController@getFavoriteGroupsForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/favorites/{favorite_id}',
            [
                'as'   => 'api.v1.FavoriteController.show',
                'uses' => 'FavoriteController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/favorites',
            [
                'as'   => 'api.v1.FavoriteController.store',
                'uses' => 'FavoriteController@store',
            ]
        );
        /**
         * remember that Route::put('favorites/... does not make sense
         */
        Route::delete(
            '/clients/{client_id}/favorites/{favorite_id}',
            [
                'as'   => 'api.v1.FavoriteController.destroy',
                'uses' => 'FavoriteController@destroy',
            ]
        );
    }
);
