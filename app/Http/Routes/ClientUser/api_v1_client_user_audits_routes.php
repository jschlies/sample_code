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
            '/clients/{client_id}/audits',
            [
                'as'   => 'api.v1.ClientDetailController.showAudits',
                'uses' => 'ClientDetailController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/opportunities/{opportunity_id}/audits',
            [
                'as'   => 'api.v1.OpportunityController.showAudits',
                'uses' => 'OpportunityController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessLists/{access_list_id}/audits',
            [
                'as'   => 'api.v1.AccessListDetailController.showAudits',
                'uses' => 'AccessListDetailController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListDetail/{access_list_id}/audits',
            [
                'as'   => 'api.v1.AccessListDetailController.showAudits',
                'uses' => 'AccessListDetailController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListProperties/{access_list_property_id}/audits',
            [
                'as'   => 'api.v1.AccessListPropertyPublicController.showAudits',
                'uses' => 'AccessListPropertyPublicController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/accessListUsers/{access_list_user_id}/audits',
            [
                'as'   => 'api.v1.AccessListUserPublicController.showAudits',
                'uses' => 'AccessListUserPublicController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/audits',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.showAudits',
                'uses' => 'AdvancedVarianceDetailController@showAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/audits',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.showAdvancedVarianceLineItemAudits',
                'uses' => 'AdvancedVarianceDetailController@showAdvancedVarianceLineItemAudits',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceApprovals/{advanced_variance_approval_id}/audits',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.showAdvancedVarianceApprovalAudits',
                'uses' => 'AdvancedVarianceDetailController@showAdvancedVarianceApprovalAudits',
            ]
        );
    }
);
