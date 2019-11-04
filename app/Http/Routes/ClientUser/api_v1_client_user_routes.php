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
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
    ],
    function ()
    {
        Route::get(
            '/clientDetails/{client_id}',
            [
                'as'   => 'api.v1.ClientDetailController.show',
                'uses' => 'ClientDetailController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/propertyDetails/{property_id_arr}',
            [
                'as'   => 'api.v1.PropertyDetailController.index',
                'uses' => 'PropertyDetailController@index',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/propertyDetails/{property_id}',
            [
                'as'   => 'api.v1.PropertyDetailController.show',
                'uses' => 'PropertyDetailController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.PropertyGroupPublicController.show',
                'uses' => 'PropertyGroupPublicController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/propertyGroup',
            [
                'as'   => 'api.v1.PropertyGroupPublicController.store',
                'uses' => 'PropertyGroupPublicController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.PropertyGroupPublicController.update',
                'uses' => 'PropertyGroupPublicController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.PropertyGroupPublicController.destroy',
                'uses' => 'PropertyGroupPublicController@destroy',
            ]
        );

        Route::get(
            '/clients/{client_id}/propertyGroupProperty/{property_group_property_id}',
            [
                'as'   => 'api.v1.PropertyGroupPropertyController.show',
                'uses' => 'PropertyGroupPropertyController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/propertyGroupProperty',
            [
                'as'   => 'api.v1.PropertyGroupPropertyController.store',
                'uses' => 'PropertyGroupPropertyController@store',
            ]
        );
        Route::delete(
            '/clients/{client_id}/propertyGroupProperty',
            [
                'as'   => 'api.v1.PropertyGroupPropertyController.destroyByComponents',
                'uses' => 'PropertyGroupPropertyController@destroyByComponents',
            ]
        );
        Route::delete(
            '/clients/{client_id}/propertyGroupProperty/{property_group_property_id}',
            [
                'as'   => 'api.v1.PropertyGroupPropertyController.destroy',
                'uses' => 'PropertyGroupPropertyController@destroy',
            ]
        );

        Route::get(
            '/clients/{client_id}/propertiesSummary/{property_id}',
            [
                'as'   => 'api.v1.PropertySummaryController.show',
                'uses' => 'PropertySummaryController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/standardAttributes',
            [
                'as'   => 'api.v1.PropertyDetailController.showStandardAttributes',
                'uses' => 'PropertyDetailController@showStandardAttributes',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/standardAttributeUniqueValues',
            [
                'as'   => 'api.v1.PropertyDetailController.showStandardAttributeUniqueValues',
                'uses' => 'PropertyDetailController@showStandardAttributeUniqueValues',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/customAttributeUniqueValues',
            [
                'as'   => 'api.v1.PropertyDetailController.showCustomAttributeUniqueValues',
                'uses' => 'PropertyDetailController@showCustomAttributeUniqueValues',
            ]
        );

        Route::post(
            '/clients/{client_id}/properties/{property_id}/customAttributes',
            [
                'as'   => 'api.v1.PropertyDetailController.storeCustomAttributes',
                'uses' => 'PropertyDetailController@storeCustomAttributes',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/accessListUsers',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessibleUsersForProperty',
                'uses' => 'AccessListDetailController@getAccessibleUsersForProperty',
            ]
        );

        Route::get(
            '/roles/available',
            [
                'as'   => 'api.v1.RoleDetailController.getAvailable',
                'uses' => 'RoleDetailController@getAvailable',
            ]
        );
        Route::get(
            '/clients/{client_id}/roles',
            [
                'as'   => 'api.v1.RoleDetailController.index',
                'uses' => 'RoleDetailController@index',
            ]
        );

        Route::get(
            '/clients/{client_id}/users/{user_id}/accessListDetail',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessListDetailForUser',
                'uses' => 'AccessListDetailController@getAccessListDetailForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/accessibleProperties',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessiblePropertiesForUser',
                'uses' => 'AccessListDetailController@getAccessiblePropertiesForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/accessibleGroups',
            [
                'as'   => 'api.v1.UserPublicController.showAccessibleGroups',
                'uses' => 'UserPublicController@showAccessibleGroups',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/accessListSummary',
            [
                'as'   => 'api.v1.AccessListSummaryController.getAccessListSummaryForUser',
                'uses' => 'AccessListSummaryController@getAccessListSummaryForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/usersDetail',
            [
                'as'   => 'api.v1.UserPublicController.show',
                'uses' => 'UserPublicController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersDetail/{user_id_arr?}',
            [
                'as'   => 'api.v1.UserPublicController.indexUserDetailForClient',
                'uses' => 'UserPublicController@indexUserDetailForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        // Next three are an alias of the previous three

        Route::get(
            '/clients/{client_id}/userDetails/{user_id}/accessibleGroups',
            [
                'as'   => 'api.v1.UserPublicController.showAccessibleGroups',
                'uses' => 'UserPublicController@showAccessibleGroups',
            ]
        );
        Route::get(
            '/clients/{client_id}/userDetails/{user_id}/accessListSummary',
            [
                'as'   => 'api.v1.AccessListSummaryController.getAccessListSummaryForUser',
                'uses' => 'AccessListSummaryController@getAccessListSummaryForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id_arr}',
            [
                'as'   => 'api.v1.UserPublicController.indexUserForClient',
                'uses' => 'UserPublicController@indexUserForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            '/clients/{client_id}/userDetails/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.show',
                'uses' => 'UserPublicController@show',
            ]
        );
        Route::put(
            '/clients/{client_id}/users/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.update',
                'uses' => 'UserPublicController@update',
            ]
        );
        Route::put(
            '/clients/{client_id}/userDetails/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.update',
                'uses' => 'UserPublicController@update',
            ]
        );

        /**
         * Opportunities
         */
        Route::get(
            '/clients/{client_id}/opportunities',
            [
                'as'   => 'api.v1.OpportunityController.indexForClient',
                'uses' => 'OpportunityController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/opportunities/{opportunity_id_arr}',
            [
                'as'   => 'api.v1.OpportunityController.indexForClient',
                'uses' => 'OpportunityController@indexForClient',
            ]
        )->where(['opportunity_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/properties/{property_id}/opportunities',
            [
                'as'   => 'api.v1.OpportunityController.indexForProperty',
                'uses' => 'OpportunityController@indexForProperty',
            ]
        );
        Route::get(
            '/clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.OpportunityController.show',
                'uses' => 'OpportunityController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/opportunities',
            [
                'as'   => 'api.v1.OpportunityController.store',
                'uses' => 'OpportunityController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.OpportunityController.update',
                'uses' => 'OpportunityController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.OpportunityController.destroy',
                'uses' => 'OpportunityController@destroy',
            ]
        );

        /**
         * propertyDetails
         */
        Route::get(
            '/clients/{client_id}/propertyDetails/{property_id}/users',
            [
                'as'   => 'api.v1.PropertyDetailController.showUsers',
                'uses' => 'PropertyDetailController@showUsers',
            ]
        );

        Route::get(
            '/clients/{client_id}/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.ClientCategoryController.show',
                'uses' => 'ClientCategoryController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/clientCategories',
            [
                'as'   => 'api.v1.ClientCategoryController.index',
                'uses' => 'ClientCategoryController@index',
            ]
        );
        Route::get(
            '/passwordRules',
            [
                'as'   => 'api.v1.PasswordRuleController.index',
                'uses' => 'PasswordRuleController@index',
            ]
        );
        Route::put(
            '/clients/{client_id}/updateNotificationConfig/users/{user_id}',
            [
                'as'   => 'api.v1.UserPublicController.updateNotificationsConfig',
                'uses' => 'UserPublicController@updateNotificationsConfig',
            ]
        );
        Route::put(
            '/clients/{client_id}/updateReportTemplateForUser/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.UserPublicController.updateDefaultReportTemplate',
                'uses' => 'UserPublicController@updateDefaultReportTemplate',
            ]
        );

        /** downloadHistories */
        Route::post(
            'clients/{client_id}/downloadHistories',
            [
                'as'   => 'api.v1.DownloadHistoryController.store',
                'uses' => 'DownloadHistoryController@store',
            ]
        );

        /** accessListDetail */
        Route::get(
            '/clients/{client_id}/accessListDetail',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessListDetailForClient',
                'uses' => 'AccessListDetailController@getAccessListDetailForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListDetail/{access_list_id}',
            [
                'as'   => 'api.v1.AccessListDetailController.show',
                'uses' => 'AccessListDetailController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/accessListDetail',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessListDetailForUser',
                'uses' => 'AccessListDetailController@getAccessListDetailForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/accessListDetails',
            [
                'as'   => 'api.v1.AccessListDetailController.getAccessListDetailForProperty',
                'uses' => 'AccessListDetailController@getAccessListDetailForProperty',
            ]
        );

        /** accessListSlim */
        Route::get(
            '/clients/{client_id}/accessListSlim',
            [
                'as'   => 'api.v1.AccessListSlimController.getAccessListSlimForClient',
                'uses' => 'AccessListSlimController@getAccessListSlimForClient',
            ]
        );
    }
);
