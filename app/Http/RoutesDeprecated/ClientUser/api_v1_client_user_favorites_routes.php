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
                    Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        ##########################################
        # favorites
        ##########################################
        Route::get(
            'favoriteGroups/available',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteGroupDeprecatedController.getAvailable',
                'uses' => 'FavoriteGroupDeprecatedController@getAvailable',
            ]
        );
        /**
         * @todo remove this route once HER-378 is pushed
         */
        Route::get(
            'favoriteGroups/client/{client_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteGroupDeprecatedController.getFavoriteGroupsForClient',
                'uses' => 'FavoriteGroupDeprecatedController@getFavoriteGroupsForClient',
            ]
        );
        Route::get(
            'favoriteGroups/clients/{client_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteGroupDeprecatedController.getFavoriteGroupsForClient',
                'uses' => 'FavoriteGroupDeprecatedController@getFavoriteGroupsForClient',
            ]
        );
        /**
         * @todo remove this route once HER-378 is pushed
         */
        Route::get(
            'favoriteGroups/user/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteGroupDeprecatedController.getFavoriteGroupsForUser',
                'uses' => 'FavoriteGroupDeprecatedController@getFavoriteGroupsForUser',
            ]
        );
        Route::get(
            'favoriteGroups/users/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteGroupDeprecatedController.getFavoriteGroupsForUser',
                'uses' => 'FavoriteGroupDeprecatedController@getFavoriteGroupsForUser',
            ]
        );
        Route::get(
            'favorites/{id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteDeprecatedController.show',
                'uses' => 'FavoriteDeprecatedController@show',
            ]
        );
        Route::post(
            'favorites',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteDeprecatedController.store',
                'uses' => 'FavoriteDeprecatedController@store',
            ]
        );
        /**
         * remember that Route::put('favorites/... does not make sense
         */
        Route::delete(
            'favorites/{favorite_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.FavoriteDeprecatedController.destroy',
                'uses' => 'FavoriteDeprecatedController@destroy',
            ]
        );
    }
);
