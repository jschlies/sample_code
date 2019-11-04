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
            'clients/{client_id}/opportunities/{opportunity_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.showAudits',
                'uses' => 'OpportunityDeprecatedController@showAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/accessLists/{access_list_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListDetailDeprecatedController.showAudits',
                'uses' => 'AccessListDetailDeprecatedController@showAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/accessListProperties/{access_list_property_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListPropertyPublicDeprecatedController.showAudits',
                'uses' => 'AccessListPropertyPublicDeprecatedController@showAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/accessListUsers/{access_list_user_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AccessListUserPublicDeprecatedController.showAudits',
                'uses' => 'AccessListUserPublicDeprecatedController@showAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceDetailDeprecatedController.showAudits',
                'uses' => 'AdvancedVarianceDetailDeprecatedController@showAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceDetailDeprecatedController.showAdvancedVarianceLineItemAudits',
                'uses' => 'AdvancedVarianceDetailDeprecatedController@showAdvancedVarianceLineItemAudits',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceApprovals/{advanced_variance_approval_id}/audits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceDetailDeprecatedController.showAdvancedVarianceApprovalAudits',
                'uses' => 'AdvancedVarianceDetailDeprecatedController@showAdvancedVarianceApprovalAudits',
            ]
        );
    }
);
