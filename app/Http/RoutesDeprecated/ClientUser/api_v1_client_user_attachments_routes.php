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
        /**
         * @todo - work w/ Nick to get rid of this
         */
        Route::get(
            'clients/{client_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.showAttachments',
                'uses' => 'OpportunityDeprecatedController@showAttachments',
            ]
        );

        /**
         * boutique routes - advancedVariances
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceAttachmentsDeprecatedController.showAdvancedVarianceAttachments',
                'uses' => 'AdvancedVarianceAttachmentsDeprecatedController@showAdvancedVarianceAttachments',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceAttachmentsDeprecatedController.storeAdvancedVarianceAttachments',
                'uses' => 'AdvancedVarianceAttachmentsDeprecatedController@storeAdvancedVarianceAttachments',
            ]
        );

        /**
         * boutique routes - advancedVarianceLineItems
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceAttachmentsDeprecatedController.showAdvancedVarianceLineItemAttachments',
                'uses' => 'AdvancedVarianceAttachmentsDeprecatedController@showAdvancedVarianceLineItemAttachments',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVarianceAttachmentsDeprecatedController.storeAdvancedVarianceLineItemAttachments',
                'uses' => 'AdvancedVarianceAttachmentsDeprecatedController@storeAdvancedVarianceLineItemAttachments',
            ]
        );

        /**
         * boutique routes - opportunities
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityAttachmentDeprecatedController.showOpportunityAttachments',
                'uses' => 'OpportunityAttachmentDeprecatedController@showOpportunityAttachments',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/opportunities/{opportunity_id}/attachments',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityAttachmentDeprecatedController.storeOpportunityAttachments',
                'uses' => 'OpportunityAttachmentDeprecatedController@storeOpportunityAttachments',
            ]
        );

        /**
         * generic routes
         */
        Route::delete(
            'clients/{client_id}/attachments/{attachment_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AttachmentDeprecatedController.destroy',
                'uses' => 'AttachmentDeprecatedController@destroy',
            ]
        );

        /**
         * generic routes
         */
        Route::get(
            'attachments/{attachment_id}/download',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AttachmentDeprecatedController.downloadAttachment',
                'uses' => 'AttachmentDeprecatedController@downloadAttachment',
            ]
        );
    }
);
