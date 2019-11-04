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
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        Route::get(
            'clients/{client_id}/properties/{property_id}/leaseDetails/{lease_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.show',
                'uses' => 'LeaseDetailDeprecatedController@show',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/suiteDetails/{suite_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.SuiteDeprecatedController.show',
                'uses' => 'SuiteDeprecatedController@show',
            ]
        );

        Route::get(
            'clients/{client_id}/properties/{property_id}/leaseDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_leases_for_property',
                'uses' => 'LeaseDetailDeprecatedController@get_leases_for_property',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/leaseDetails/active',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_active_leases_for_property',
                'uses' => 'LeaseDetailDeprecatedController@get_active_leases_for_property',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/suiteDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.SuiteDeprecatedController.get_suites_for_property',
                'uses' => 'SuiteDeprecatedController@get_suites_for_property',
            ]
        );

        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/leaseDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_leases_for_property_group',
                'uses' => 'LeaseDetailDeprecatedController@get_leases_for_property_group',
            ]
        );

        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/leaseDetails/active',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_active_leases_for_property_group',
                'uses' => 'LeaseDetailDeprecatedController@get_active_leases_for_property_group',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/suiteDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.SuiteDeprecatedController.get_suites_for_property_group',
                'uses' => 'SuiteDeprecatedController@get_suites_for_property_group',
            ]
        );

        Route::get(
            'clients/{client_id}/properties/{property_id}/suitelessleaseDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_leases_for_property_suiteless',
                'uses' => 'LeaseDetailDeprecatedController@get_leases_for_property_suiteless',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/suitelessLeaseDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LeaseDetailDeprecatedController.get_leases_for_property_group_suiteless',
                'uses' => 'LeaseDetailDeprecatedController@get_leases_for_property_group_suiteless',
            ]
        );

        /**  PropertyLeaseRollup*/
        Route::get(
            'clients/{client_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyLeaseRollupDeprecatedController.index',
                'uses' => 'PropertyLeaseRollupDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyLeaseRollupDeprecatedController.indexForProperty',
                'uses' => 'PropertyLeaseRollupDeprecatedController@indexForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/propertyLeaseRollups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupLeaseRollupDeprecatedController.indexForPropertyGroup',
                'uses' => 'PropertyGroupLeaseRollupDeprecatedController@indexForPropertyGroup',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroupLeaseRollups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupLeaseRollupDeprecatedController.index',
                'uses' => 'PropertyGroupLeaseRollupDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/propertyGroupLeaseRollups',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyGroupLeaseRollupDeprecatedController.indexForPropertyGroup',
                'uses' => 'PropertyGroupLeaseRollupDeprecatedController@indexForPropertyGroup',
            ]
        );
    }
);
