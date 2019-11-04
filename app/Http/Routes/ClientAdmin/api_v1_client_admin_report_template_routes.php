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
    ],
    function ()
    {
        /**
         * reportTemplates routes
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/generateAccountTypeBasedReportTemplate',
            [
                'as'   => 'api.v1.ReportTemplateFullController.generateAccountTypeBasedReportTemplate',
                'uses' => 'ReportTemplateFullController@generateAccountTypeBasedReportTemplate',
            ]
        );
        Route::post(
            '/clients/{client_id}/reportTemplates/generateBomaBasedReportTemplate',
            [
                'as'   => 'api.v1.ReportTemplateFullController.generateBomaBasedReportTemplate',
                'uses' => 'ReportTemplateFullController@generateBomaBasedReportTemplate',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates',
            [
                'as'   => 'api.v1.ReportTemplateController.showForClient',
                'uses' => 'ReportTemplateController@showForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.ReportTemplateFullController.show',
                'uses' => 'ReportTemplateFullController@show',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.ReportTemplateFullController.destroy',
                'uses' => 'ReportTemplateFullController@destroy',
            ]
        );

        /**
         * reportTemplateAccountGroups routes
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.store',
                'uses' => 'ReportTemplateAccountGroupController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.update',
                'uses' => 'ReportTemplateAccountGroupController@update',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.index',
                'uses' => 'ReportTemplateAccountGroupController@index',
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
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.index',
                'uses' => 'ReportTemplateAccountGroupController@index',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.destroy',
                'uses' => 'ReportTemplateAccountGroupController@destroy',
            ]
        );

        /**
         * nativeAccounts
         */
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.show_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupController@show_native_account_mapping',
            ]
        );

        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.store_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupController@store_native_account_mapping',
            ]
        );

        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.ReportTemplateAccountGroupController.destroy_native_account_mapping',
                'uses' => 'ReportTemplateAccountGroupController@destroy_native_account_mapping',
            ]
        );

        /**
         * reportTemplateMappings
         */
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings',
            [
                'as'   => 'api.v1.ReportTemplateMappingController.index',
                'uses' => 'ReportTemplateMappingController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.ReportTemplateMappingController.show',
                'uses' => 'ReportTemplateMappingController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings',
            [
                'as'   => 'api.v1.ReportTemplateMappingController.store',
                'uses' => 'ReportTemplateMappingController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.ReportTemplateMappingController.update',
                'uses' => 'ReportTemplateMappingController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/reportTemplateAccountGroups/{report_template_account_group_id}/reportTemplateMappings/{report_template_mapping_id}',
            [
                'as'   => 'api.v1.ReportTemplateMappingController.destroy',
                'uses' => 'ReportTemplateMappingController@destroy',
            ]
        );
    }
);