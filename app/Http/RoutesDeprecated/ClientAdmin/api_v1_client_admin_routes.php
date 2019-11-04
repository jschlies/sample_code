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

use App\Waypoint\Http\ApiController;
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
            'accessListSummary/{id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListSummaryDeprecatedController.show',
                'uses' => 'AccessListSummaryDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/accessList',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.store',
                'uses' => 'AccessListDetailDeprecatedController@store',
            ]
        );

        Route::delete(
            'clients/{client_id}/accessList/{access_list_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.destroy',
                'uses' => 'AccessListDetailDeprecatedController@destroy',
            ]
        );

        /**
         * accessListProperty
         */
        Route::get(
            'clients/{client_id}/accessList/{access_list_id}/accessListProperty',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListPropertyPublicDeprecatedController.index',
                'uses' => 'AccessListPropertyPublicDeprecatedController@index',
            ]
        );

        Route::get(
            'clients/{client_id}/accessList/{access_list_id}/accessListProperty/{access_list_property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListPropertyPublicDeprecatedController.show',
                'uses' => 'AccessListPropertyPublicDeprecatedController@show',
            ]
        );

        Route::delete(
            'clients/{client_id}/accessList/{access_list_id}/accessListProperty/{access_list_property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListPropertyPublicDeprecatedController.destroy',
                'uses' => 'AccessListPropertyPublicDeprecatedController@destroy',
            ]
        );

        Route::post(
            'clients/{client_id}/accessList/{access_list_id}/accessListProperty',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListPropertyPublicDeprecatedController.store',
                'uses' => 'AccessListPropertyPublicDeprecatedController@store',
            ]
        );

        /**
         * accessListUser
         */
        Route::get(
            'clients/{client_id}/accessList/{access_list_id}/accessListUser',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListUserPublicDeprecatedController.index',
                'uses' => 'AccessListUserPublicDeprecatedController@index',
            ]
        );

        Route::get(
            'clients/{client_id}/accessList/{access_list_id}/accessListUser/{access_list_user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListUserPublicDeprecatedController.show',
                'uses' => 'AccessListUserPublicDeprecatedController@show',
            ]
        );

        Route::delete(
            'clients/{client_id}/accessList/{access_list_id}/accessListUser/{access_list_user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListUserPublicDeprecatedController.destroy',
                'uses' => 'AccessListUserPublicDeprecatedController@destroy',
            ]
        );

        Route::post(
            'clients/{client_id}/accessList/{access_list_id}/accessListUser',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListUserPublicDeprecatedController.store',
                'uses' => 'AccessListUserPublicDeprecatedController@store',
            ]
        )->where(['access_list_id' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            'clients/{client_id}/accessLists',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.indexForClient',
                'uses' => 'AccessListDetailDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            'clients/{client_id}/accessListsPerUser',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListTrimmedSummaryDeprecatedController.getAccessListsPerUserForGivenClient',
                'uses' => 'AccessListTrimmedSummaryDeprecatedController@getAccessListsPerUserForGivenClient',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyGroupDetailDeprecatedController.indexForClient',
                'uses' => 'PropertyGroupDetailDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            'clients/{client_id}/propertiesSummary',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertySummaryDeprecatedController.indexForClient',
                'uses' => 'PropertySummaryDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            'clients/{client_id}/properties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyPublicDeprecatedController.index',
                'uses' => 'PropertyPublicDeprecatedController@index',
            ]
        );
        Route::put(
            'properties/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyPublicDeprecatedController.update',
                'uses' => 'PropertyPublicDeprecatedController@update',
            ]
        );
        Route::post(
            'clients/{client_id}/properties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyPublicDeprecatedController.store',
                'uses' => 'PropertyPublicDeprecatedController@store',
            ]
        );
        Route::delete(
            'properties/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyPublicDeprecatedController.destroy',
                'uses' => 'PropertyPublicDeprecatedController@destroy',
            ]
        );
        Route::delete(
            'clients/{client_id}/property_group_calc/{property_group_calc_value}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ClientPublicDeprecatedController.set_property_group_calc',
                'uses' => 'ClientPublicDeprecatedController@set_property_group_calc',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyDetailDeprecatedController.index',
                'uses' => 'PropertyDetailDeprecatedController@index',
            ]
        );

        Route::post(
            '/clientCategories',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ClientCategoryDeprecatedController.store',
                'uses' => 'ClientCategoryDeprecatedController@store',
            ]
        );
        Route::put(
            '/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ClientCategoryDeprecatedController.update',
                'uses' => 'ClientCategoryDeprecatedController@update',
            ]
        );
        Route::delete(
            '/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ClientCategoryDeprecatedController.destroy',
                'uses' => 'ClientCategoryDeprecatedController@destroy',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/accessListDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.getAccessListDetailForProperty',
                'uses' => 'AccessListDetailDeprecatedController@getAccessListDetailForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/ecmProjects',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForClient',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForClient',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::post(
            'clients/{client_id}/users/{user_id}/role/{role_name}/addRole',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.addRoleToUser',
                'uses' => 'UserPublicDeprecatedController@addRoleToUser',
            ]
        );
        Route::delete(
            'clients/{client_id}/users/{user_id}/role/{role_name}/deleteRole',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.UserPublicDeprecatedController.destroyRoleToUser',
                'uses' => 'UserPublicDeprecatedController@destroyRoleToUser',
            ]
        );

        Route::get(
            '/clients/{client_id}/renderMappingsPerClient',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.renderMappingsPerClient',
                'uses' => 'NativeAccountDeprecatedController@renderMappingsPerClient',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/renderMappingsPerClientProperty',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.renderMappingsPerClientProperty',
                'uses' => 'NativeAccountDeprecatedController@renderMappingsPerClientProperty',
            ]
        );

        Route::get(
            '/clients/{client_id}/report_templates/render',
            [
                'as'   => 'api.v1' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.renderReportTemplatesForClient',
                'uses' => 'ReportTemplateFullDeprecatedController@renderReportTemplatesForClient',
            ]
        );

        Route::get(
            '/clients/{client_id}/report_templates/{report_template_id}/render',
            [
                'as'   => 'api.v1' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.ReportTemplateFullDeprecatedController.renderReportTemplate',
                'uses' => 'ReportTemplateFullDeprecatedController@renderReportTemplate',
            ]
        );

        /** assetTypes */
        Route::get(
            '/clients/{client_id}/assetTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AssetTypeDeprecatedController.index',
                'uses' => 'AssetTypeDeprecatedController@index',
            ]
        );

        Route::post(
            'clients/{client_id}/assetTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AssetTypeDeprecatedController.store',
                'uses' => 'AssetTypeDeprecatedController@store',
            ]
        );

        Route::get(
            '/clients/{client_id}/assetTypes/{asset_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AssetTypeDeprecatedController.show',
                'uses' => 'AssetTypeDeprecatedController@show',
            ]
        );

        Route::delete(
            'clients/{client_id}/assetTypes/{asset_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AssetTypeDeprecatedController.destroy',
                'uses' => 'AssetTypeDeprecatedController@destroy',
            ]
        );

        Route::get(
            'clients/{client_id}/properties/{property_id}/leases/refresh',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.refresh_leases_for_property',
                'uses' => 'LeaseDetailDeprecatedController@refresh_leases_for_property',
            ]
        );
    }
);
