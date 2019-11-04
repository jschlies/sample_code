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
         * @todo - work w/ Nick to get rid of this
         */
        Route::get(
            '/clients/{client_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.OpportunityController.showAttachments',
                'uses' => 'OpportunityController@showAttachments',
            ]
        );
        Route::post(
            '/clients/{client_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.OpportunityController.storeAttachments',
                'uses' => 'OpportunityController@storeAttachments',
            ]
        );

        /**
         * boutique routes - advancedVariances
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/attachments',
            [
                'as'   => 'api.v1.AdvancedVarianceAttachmentsController.showAdvancedVarianceAttachments',
                'uses' => 'AdvancedVarianceAttachmentsController@showAdvancedVarianceAttachments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/attachments',
            [
                'as'   => 'api.v1.AdvancedVarianceAttachmentsController.storeAdvancedVarianceAttachments',
                'uses' => 'AdvancedVarianceAttachmentsController@storeAdvancedVarianceAttachments',
            ]
        );

        /**
         * boutique routes - advancedVarianceLineItems
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/attachments',
            [
                'as'   => 'api.v1.showAdvancedVarianceLineItemAttachments.showAdvancedVarianceLineItemAttachments',
                'uses' => 'AdvancedVarianceAttachmentsController@showAdvancedVarianceLineItemAttachments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/attachments',
            [
                'as'   => 'api.v1.AdvancedVarianceAttachmentsController.storeAdvancedVarianceLineItemAttachments',
                'uses' => 'AdvancedVarianceAttachmentsController@storeAdvancedVarianceLineItemAttachments',
            ]
        );

        /**
         * boutique routes - opportunities
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.OpportunityAttachmentController.showOpportunityAttachments',
                'uses' => 'OpportunityAttachmentController@showOpportunityAttachments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.OpportunityAttachmentController.storeOpportunityAttachments',
                'uses' => 'OpportunityAttachmentController@storeOpportunityAttachments',
            ]
        );

        /**
         * boutique routes - property attachments
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/attachments',
            [
                'as'   => 'api.v1.PropertyAttachmentController.showPropertyAttachments',
                'uses' => 'PropertyAttachmentController@showPropertyAttachments',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/attachments',
            [
                'as'   => 'api.v1.PropertyAttachmentController.storePropertyAttachments',
                'uses' => 'PropertyAttachmentController@storePropertyAttachments',
            ]
        );

        /**
         * generic routes
         */
        Route::delete(
            '/clients/{client_id}/attachments/{attachment_id}',
            [
                'as'   => 'api.v1.AttachmentController.destroy',
                'uses' => 'AttachmentController@destroy',
            ]
        );

        /**
         * generic routes
         */
        Route::get(
            '/clients/{client_id}/attachments/{attachment_id}/download',
            [
                'as'   => 'api.v1.AttachmentController.downloadAttachment',
                'uses' => 'AttachmentController@downloadAttachment',
            ]
        );
    }
);
