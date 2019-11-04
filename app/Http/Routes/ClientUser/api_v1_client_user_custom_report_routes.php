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
         * customReportType
         */
        Route::get(
            '/clients/{client_id}/customReportTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeController.index',
                'uses' => 'CustomReportTypeController@index',
            ]
        );
        /**
         * customReportType
         */
        Route::get(
            '/clients/{client_id}/customReportTypes/{custom_report_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.CustomReportTypeController.show',
                'uses' => 'CustomReportTypeController@show',
            ]
        );

        /**
         * customReport
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/customReportsDetail',
            [
                'as'   => 'api.v1.CustomReportDetailController.getCustomReportsForProperties',
                'uses' => 'CustomReportDetailController@getCustomReportsForProperties',
            ]
        );

        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/customReportsDetail',
            [
                'as'   => 'api.v1.CustomReportDetailController.getCustomReportsForGroups',
                'uses' => 'CustomReportDetailController@getCustomReportsForGroups',
            ]
        );
    }
);
