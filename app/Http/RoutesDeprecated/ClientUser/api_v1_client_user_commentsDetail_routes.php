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
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        Route::get(
            'clients/{client_id}/commentsDetail/{comment_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.CommentDetailDeprecatedController.show',
                'uses' => 'CommentDetailDeprecatedController@show',
            ]
        );
        Route::get(
            'clients/{client_id}/commentsDetail/commentable_type/{commentable_type}/commentable_id/{commentable_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.CommentDetailDeprecatedController.index',
                'uses' => 'CommentDetailDeprecatedController@index',
            ]
        );
        Route::post(
            'clients/{client_id}/commentsDetail/commentable_type/{commentable_type}/commentable_id/{commentable_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.CommentDetailDeprecatedController.store',
                'uses' => 'CommentDetailDeprecatedController@store',
            ]
        );
        Route::delete(
            'clients/{client_id}/commentsDetail/{comment_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.CommentDetailDeprecatedController.destroy',
                'uses' => 'CommentDetailDeprecatedController@destroy',
            ]
        );
    }
);
