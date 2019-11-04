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
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here and app/Http/Routes/api_v1_admin_routes.php (ie Root routes)
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
        Route::get(
            'clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeCoaDeprecatedController.show',
                'uses' => 'NativeCoaDeprecatedController@show',
            ]
        );
        /** nativeCoasFull */
        Route::get(
            'clients/{client_id}/nativeCoasFull',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeCoaDeprecatedController.indexForClient',
                'uses' => 'NativeCoaDeprecatedController@indexForClient',
            ]
        );

        /** reportTemplatesFull */
        Route::get(
            'clients/{client_id}/reportTemplatesFull',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateFullDeprecatedController.index',
                'uses' => 'ReportTemplateFullDeprecatedController@index',
            ]
        );

        /** reportTemplatesFull */
        Route::get(
            'clients/{client_id}/reportTemplatesFull/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateFullDeprecatedController.show',
                'uses' => 'ReportTemplateFullDeprecatedController@show',
            ]
        );

        /** reportTemplatesDetail */
        Route::get(
            'clients/{client_id}/reportTemplatesDetail',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateDetailDeprecatedController.index',
                'uses' => 'ReportTemplateDetailDeprecatedController@index',
            ]
        );

        /** reportTemplatesDetail */
        Route::get(
            'clients/{client_id}/reportTemplatesDetail/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateDetailDeprecatedController.show',
                'uses' => 'ReportTemplateDetailDeprecatedController@show',
            ]
        );

        Route::get(
            'clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.show',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@show',
            ]
        );

        Route::get(
            'clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.show',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@show',
            ]
        );

        Route::get(
            'reportTemplateAccountGroupBreadCrumb/{id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateAccountGroupBreadCrumbDeprecatedController.show',
                'uses' => 'ReportTemplateAccountGroupBreadCrumbDeprecatedController@show',
            ]
        );
        Route::get(
            'reportTemplateAccountGroupBreadCrumb/rt_account_group_code/{rt_account_group_code}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateAccountGroupBreadCrumbDeprecatedController.showWithCode',
                'uses' => 'ReportTemplateAccountGroupBreadCrumbDeprecatedController@showWithCode',
            ]
        );

        /** nativeAccountTypes */
        Route::get(
            'clients/{client_id}/nativeAccountTypesDetail',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeAccountTypeDeprecatedController.indexDetail',
                'uses' => 'NativeAccountTypeDeprecatedController@indexDetail',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeAccountTypesDetail/{native_account_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeAccountTypeDeprecatedController.showDetail',
                'uses' => 'NativeAccountTypeDeprecatedController@showDetail',
            ]
        );
    }
);