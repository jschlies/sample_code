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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular can be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here (ie Root routes)
 */

use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . Role::WAYPOINT_ROOT_ROLE,
        ],
        'prefix'     => Role::WAYPOINT_ROOT_ROLE,
    ],
    function ()
    {
        /** favorites */
        Route::resource("favorites", "FavoriteDeprecatedController", ['except' => ['edit', 'create', 'update']]);

        /** favoriteGroups */
        Route::resource("favoriteGroups", "FavoriteGroupDeprecatedController", ['except' => ['edit', 'create', 'store', 'show', 'update', 'destroy']]);

        /** accessListsDetail */
        Route::get(
            'accessListsDetail/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AccessListDetailDeprecatedController.show',
                'uses' => 'AccessListDetailDeprecatedController@show',
            ]
        );

        /** accessListsSummary */
        Route::get(
            'accessListsSummary',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AccessListSummaryDeprecatedController.index',
                'uses' => 'AccessListSummaryDeprecatedController@index',
            ]
        );
        Route::get(
            'accessListsSummary/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AccessListSummaryDeprecatedController.show',
                'uses' => 'AccessListSummaryDeprecatedController@show',
            ]
        );

        /** accessListsFull */
        Route::get(
            'accessListsFull',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AccessListFullDeprecatedController.index',
                'uses' => 'AccessListFullDeprecatedController@index',
            ]
        );
        Route::get(
            'accessListsFull/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AccessListFullDeprecatedController.show',
                'uses' => 'AccessListFullDeprecatedController@show',
            ]
        );

        /** clientsFull */
        Route::get(
            'clientsFull',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ClientFullDeprecatedController.index',
                'uses' => 'ClientFullDeprecatedController@index',
            ]
        );
        Route::get(
            'clientsFull/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ClientFullDeprecatedController.show',
                'uses' => 'ClientFullDeprecatedController@show',
            ]
        );

        /** propertiesSummary */
        Route::get(
            'propertiesSummary',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertySummaryDeprecatedController.index',
                'uses' => 'PropertySummaryDeprecatedController@index',
            ]
        );
        Route::get(
            'propertiesSummary/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertySummaryDeprecatedController.show',
                'uses' => 'PropertySummaryDeprecatedController@show',
            ]
        );

        /** propertiesDetail */
        Route::get(
            'propertyDetails',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyDetailDeprecatedController.index',
                'uses' => 'PropertyDetailDeprecatedController@index',
            ]
        );
        Route::get(
            'propertyDetails/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyDetailDeprecatedController.show',
                'uses' => 'PropertyDetailDeprecatedController@show',
            ]
        );

        /** propertyGroupsDetail */
        Route::get(
            'propertyGroupsDetail',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyGroupDetailDeprecatedController.Summary',
                'uses' => 'PropertyGroupDetailDeprecatedController@index',
            ]
        );
        Route::get(
            'propertyGroupsDetail/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyGroupDetailDeprecatedController.show',
                'uses' => 'PropertyGroupDetailDeprecatedController@show',
            ]
        );

        /** propertyGroupsFull */
        Route::get(
            'propertyGroupsFull',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyGroupFullDeprecatedController.Summary',
                'uses' => 'PropertyGroupFullDeprecatedController@index',
            ]
        );
        Route::get(
            'propertyGroupsFull/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.PropertyGroupFullDeprecatedController.show',
                'uses' => 'PropertyGroupFullDeprecatedController@show',
            ]
        );

        /** users */
        Route::post(
            'users/deactivate',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.UserSummaryDeprecatedController.deactivateUsers',
                'uses' => 'UserSummaryDeprecatedController@deactivateUsers',
            ]
        );
        /** usersSummary */
        Route::get(
            'usersSummary',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.UserSummaryDeprecatedController.Summary',
                'uses' => 'UserSummaryDeprecatedController@index',
            ]
        );
        Route::get(
            'usersSummary/{id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.UserSummaryDeprecatedController.show',
                'uses' => 'UserSummaryDeprecatedController@show',
            ]
        );
        Route::put(
            'users/{user_id}/is_hidden',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.UserPublicDeprecatedController.update_is_hidden',
                'uses' => 'UserPublicDeprecatedController@update_is_hidden',
            ]
        );
    }
);