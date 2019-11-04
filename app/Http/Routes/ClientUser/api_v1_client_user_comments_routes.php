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
        /**
         * boutique routes - opportunities
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/comments',
            [
                'as'   => 'api.v1.OpportunityCommentController.showOpportunityComments',
                'uses' => 'OpportunityCommentController@showOpportunityComments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/comments',
            [
                'as'   => 'api.v1.OpportunityCommentController.storeOpportunityComments',
                'uses' => 'OpportunityCommentController@storeOpportunityComments',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/comments/{comment_id}',
            [
                'as'   => 'api.v1.OpportunityCommentController.destroyOpportunityComment',
                'uses' => 'OpportunityCommentController@destroyOpportunityComment',
            ]
        );

        /**
         * boutique get routes - advancedVarianceLineItems
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/comments',
            [
                'as'   => 'api.v1.AdvancedVarianceCommentsController.showAdvancedVarianceLineItemComments',
                'uses' => 'AdvancedVarianceCommentsController@showAdvancedVarianceLineItemComments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/comments',
            [
                'as'   => 'api.v1.AdvancedVarianceCommentsController.storeAdvancedVarianceLineItemComments',
                'uses' => 'AdvancedVarianceCommentsController@storeAdvancedVarianceLineItemComments',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/comments/{comment_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceCommentsController.destroyAdvancedVarianceLineItemComment',
                'uses' => 'AdvancedVarianceCommentsController@destroyAdvancedVarianceLineItemComment',
            ]
        );
    }
);
