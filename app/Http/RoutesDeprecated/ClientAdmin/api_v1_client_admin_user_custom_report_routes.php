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
        'prefix'     => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
    ],
    function ()
    {
        /**
         * customReportType
         */
        Route::get(
            '/clients/{client_id}/customReportTypes/{custom_report_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeDeprecatedController.show',
                'uses' => 'CustomReportTypeDeprecatedController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/customReportTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeDeprecatedController.store',
                'uses' => 'CustomReportTypeDeprecatedController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/customReportTypes/{custom_report_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeDeprecatedController.update',
                'uses' => 'CustomReportTypeDeprecatedController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/customReportTypes/{custom_report_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeDeprecatedController.destroy',
                'uses' => 'CustomReportTypeDeprecatedController@destroy',
            ]
        );

        /**
         * CustomReportDetail
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/customReportType/{custom_report_type_id}/year/{year}/period/{period}',
            [
                'as'   => 'api.vi.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportDetailDeprecatedController.store',
                'uses' => 'CustomReportDetailDeprecatedController@store',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/customReportsDetail/{custom_report_id}',
            [
                'as'   => 'api.vi.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportDetailDeprecatedController.destroy',
                'uses' => 'CustomReportDetailDeprecatedController@destroy',
            ]
        );
    }
);
