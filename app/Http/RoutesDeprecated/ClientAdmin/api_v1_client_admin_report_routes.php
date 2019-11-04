<?php

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
        Route::get(
            '/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaReportDeprecatedController.show',
                'uses' => 'NativeCoaReportDeprecatedController@show',
            ]
        );
        Route::get(
            '/reportTemplates/{report_template}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateReportDeprecatedController.show',
                'uses' => 'ReportTemplateReportDeprecatedController@show',
            ]
        );

        Route::get(
            '/clients/{client_id_old}/property_groups/list',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyGroupReportDeprecatedController.list_property_groups_by_client_id_old',
                'uses' => 'PropertyGroupReportDeprecatedController@list_property_groups_by_client_id_old',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyReportDeprecatedController.index',
                'uses' => 'PropertyReportDeprecatedController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/users',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserReportDeprecatedController.index',
                'uses' => 'UserReportDeprecatedController@index',
            ]
        );
    }
);