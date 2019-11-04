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
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        Route::get(
            'clientDetails/{client_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ClientDetailDeprecatedController.show',
                'uses' => 'ClientDetailDeprecatedController@show',
            ]
        );

        Route::get(
            'clients/{client_id}/propertyDetails/{property_id_arr}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.index',
                'uses' => 'PropertyDetailDeprecatedController@index',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            'propertyDetails/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.show',
                'uses' => 'PropertyDetailDeprecatedController@show',
            ]
        );
        Route::get(
            'propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPublicDeprecatedController.show',
                'uses' => 'PropertyGroupPublicDeprecatedController@show',
            ]
        );
        Route::post(
            'propertyGroup',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPublicDeprecatedController.store',
                'uses' => 'PropertyGroupPublicDeprecatedController@store',
            ]
        );
        Route::put(
            'propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPublicDeprecatedController.update',
                'uses' => 'PropertyGroupPublicDeprecatedController@update',
            ]
        );
        Route::delete(
            'propertyGroup/{property_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPublicDeprecatedController.destroy',
                'uses' => 'PropertyGroupPublicDeprecatedController@destroy',
            ]
        );

        Route::get(
            'propertyGroupProperty/{property_group_property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPropertyDeprecatedController.show',
                'uses' => 'PropertyGroupPropertyDeprecatedController@show',
            ]
        );
        Route::post(
            'propertyGroupProperty',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPropertyDeprecatedController.store',
                'uses' => 'PropertyGroupPropertyDeprecatedController@store',
            ]
        );
        Route::delete(
            'propertyGroupProperty',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPropertyDeprecatedController.destroyByComponents',
                'uses' => 'PropertyGroupPropertyDeprecatedController@destroyByComponents',
            ]
        );
        Route::delete(
            'propertyGroupProperty/{property_group_property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupPropertyDeprecatedController.destroy',
                'uses' => 'PropertyGroupPropertyDeprecatedController@destroy',
            ]
        );

        Route::get(
            'propertiesSummary/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertySummaryDeprecatedController.show',
                'uses' => 'PropertySummaryDeprecatedController@show',
            ]
        );

        Route::get(
            'properties/standardAttributes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.showStandardAttributes',
                'uses' => 'PropertyDetailDeprecatedController@showStandardAttributes',
            ]
        );

        Route::get(
            'clients/{client_id}/properties/standardAttributeUniqueValues',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.showStandardAttributeUniqueValues',
                'uses' => 'PropertyDetailDeprecatedController@showStandardAttributeUniqueValues',
            ]
        );

        Route::get(
            'clients/{client_id}/properties/customAttributeUniqueValues',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.showCustomAttributeUniqueValues',
                'uses' => 'PropertyDetailDeprecatedController@showCustomAttributeUniqueValues',
            ]
        );

        Route::post(
            'clients/{client_id}/properties/{property_id}/customAttributes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.storeCustomAttributes',
                'uses' => 'PropertyDetailDeprecatedController@storeCustomAttributes',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/accessListUsers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListDetailDeprecatedController.getAccessibleUsersForProperty',
                'uses' => 'AccessListDetailDeprecatedController@getAccessibleUsersForProperty',
            ]
        );

        Route::get(
            'roles/available/',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RoleDetailDeprecatedController.getAvailable',
                'uses' => 'RoleDetailDeprecatedController@getAvailable',
            ]
        );
        Route::get(
            'roles/',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RoleDetailDeprecatedController.index',
                'uses' => 'RoleDetailDeprecatedController@index',
            ]
        );

        Route::get(
            'users/{user_id}/accessListDetail',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListDetailDeprecatedController.getAccessListDetailForUser',
                'uses' => 'AccessListDetailDeprecatedController@getAccessListDetailForUser',
            ]
        );
        Route::get(
            'users/{user_id}/accessibleProperties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListDetailDeprecatedController.getAccessiblePropertiesForUser',
                'uses' => 'AccessListDetailDeprecatedController@getAccessiblePropertiesForUser',
            ]
        );
        Route::get(
            '/users/{user_id}/accessibleGroups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.showAccessibleGroups',
                'uses' => 'UserPublicDeprecatedController@showAccessibleGroups',
            ]
        );
        Route::get(
            'users/{user_id}/accessListSummary',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListSummaryDeprecatedController.getAccessListSummaryForUser',
                'uses' => 'AccessListSummaryDeprecatedController@getAccessListSummaryForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersDetail/{user_id_arr?}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.indexUserDetailForClient',
                'uses' => 'UserPublicDeprecatedController@indexUserDetailForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        // Next three are an alias of the previous three

        Route::get(
            '/userDetails/{user_id}/accessibleGroups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.showAccessibleGroups',
                'uses' => 'UserPublicDeprecatedController@showAccessibleGroups',
            ]
        );
        Route::get(
            'userDetails/{user_id}/accessListSummary',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListSummaryDeprecatedController.getAccessListSummaryForUser',
                'uses' => 'AccessListSummaryDeprecatedController@getAccessListSummaryForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id_arr?}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.indexUserForClient',
                'uses' => 'UserPublicDeprecatedController@indexUserForClient',
            ]
        )->where(['user_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            'users/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.show',
                'uses' => 'UserPublicDeprecatedController@show',
            ]
        );
        Route::get(
            'userDetails/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.show',
                'uses' => 'UserPublicDeprecatedController@show',
            ]
        );
        Route::put(
            'users/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.update',
                'uses' => 'UserPublicDeprecatedController@update',
            ]
        );
        Route::put(
            'userDetails/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.update',
                'uses' => 'UserPublicDeprecatedController@update',
            ]
        );
        Route::get(
            'clients/{client_id}/reportTemplatesFull/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ReportTemplateFullDeprecatedController.show',
                'uses' => 'ReportTemplateFullDeprecatedController@show',
            ]
        );

        /**
         * Opportunities
         */
        Route::get(
            'clients/{client_id}/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.indexForClient',
                'uses' => 'OpportunityDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            'clients/{client_id}/opportunities/{opportunity_id_arr}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.indexForClient',
                'uses' => 'OpportunityDeprecatedController@indexForClient',
            ]
        )->where(['opportunity_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            'clients/{client_id}/properties/{property_id}/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.indexForProperty',
                'uses' => 'OpportunityDeprecatedController@indexForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.show',
                'uses' => 'OpportunityDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.store',
                'uses' => 'OpportunityDeprecatedController@store',
            ]
        );
        Route::put(
            'clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.update',
                'uses' => 'OpportunityDeprecatedController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/opportunities/{opportunity_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.destroy',
                'uses' => 'OpportunityDeprecatedController@destroy',
            ]
        );

        /**
         * propertyDetails
         */
        Route::get(
            'propertyDetails/{property_id}/users',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.showUsers',
                'uses' => 'PropertyDetailDeprecatedController@showUsers',
            ]
        );

        Route::get(
            '/clientCategories/{client_category_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ClientCategoryDeprecatedController.show',
                'uses' => 'ClientCategoryDeprecatedController@show',
            ]
        );
        Route::get(
            '/clientCategories',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ClientCategoryDeprecatedController.index',
                'uses' => 'ClientCategoryDeprecatedController@index',
            ]
        );
        Route::get(
            'passwordRules',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PasswordRuleDeprecatedController.index',
                'uses' => 'PasswordRuleDeprecatedController@index',
            ]
        );
        Route::put(
            '/updateNotificationConfig/users/{user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.updateNotificationsConfig',
                'uses' => 'UserPublicDeprecatedController@updateNotificationsConfig',
            ]
        );
        Route::put(
            'updateReportTemplateForUser/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.UserPublicDeprecatedController.updateDefaultReportTemplate',
                'uses' => 'UserPublicDeprecatedController@updateDefaultReportTemplate',
            ]
        );

        /**
         * Download Histories
         */
        /** downloadHistories */
        Route::post(
            'clients/{client_id}/downloadHistories',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.DownloadHistoryDeprecatedController.store',
                'uses' => 'DownloadHistoryDeprecatedController@store',
            ]
        );

        /** accessListDetail */
        Route::get(
            'clients/{client_id}/accessListDetail',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.getAccessListDetailForClient',
                'uses' => 'AccessListDetailDeprecatedController@getAccessListDetailForClient',
            ]
        );

        Route::get(
            'accessListDetail/{id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AccessListDetailDeprecatedController.show',
                'uses' => 'AccessListDetailDeprecatedController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/accessListDetail',
            [
                'as'   => 'api.v1.AccessListDetailDeprecatedController.getAccessListDetailForUser',
                'uses' => 'AccessListDetailDeprecatedController@getAccessListDetailForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/accessListDetails',
            [
                'as'   => 'api.v1.AccessListDetailDeprecatedController.getAccessListDetailForProperty',
                'uses' => 'AccessListDetailDeprecatedController@getAccessListDetailForProperty',
            ]
        );
    }
);
