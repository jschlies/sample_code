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
        Route::get(
            '/clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.NativeCoaController.show',
                'uses' => 'NativeCoaController@show',
            ]
        );
        /** nativeCoasFull */
        Route::get(
            '/clients/{client_id}/nativeCoasFull',
            [
                'as'   => 'api.v1.NativeCoaController.indexForClient',
                'uses' => 'NativeCoaController@indexForClient',
            ]
        );

        /** reportTemplatesFull */
        Route::get(
            '/clients/{client_id}/reportTemplatesFull',
            [
                'as'   => 'api.v1.ReportTemplateFullController.index',
                'uses' => 'ReportTemplateFullController@index',
            ]
        );

        /** reportTemplatesFull */
        Route::get(
            '/clients/{client_id}/reportTemplatesFull/{report_template_id}',
            [
                'as'   => 'api.v1.ReportTemplateFullController.show',
                'uses' => 'ReportTemplateFullController@show',
            ]
        );

        /** reportTemplatesDetail */
        Route::get(
            '/clients/{client_id}/reportTemplatesDetail',
            [
                'as'   => 'api.v1.ReportTemplateDetailController.index',
                'uses' => 'ReportTemplateDetailController@index',
            ]
        );

        /** reportTemplatesDetail */
        Route::get(
            '/clients/{client_id}/reportTemplatesDetail/{report_template_id}',
            [
                'as'   => 'api.v1.ReportTemplateDetailController.show',
                'uses' => 'ReportTemplateDetailController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.show',
                'uses' => 'ReportTemplateAccountGroupController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.show',
                'uses' => 'ReportTemplateAccountGroupController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/reportTemplateAccountGroupBreadCrumb/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupBreadCrumbController.show',
                'uses' => 'ReportTemplateAccountGroupBreadCrumbController@show',
            ]
        );
        Route::get(
            '/reportTemplateAccountGroupBreadCrumb/rt_account_group_code/{rt_account_group_code}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupBreadCrumbController.showWithCode',
                'uses' => 'ReportTemplateAccountGroupBreadCrumbController@showWithCode',
            ]
        );

        /** nativeAccountTypes */
        Route::get(
            '/clients/{client_id}/nativeAccountTypesDetail',
            [
                'as'   => 'api.v1.NativeAccountTypeController.indexDetail',
                'uses' => 'NativeAccountTypeController@indexDetail',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeAccountTypesDetail/{native_account_type_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeController.showDetail',
                'uses' => 'NativeAccountTypeController@showDetail',
            ]
        );

    }
);