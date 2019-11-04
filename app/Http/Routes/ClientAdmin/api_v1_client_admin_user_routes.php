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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE's and Role::WAYPOINT_ASSOCIATE_ROLE's
 * and Role::CLIENT_ADMINISTRATIVE_USER_ROLE's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here
 */

use App\Waypoint\Http\ApiController;
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
                ]
            ),
        ],
    ],
    function ()
    {
        Route::delete(
            '/clients/{client_id}/users/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.destroy',
                'uses' => 'UserPublicController@destroy',
            ]
        );
        Route::delete(
            '/clients/{client_id}/userDetails/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.destroy',
                'uses' => 'UserPublicController@destroy',
            ]
        );

        Route::post(
            '/clients/{client_id}/users',
            [
                'as'   => 'api.v1.UserPublicController.store',
                'uses' => 'UserPublicController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/userDetails/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.updateAdmin',
                'uses' => 'UserPublicController@updateAdmin',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersSummary',
            [
                'as'   => 'api.v1.UserSummaryController.indexForClient',
                'uses' => 'UserSummaryController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id_arr?}',
            [
                'as'   => 'api.v1.UserPublicController.indexUserForClient',
                'uses' => 'UserPublicController@indexUserForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::post(
            '/clients/{client_id}/users/{user_id_arr?}/inviteUser',
            [
                'as'   => 'api.v1.UserPublicController.inviteUser',
                'uses' => 'UserPublicController@inviteUser',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::post(
            '/clients/{client_id}/users/{user_id_arr?}/inviteUserCancel',
            [
                'as'   => 'api.v1.UserPublicController.inviteUserCancel',
                'uses' => 'UserPublicController@inviteUserCancel',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/usersBulk',
            [
                'as'   => 'api.v1.UserSummaryController.downloadUsersForClient',
                'uses' => 'UserSummaryController@downloadUsersForClient',
            ]
        );
    }
);
