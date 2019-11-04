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
            '/clients/{client_id}/properties/{property_id}/leaseDetails/{lease_id}',
            [
                'as'   => 'api.v1.LeaseDetailController.show',
                'uses' => 'LeaseDetailController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/suiteDetails/{suite_id}',
            [
                'as'   => 'api.v1.SuiteController.show',
                'uses' => 'SuiteController@show',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/leaseDetails',
            [
                'as'   => 'api.v1.LeaseDetailController.get_leases_for_property',
                'uses' => 'LeaseDetailController@get_leases_for_property',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/leaseDetails/active',
            [
                'as'   => 'api.v1.LeaseDetailController.get_active_leases_for_property',
                'uses' => 'LeaseDetailController@get_active_leases_for_property',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/suiteDetails',
            [
                'as'   => 'api.v1.SuiteController.get_suites_for_property',
                'uses' => 'SuiteController@get_suites_for_property',
            ]
        );

        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/leaseDetails',
            [
                'as'   => 'api.v1.LeaseDetailController.get_leases_for_property_group',
                'uses' => 'LeaseDetailController@get_leases_for_property_group',
            ]
        );

        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/leaseDetails/active',
            [
                'as'   => 'api.v1.LeaseDetailController.get_active_leases_for_property_group',
                'uses' => 'LeaseDetailController@get_active_leases_for_property_group',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/suiteDetails',
            [
                'as'   => 'api.v1.SuiteController.get_suites_for_property_group',
                'uses' => 'SuiteController@get_suites_for_property_group',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/suitelessLeaseDetails',
            [
                'as'   => 'api.v1.LeaseDetailController.get_leases_for_property_suiteless',
                'uses' => 'LeaseDetailController@get_leases_for_property_suiteless',
            ]
        );
        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/suitelessLeaseDetails',
            [
                'as'   => 'api.v1.LeaseDetailController.get_leases_for_property_group_suiteless',
                'uses' => 'LeaseDetailController@get_leases_for_property_group_suiteless',
            ]
        );

        /**  PropertyLeaseRollup*/
        Route::get(
            'clients/{client_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.PropertyLeaseRollupController.index',
                'uses' => 'PropertyLeaseRollupController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.PropertyLeaseRollupController.indexForProperty',
                'uses' => 'PropertyLeaseRollupController@indexForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.PropertyGroupLeaseRollupController.indexForPropertyGroup',
                'uses' => 'PropertyGroupLeaseRollupController@indexForPropertyGroup',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroupLeaseRollups',
            [
                'as'   => 'api.v1.PropertyGroupLeaseRollupController.index',
                'uses' => 'PropertyGroupLeaseRollupController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/propertyGroupLeaseRollups',
            [
                'as'   => 'api.v1.PropertyGroupLeaseRollupController.indexForPropertyGroup',
                'uses' => 'PropertyGroupLeaseRollupController@indexForPropertyGroup',
            ]
        );

        /** Tenants */

        Route::put(
            'clients/{client_id}/tenants/{tenant_id}',
            [
                'as'   => 'api.v1.TenantDetailController.update',
                'uses' => 'TenantDetailController@update',
            ]
        );
        /** TenantsTypes */
        Route::post(
            'clients/{client_id}/tenantIndustries',
            [
                'as'   => 'api.v1.TenantIndustryDetailController.store',
                'uses' => 'TenantIndustryDetailController@store',
            ]
        );
        Route::get(
            'clients/{client_id}/tenantIndustryDetails',
            [
                'as'   => 'api.v1.TenantIndustryDetailController.index',
                'uses' => 'TenantIndustryDetailController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/tenantIndustryDetails/{tenant_industry_id}',
            [
                'as'   => 'api.v1.TenantIndustryDetailController.show',
                'uses' => 'TenantIndustryDetailController@show',
            ]
        );
        Route::put(
            'clients/{client_id}/tenantIndustries/{tenant_industry_id}',
            [
                'as'   => 'api.v1.TenantIndustryDetailController.update',
                'uses' => 'TenantIndustryDetailController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/tenantIndustries/{tenant_industry_id}',
            [
                'as'   => 'api.v1.TenantIndustryDetailController.destroy',
                'uses' => 'TenantIndustryDetailController@destroy',
            ]
        );

        /** TenantAttributes */
        Route::post(
            'clients/{client_id}/tenantAttributes',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.store',
                'uses' => 'TenantAttributeDetailController@store',
            ]
        );
        Route::get(
            'clients/{client_id}/tenantAttributeDetails',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.index',
                'uses' => 'TenantAttributeDetailController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/tenantAttributeDetails/{tenant_attribute_id}',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.show',
                'uses' => 'TenantAttributeDetailController@show',
            ]
        );
        Route::put(
            'clients/{client_id}/tenantAttributes/{tenant_attribute_id}',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.update',
                'uses' => 'TenantAttributeDetailController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/tenantAttributes/{tenant_attribute_id}',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.destroy',
                'uses' => 'TenantAttributeDetailController@destroy',
            ]
        );

        /** TenantTenantAttributes */
        Route::post(
            'clients/{client_id}/tenants/{tenant_id}/tenantTenantAttributes',
            [
                'as'   => 'api.v1.TenantTenantAttributeDetailController.store',
                'uses' => 'TenantTenantAttributeDetailController@store',
            ]
        );
        Route::get(
            'clients/{client_id}/tenants/{tenant_id}/tenantAttributeDetails',
            [
                'as'   => 'api.v1.TenantAttributeDetailController.indexForTenant',
                'uses' => 'TenantAttributeDetailController@indexForTenant',
            ]
        );
        Route::delete(
            'clients/{client_id}/tenants/{tenant_id}/tenantTenantAttributes/{tenant_tenant_attribute_id}',
            [
                'as'   => 'api.v1.TenantTenantAttributeDetailController.destroy',
                'uses' => 'TenantTenantAttributeDetailController@destroy',
            ]
        );

        /** TenantDetails */
        Route::get(
            'clients/{client_id}/properties/{property_id}/tenantDetails',
            [
                'as'   => 'api.v1.TenantDetailController.indexForProperty',
                'uses' => 'TenantDetailController@indexForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/tenantDetails',
            [
                'as'   => 'api.v1.TenantDetailController.indexForPropertyGroup',
                'uses' => 'TenantDetailController@indexForPropertyGroup',
            ]
        );
        Route::get(
            'clients/{client_id}/tenantDetails/{tenant_id}',
            [
                'as'   => 'api.v1.TenantDetailController.show',
                'uses' => 'TenantDetailController@show',
            ]
        );
    }
);
