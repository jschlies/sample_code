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
 * a particular can be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here (ie Root routes)
 */

use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . Role::WAYPOINT_ROOT_ROLE,
        ],
        'namespace'  => 'Generated',
    ],
    function ()
    {
        /** advancedVariances */
        Route::resource("advancedVariances", "AdvancedVarianceController", ['except' => ['edit', 'create']]);

        /** advancedVarianceLineItems */
        Route::resource("advancedVarianceLineItems", "AdvancedVarianceLineItemController", ['except' => ['edit', 'create']]);

        /** advancedVarianceApprovals */
        Route::resource("advancedVarianceApprovals", "AdvancedVarianceApprovalController", ['except' => ['edit', 'create']]);

        /** AdvancedVarianceExplanationTypes */
        Route::resource("advancedVarianceExplanationTypes", "AdvancedVarianceExplanationTypeController", ['except' => ['edit', 'create']]);

        /** accessLists */
        Route::resource("accessLists", "AccessListController", ['except' => ['edit', 'create']]);

        /** accessListProperties */
        Route::resource("accessListProperties", "AccessListPropertyController", ['except' => ['edit', 'create']]);

        /** accessListUsers */
        Route::resource("accessListUsers", "AccessListUserController", ['except' => ['edit', 'create']]);

        /** advancedVarianceThresholds */
        Route::resource("advancedVarianceThresholds", "AdvancedVarianceThresholdController", ['except' => ['edit', 'create']]);

        /** apiKeys */
        Route::resource("apiKeys", "ApiKeyController", ['except' => ['edit', 'create']]);

        /** apiLogs */
        Route::resource("apiLogs", "ApiLogController", ['except' => ['edit', 'create']]);

        /** apiLogs */
        Route::resource("auxCoaLineGroups", "ReportTemplateController", ['except' => ['edit', 'create']]);

        /** assetTypes */
        Route::resource("assetTypes", "AssetTypeController", ['except' => ['edit', 'create']]);

        /** authenticatingEntities */
        Route::resource("authenticatingEntities", "AuthenticatingEntityController", ['except' => ['edit', 'create']]);

        /** reportTemplateAccountGroups */
        Route::resource("reportTemplateAccountGroups", "ReportTemplateAccountGroupController", ['except' => ['edit', 'create']]);

        /** clientCategories */
        Route::resource("clientCategories", "ClientCategoryController", ['except' => ['edit', 'create']]);

        /** reportTemplates */
        Route::resource("reportTemplates", "ReportTemplateController", ['except' => ['edit', 'create']]);

        /** nativeCoas */
        Route::resource("nativeCoas", "NativeCoaController", ['except' => ['edit', 'create']]);

        /** nativeAccounts */
        Route::resource("nativeAccounts", "NativeAccountController", ['except' => ['edit', 'create']]);

        /** calculatedFields */
        Route::resource("calculatedFields", "CalculatedFieldController", ['except' => ['edit', 'create']]);
        /** calculatedFieldEquations */

        Route::resource("calculatedFieldEquations", "CalculatedFieldEquationController", ['except' => ['edit', 'create']]);

        /** calculatedFieldEquations */
        Route::resource("calculatedFieldEquationProperties", "CalculatedFieldEquationPropertyController", ['except' => ['edit', 'create']]);

        /** calculatedFieldEquations */
        Route::resource("calculatedFieldEquationPropertie", "CalculatedFieldEquationPropertyController", ['except' => ['edit', 'create']]);

        /** calculatedFieldEquations */
        Route::resource("calculatedFieldVariables", "CalculatedFieldVariableController", ['except' => ['edit', 'create']]);

        /** clients */
        Route::resource("clients", "ClientController", ['except' => ['edit', 'create']]);

        /** custom reports */
        Route::resource("customReportTypes", "CustomReportTypeController", ['except' => ['edit', 'create']]);

        /** customReports */
        Route::resource("customReports", "CustomReportController", ['except' => ['edit', 'create']]);

        /** downloadHistories */
        Route::resource("downloadHistories", "DownloadHistoryController", ['except' => ['edit', 'create']]);

        /** ecmProjects */
        Route::resource("ecmProjects", "EcmProjectController", ['except' => ['edit', 'create']]);

        /** emailHistories */
        Route::resource("notificationLogs", "NotificationLogController", ['except' => ['edit', 'create']]);

        /** entityTagEntities */
        Route::resource("entityTagEntities", "EntityTagEntityController", ['except' => ['edit', 'create']]);

        /** entityTags */
        Route::resource("entityTags", "EntityTagController", ['except' => ['edit', 'create']]);

        /** failedJobs */
        Route::resource("failedJobs", "FailedJobController", ['except' => ['edit', 'create']]);

        /** reportTemplateMappings */
        Route::resource("reportTemplateMappings", "ReportTemplateMappingController", ['except' => ['edit', 'create']]);

        /** nativeCoas */
        Route::resource("nativeCoas", "NativeCoaController", ['except' => ['edit', 'create']]);

        /** nativeAccountAmounts */
        Route::resource("nativeAccountAmounts", "NativeAccountAmountController", ['except' => ['edit', 'create']]);

        /** nativeAccounts */
        Route::resource("nativeAccounts", "NativeAccountController", ['except' => ['edit', 'create']]);

        /** nativeAccounts */
        Route::resource("nativeAccountTypes", "NativeAccountTypeController", ['except' => ['edit', 'create']]);

        /** nativeAccounts */
        Route::resource("nativeAccountTypeTrailers", "NativeAccountTypeTrailerController", ['except' => ['edit', 'create']]);

        /** utilityAccounts */
        Route::resource("opportunities", "OpportunityController", ['except' => ['edit', 'create']]);

        /** passwordRules */
        Route::resource("passwordRules", "PasswordRuleController", ['except' => ['edit', 'create']]);

        /** utilityAccounts */
        Route::resource("permissionRoles", "PermissionRoleController", ['except' => ['edit', 'create']]);

        /** permissions */
        Route::resource("permissions", "PermissionController", ['except' => ['edit', 'create']]);

        /** properties */
        Route::resource("properties", "PropertyController", ['except' => ['edit', 'create']]);

        /** propertyGroups */
        Route::resource("propertyGroups", "PropertyGroupController", ['except' => ['edit', 'create']]);

        /** propertyGroupProperties */
        Route::resource("propertyGroupProperties", "PropertyGroupPropertyController", ['except' => ['edit', 'create']]);

        /** propertyNativeCoas */
        Route::resource("propertyNativeCoas", "PropertyNativeCoaController", ['except' => ['edit', 'create']]);

        /** roles */
        Route::resource("relatedUsers", "RelatedUserController", ['except' => ['edit', 'create']]);

        /** roles */
        Route::resource("relatedUserTypes", "RelatedUserTypeController", ['except' => ['edit', 'create']]);

        /** roles */
        Route::resource("roles", "RoleController", ['except' => ['edit', 'create']]);

        /** roleUsers */
        Route::resource("roleUsers", "RoleUserController", ['except' => ['edit', 'create']]);

        /** users */
        Route::resource("users", "UserController", ['except' => ['edit', 'create', 'patch']]);

        /** users */
        Route::resource("userInvitations", "UserInvitationController", ['except' => ['edit', 'create', 'patch']]);

        /** suites */
        Route::resource("suites", "SuiteController", ['except' => ['edit', 'create', 'patch']]);

        /** tenantIndustries */
        Route::resource("tenantIndustries", "TenantIndustryController", ['except' => ['edit', 'create', 'patch']]);

        /** tenantAttributes */
        Route::resource("tenantAttributes", "TenantAttributeController", ['except' => ['edit', 'create', 'patch']]);

        /** tenantTenantAttributes */
        Route::resource("tenantTenantAttributes", "TenantTenantAttributeController", ['except' => ['edit', 'create', 'patch']]);

        /** tenants */
        Route::resource("tenants", "TenantController", ['except' => ['edit', 'create', 'patch']]);

        /** leases */
        Route::resource("leases", "LeaseController", ['except' => ['edit', 'create', 'patch']]);

        /** leases */
        Route::resource("suiteTenants", "SuiteTenantController", ['except' => ['edit', 'create', 'patch']]);

        /** leases */
        Route::resource("leaseTenants", "LeaseTenantController", ['except' => ['edit', 'create', 'patch']]);

        /** lease_schedules */
        Route::resource("leaseSchedules", "LeaseScheduleController", ['except' => ['edit', 'create', 'patch']]);

        /** suite_leases */
        Route::resource("suiteLeases", "SuiteLeaseController", ['except' => ['edit', 'create', 'patch']]);
    }
);
Route::group(
    [
        'middleware' => [
            'role:' . Role::WAYPOINT_ROOT_ROLE,
        ],
    ],
    function ()
    {
        /** favorites */
        Route::resource("favorites", "FavoriteController", ['except' => ['edit', 'create', 'update']]);

        /** favoriteGroups */
        Route::resource("favoriteGroups", "FavoriteGroupController", ['except' => ['edit', 'create', 'store', 'show', 'update', 'destroy']]);

        /** accessListsSummary */
        Route::get(
            '/clients/{client_id}/accessListsSummary',
            [
                'as'   => 'api.v1.AccessListSummaryController.index',
                'uses' => 'AccessListSummaryController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListsSummary/{access_list_id}',
            [
                'as'   => 'api.v1.AccessListSummaryController.show',
                'uses' => 'AccessListSummaryController@show',
            ]
        );

        /** accessListsFull */
        Route::get(
            '/clients/{client_id}/accessListsFull',
            [
                'as'   => 'api.v1.AccessListFullController.index',
                'uses' => 'AccessListFullController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListsFull/{access_list_id}',
            [
                'as'   => 'api.v1.AccessListFullController.show',
                'uses' => 'AccessListFullController@show',
            ]
        );
        Route::get(
            '/clientsFull/{client_id}',
            [
                'as'   => 'api.v1.ClientFullController.show',
                'uses' => 'ClientFullController@show',
            ]
        );

        /** propertiesDetail */
        Route::get(
            '/clients/{client_id}/propertyDetails',
            [
                'as'   => 'api.v1.PropertyDetailController.index',
                'uses' => 'PropertyDetailController@index',
            ]
        );

        /** propertyGroupsDetail */
        Route::get(
            '/clients/{client_id}/propertyGroupsDetail',
            [
                'as'   => 'api.v1.PropertyGroupDetailController.Summary',
                'uses' => 'PropertyGroupDetailController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyGroupsDetail/{property_group_id}',
            [
                'as'   => 'api.v1.PropertyGroupDetailController.show',
                'uses' => 'PropertyGroupDetailController@show',
            ]
        );

        /** users */
        Route::post(
            '/clients/{client_id}/users/deactivate',
            [
                'as'   => 'api.v1.UserSummaryController.deactivateUsers',
                'uses' => 'UserSummaryController@deactivateUsers',
            ]
        );
        /** usersSummary */
        Route::get(
            '/clients/{client_id}/usersSummary',
            [
                'as'   => 'api.v1.UserSummaryController.Summary',
                'uses' => 'UserSummaryController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/usersSummary/{user_id}',
            [
                'as'   => 'api.v1.UserSummaryController.show',
                'uses' => 'UserSummaryController@show',
            ]
        );

        Route::post(
            '/clients/{client_id}/reportTemplates',
            [
                'as'   => 'api.v1.ReportTemplateController.store',
                'uses' => 'ReportTemplateController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}',
            [
                'as'   => 'api.v1.ReportTemplateController.update',
                'uses' => 'ReportTemplateController@update',
            ]
        );
        Route::put(
            '/clients/{client_id}/users/{user_id}/is_hidden',
            [
                'as'   => 'api.v1.UserPublicController.update_is_hidden',
                'uses' => 'UserPublicController@update_is_hidden',
            ]
        );
    }
);