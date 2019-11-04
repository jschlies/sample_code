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

use App\Waypoint\Http\ApiController;
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
            '/clients/{client_id}/accessListSummary/{access_list_id}',
            [
                'as'   => 'api.v1.AccessListSummaryController.show',
                'uses' => 'AccessListSummaryController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/accessList',
            [
                'as'   => 'api.v1.AccessListDetailController.store',
                'uses' => 'AccessListDetailController@store',
            ]
        );

        Route::delete(
            '/clients/{client_id}/accessList/{access_list_id}',
            [
                'as'   => 'api.v1.AccessListDetailController.destroy',
                'uses' => 'AccessListDetailController@destroy',
            ]
        );

        /**
         * accessListProperty
         */
        Route::get(
            '/clients/{client_id}/accessList/{access_list_id}/accessListProperty',
            [
                'as'   => 'api.v1.AccessListPropertyPublicController.index',
                'uses' => 'AccessListPropertyPublicController@index',
            ]
        );

        Route::get(
            '/clients/{client_id}/accessList/{access_list_id}/accessListProperty/{access_list_property_id}',
            [
                'as'   => 'api.v1.AccessListPropertyPublicController.show',
                'uses' => 'AccessListPropertyPublicController@show',
            ]
        );

        Route::delete(
            '/clients/{client_id}/accessList/{access_list_id}/accessListProperty/{access_list_property_id}',
            [
                'as'   => 'api.v1.AccessListPropertyPublicController.destroy',
                'uses' => 'AccessListPropertyPublicController@destroy',
            ]
        );

        Route::post(
            '/clients/{client_id}/accessList/{access_list_id}/accessListProperty',
            [
                'as'   => 'api.v1.AccessListPropertyPublicController.store',
                'uses' => 'AccessListPropertyPublicController@store',
            ]
        );

        /**
         * accessListUser
         */
        Route::get(
            '/clients/{client_id}/accessList/{access_list_id}/accessListUser',
            [
                'as'   => 'api.v1.AccessListUserPublicController.index',
                'uses' => 'AccessListUserPublicController@index',
            ]
        );

        Route::get(
            '/clients/{client_id}/accessList/{access_list_id}/accessListUser/{access_list_user_id}',
            [
                'as'   => 'api.v1.AccessListUserPublicController.show',
                'uses' => 'AccessListUserPublicController@show',
            ]
        );

        Route::delete(
            '/clients/{client_id}/accessList/{access_list_id}/accessListUser/{access_list_user_id}',
            [
                'as'   => 'api.v1.AccessListUserPublicController.destroy',
                'uses' => 'AccessListUserPublicController@destroy',
            ]
        );

        Route::delete(
            '/clients/{client_id}/accessLists/{access_list_id_arr}/users/{user_id}/multi',
            [
                'as'   => 'api.v1.AccessListUserPublicController.destroyByUser',
                'uses' => 'AccessListUserPublicController@destroyByUser',
            ]
        )->where(['access_list_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::post(
            '/clients/{client_id}/accessList/{access_list_id}/accessListUser',
            [
                'as'   => 'api.v1.AccessListUserPublicController.store',
                'uses' => 'AccessListUserPublicController@store',
            ]
        )->where(['access_list_id' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            '/clients/{client_id}/accessLists',
            [
                'as'   => 'api.v1.AccessListDetailController.indexForClient',
                'uses' => 'AccessListDetailController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListsPerUser',
            [
                'as'   => 'api.v1.AccessListTrimmedSummaryController.getAccessListsPerUserForGivenClient',
                'uses' => 'AccessListTrimmedSummaryController@getAccessListsPerUserForGivenClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyGroups',
            [
                'as'   => 'api.v1.PropertyGroupDetailController.indexForClient',
                'uses' => 'PropertyGroupDetailController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertiesSummary',
            [
                'as'   => 'api.v1.PropertySummaryController.indexForClient',
                'uses' => 'PropertySummaryController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties',
            [
                'as'   => 'api.v1.PropertyPublicController.index',
                'uses' => 'PropertyPublicController@index',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}',
            [
                'as'   => 'api.v1.PropertyPublicController.update',
                'uses' => 'PropertyPublicController@update',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties',
            [
                'as'   => 'api.v1.PropertyPublicController.store',
                'uses' => 'PropertyPublicController@store',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}',
            [
                'as'   => 'api.v1.PropertyPublicController.destroy',
                'uses' => 'PropertyPublicController@destroy',
            ]
        );
        Route::delete(
            '/clients/{client_id}/property_group_calc/{property_group_calc_value}',
            [
                'as'   => 'api.v1.ClientPublicController.set_property_group_calc',
                'uses' => 'ClientPublicController@set_property_group_calc',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyDetails',
            [
                'as'   => 'api.v1.PropertyDetailController.index',
                'uses' => 'PropertyDetailController@index',
            ]
        );

        Route::post(
            '/clients/{client_id}/clientCategories',
            [
                'as'   => 'api.v1.ClientCategoryController.store',
                'uses' => 'ClientCategoryController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.ClientCategoryController.update',
                'uses' => 'ClientCategoryController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.ClientCategoryController.destroy',
                'uses' => 'ClientCategoryController@destroy',
            ]
        );
        Route::post(
            '/clients/{client_id}/users/{user_id}/role/{role_name}/addRole',
            [
                'as'   => 'api.v1.UserPublicController.addRoleToUser',
                'uses' => 'UserPublicController@addRoleToUser',
            ]
        );
        Route::delete(
            '/clients/{client_id}/users/{user_id}/role/{role_name}/deleteRole',
            [
                'as'   => 'api.v1.UserPublicController.destroyRoleToUser',
                'uses' => 'UserPublicController@destroyRoleToUser',
            ]
        );

        Route::get(
            '/clients/{client_id}/renderMappingsPerClient',
            [
                'as'   => 'api.v1.NativeAccountController.renderMappingsPerClient',
                'uses' => 'NativeAccountController@renderMappingsPerClient',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/renderMappingsPerClientProperty',
            [
                'as'   => 'api.v1.NativeAccountController.renderMappingsPerClientProperty',
                'uses' => 'NativeAccountController@renderMappingsPerClientProperty',
            ]
        );

        Route::get(
            '/clients/{client_id}/report_templates/render',
            [
                'as'   => 'api.v1.ReportTemplateFullController.renderReportTemplatesForClient',
                'uses' => 'ReportTemplateFullController@renderReportTemplatesForClient',
            ]
        );

        Route::get(
            '/clients/{client_id}/report_templates/{report_template_id}/render',
            [
                'as'   => 'api.v1.ReportTemplateFullController.renderReportTemplate',
                'uses' => 'ReportTemplateFullController@renderReportTemplate',
            ]
        );

        /** assetTypes */
        Route::get(
            '/clients/{client_id}/assetTypes',
            [
                'as'   => 'api.v1.AssetTypeController.index',
                'uses' => 'AssetTypeController@index',
            ]
        );

        Route::post(
            '/clients/{client_id}/assetTypes',
            [
                'as'   => 'api.v1.AssetTypeController.store',
                'uses' => 'AssetTypeController@store',
            ]
        );

        Route::get(
            '/clients/{client_id}/assetTypes/{asset_type_id}',
            [
                'as'   => 'api.v1.AssetTypeController.show',
                'uses' => 'AssetTypeController@show',
            ]
        );

        Route::delete(
            '/clients/{client_id}/assetTypes/{asset_type_id}',
            [
                'as'   => 'api.v1.AssetTypeController.destroy',
                'uses' => 'AssetTypeController@destroy',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/leases/refresh',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailController.refresh_leases_for_property',
                'uses' => 'LeaseDetailController@refresh_leases_for_property',
            ]
        );

        Route::get(
            '/clients/{client_id}/renderPreCalcStatusClient',
            [
                'as'   => 'api.v1.ClientDetailController.renderPreCalcStatusClient',
                'uses' => 'ClientDetailController@renderPreCalcStatusClient',
            ]
        );
        /** SystemInfo */
        Route::get(
            'systemInfo',
            [
                'as'   => 'api.v1.SystemInfoController.index',
                'uses' => 'SystemInfoController@index',
            ]
        );
    }
);
