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

use App\Waypoint\Http\ApiController;
use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . implode(
                '|',
                [
                    Role::WAYPOINT_ROOT_ROLE,
                    Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
    ],
    function ()
    {
        Route::delete(
            'users/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.destroy',
                'uses' => 'UserPublicDeprecatedController@destroy',
            ]
        );
        Route::delete(
            'userDetails/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.destroy',
                'uses' => 'UserPublicDeprecatedController@destroy',
            ]
        );

        Route::post(
            'clients/{client_id}/users',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.store',
                'uses' => 'UserPublicDeprecatedController@store',
            ]
        );
        Route::put(
            'userDetails/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.updateAdmin',
                'uses' => 'UserPublicDeprecatedController@updateAdmin',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersSummary',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserSummaryDeprecatedController.indexForClient',
                'uses' => 'UserSummaryDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            '/users/{user_id}/usersDetail',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.show',
                'uses' => 'UserPublicDeprecatedController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersDetail/{user_id_arr?}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.indexUserDetailForClient',
                'uses' => 'UserPublicDeprecatedController@indexUserDetailForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            '/clients/{client_id}/users/{user_id_arr?}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.indexUserForClient',
                'uses' => 'UserPublicDeprecatedController@indexUserForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::post(
            '/clients/{client_id}/users/{user_id_arr?}/inviteUser',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.inviteUser',
                'uses' => 'UserPublicDeprecatedController@inviteUser',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::post(
            '/clients/{client_id}/users/{user_id_arr?}/inviteUserCancel',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.inviteUserCancel',
                'uses' => 'UserPublicDeprecatedController@inviteUserCancel',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::delete(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AdvancedVarianceDetailDeprecatedController.destroy',
                'uses' => 'AdvancedVarianceDetailDeprecatedController@destroy',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersBulk',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserSummaryDeprecatedController.downloadUsersForClient',
                'uses' => 'UserSummaryDeprecatedController@downloadUsersForClient',
            ]
        );
    }
);
