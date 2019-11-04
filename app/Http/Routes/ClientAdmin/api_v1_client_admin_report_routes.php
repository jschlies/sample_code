<?php

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
        Route::get(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/report',
            [
                'as'   => 'api.v1.NativeCoaReportController.show',
                'uses' => 'NativeCoaReportController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template}/report',
            [
                'as'   => 'api.v1.ReportTemplateReportController.show',
                'uses' => 'ReportTemplateReportController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/report',
            [
                'as'   => 'api.v1.PropertyReportController.index',
                'uses' => 'PropertyReportController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/report',
            [
                'as'   => 'api.v1.UserReportController.index',
                'uses' => 'UserReportController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/client_id_old/{client_id_old}/property_groups/list/report',
            [
                'as'   => 'api.v1.PropertyGroupReportController.list_property_groups_by_client_id_old',
                'uses' => 'PropertyGroupReportController@list_property_groups_by_client_id_old',
            ]
        );
    }
);