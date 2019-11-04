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
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
    ],
    function ()
    {
        /**
         * reportTemplates routes
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/generateAccountTypeBasedReportTemplate',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.generateAccountTypeBasedReportTemplate',
                'uses' => 'ReportTemplateFullDeprecatedController@generateAccountTypeBasedReportTemplate',
            ]
        );
        Route::post(
            '/clients/{client_id}/reportTemplates/generateBomaBasedReportTemplate',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.generateBomaBasedReportTemplate',
                'uses' => 'ReportTemplateFullDeprecatedController@generateBomaBasedReportTemplate',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateDeprecatedController.showForClient',
                'uses' => 'ReportTemplateDeprecatedController@showForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.show',
                'uses' => 'ReportTemplateFullDeprecatedController@show',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.destroy',
                'uses' => 'ReportTemplateFullDeprecatedController@destroy',
            ]
        );

        /**
         * reportTemplateAccountGroups routes
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.store',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.update',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@update',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.index',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.show',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.index',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@index',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.destroy',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@destroy',
            ]
        );

        /**
         * nativeAccounts
         */
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.show_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@show_native_account_mapping',
            ]
        );

        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.store_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@store_native_account_mapping',
            ]
        );

        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateAccountGroupDeprecatedController.destroy_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupDeprecatedController@destroy_native_account_mapping',
            ]
        );

        /**
         * reportTemplateMappings
         */
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateMappingDeprecatedController.index',
                'uses' => 'ReportTemplateMappingDeprecatedController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateMappingDeprecatedController.show',
                'uses' => 'ReportTemplateMappingDeprecatedController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateMappingDeprecatedController.store',
                'uses' => 'ReportTemplateMappingDeprecatedController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateMappingDeprecatedController.update',
                'uses' => 'ReportTemplateMappingDeprecatedController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateMappingDeprecatedController.destroy',
                'uses' => 'ReportTemplateMappingDeprecatedController@destroy',
            ]
        );
    }
);