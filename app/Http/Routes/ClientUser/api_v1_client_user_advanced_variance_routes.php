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
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/spreadsheet',
            [
                'as'   => 'api.v1.AdvancedVarianceSpreadsheetController.index',
                'uses' => 'AdvancedVarianceSpreadsheetController@index',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.show',
                'uses' => 'AdvancedVarianceDetailController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariancesDetail/{advanced_variance_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.showDetail',
                'uses' => 'AdvancedVarianceDetailController@showDetail',
            ]
        );

        /**
         * AdvancedVariance Reviewers
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/reviewers',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.storeReviewer',
                'uses' => 'AdvancedVarianceDetailController@storeReviewer',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/reviewers/{related_user_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.destroyReviewer',
                'uses' => 'AdvancedVarianceDetailController@destroyReviewer',
            ]
        );

        /**
         * approvals
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/approvals',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.storeApproval',
                'uses' => 'AdvancedVarianceDetailController@storeApproval',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/approvals/{advanced_variance_approval_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.destroyApproval',
                'uses' => 'AdvancedVarianceDetailController@destroyApproval',
            ]
        );

        /**
         * flagged
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/flagged',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.indexFlagged',
                'uses' => 'AdvancedVarianceDetailController@indexFlagged',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/flaggedManually',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.indexFlaggedManually',
                'uses' => 'AdvancedVarianceDetailController@indexFlaggedManually',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/flaggedByPolicy',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.indexFlaggedByPolicy',
                'uses' => 'AdvancedVarianceDetailController@indexFlaggedByPolicy',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/flag',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateFlagAdvancedVarianceLineItems',
                'uses' => 'AdvancedVarianceDetailController@updateFlagAdvancedVarianceLineItems',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/unflag',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateUnflagAdvancedVarianceLineItems',
                'uses' => 'AdvancedVarianceDetailController@updateUnflagAdvancedVarianceLineItems',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/resolve',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateResolveAdvancedVarianceLineItems',
                'uses' => 'AdvancedVarianceDetailController@updateResolveAdvancedVarianceLineItems',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/unresolve',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateUnresolveAdvancedVarianceLineItems',
                'uses' => 'AdvancedVarianceDetailController@updateUnresolveAdvancedVarianceLineItems',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/advancedVarianceLineItems/{advanced_variance_line_item_id}/explanation',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateExplanationAdvancedVarianceLineItems',
                'uses' => 'AdvancedVarianceDetailController@updateExplanationAdvancedVarianceLineItems',
            ]
        );

        /**
         * completion
         */

        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/lock',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateLock',
                'uses' => 'AdvancedVarianceDetailController@updateLock',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/unlock',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.updateUnlock',
                'uses' => 'AdvancedVarianceDetailController@updateUnlock',
            ]
        );

        /**
         * per property
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.indexAdvancedVariancesPerProperty',
                'uses' => 'AdvancedVarianceDetailController@indexAdvancedVariancesPerProperty',
            ]
        );

        /**
         * per propertyGroup
         */
        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/advancedVariances',
            [
                'as'   => 'api.v1.PropertyGroupPublicController.advanced_variance_by_property_group',
                'uses' => 'PropertyGroupPublicController@advanced_variance_by_property_group',
            ]
        );

        /**
         * per advancedVariances/triggerJobs client
         */
        Route::post(
            '/clients/{client_id}/advancedVariances/triggerJobs',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.advancedVariancesPerClientTriggerJobs',
                'uses' => 'AdvancedVarianceDetailController@advancedVariancesPerClientTriggerJobs',
            ]
        );

        /**
         * per advancedVariances/triggerJobs property
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/triggerJobs',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.advancedVariancesPerPropertyTriggerJobs',
                'uses' => 'AdvancedVarianceDetailController@advancedVariancesPerPropertyTriggerJobs',
            ]
        );

        /**
         * advancedVarianceThresholds
         */
        Route::get(
            '/clients/{client_id}/advancedVarianceThresholds',
            [
                'as'   => 'api.v1.AdvancedVarianceThresholdController.index',
                'uses' => 'AdvancedVarianceThresholdController@index',
            ]
        );

        /**
         * per advancedVarianceLineItems by property and nativeAccount
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr}/nativeAccount/{native_account_id_arr}/advancedVarianceLineItems',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.advanced_variance_line_items_by_property_id_native_account_id',
                'uses' => 'AdvancedVarianceDetailController@advanced_variance_line_items_by_property_id_native_account_id',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS])
             ->where(['native_account_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        /**
         * per advancedVarianceLineItems by property and reportTemplateAccountGroup
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr}/reportTemplateAccountGroup/{rtag_id_arr}/advancedVarianceLineItems',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.advanced_variance_line_items_by_property_id_report_template_account_group_id',
                'uses' => 'AdvancedVarianceDetailController@advanced_variance_line_items_by_property_id_report_template_account_group_id',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS])
             ->where(['rtag_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        /**
         * per advancedVarianceLineItems by property and calculatedField
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr}/calculatedField/{calculated_field_id_arr}/advancedVarianceLineItems',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailController.advanced_variance_line_items_by_property_id_calculated_field_id',
                'uses' => 'AdvancedVarianceDetailController@advanced_variance_line_items_by_property_id_calculated_field_id',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS])
             ->where(['calculated_field_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/advancedVarianceExplanationTypes',
            [
                'as'   => 'api.v1.AdvancedVarianceExplanationTypeController.index',
                'uses' => 'AdvancedVarianceExplanationTypeController@index',
            ]
        );

        /**
         * NOTE NOTE NOTE that Laravel has issues with long param names so we shorten things here, explanation_type_id vs. advanced_variance_explanation_type_id
         */
        Route::get(
            '/clients/{client_id}/advancedVarianceExplanationTypes/{explanation_type_arr}',
            [
                'as'   => 'api.v1.AdvancedVarianceExplanationTypeController.show',
                'uses' => 'AdvancedVarianceExplanationTypeController@show',
            ]
        )->where(['explanation_type_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        /**
         * uniqueAdvancedVarianceDates
         */
        Route::get(
            '/clients/{client_id}/uniqueAdvancedVarianceDates',
            [
                'as'   => 'api.v1.AdvancedVarianceController.uniqueAdvancedVarianceDatesForClient',
                'uses' => 'AdvancedVarianceController@uniqueAdvancedVarianceDatesForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr}/uniqueAdvancedVarianceDates',
            [
                'as'   => 'api.v1.AdvancedVarianceController.uniqueAdvancedVarianceDatesForProperties',
                'uses' => 'AdvancedVarianceController@uniqueAdvancedVarianceDatesForProperties',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/uniqueAdvancedVarianceDates',
            [
                'as'   => 'api.v1.AdvancedVarianceController.uniqueAdvancedVarianceDatesForPropertyGroup',
                'uses' => 'AdvancedVarianceController@uniqueAdvancedVarianceDatesForPropertyGroup',
            ]
        )->where(['property_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id}/advancedVariances/workflow',
            [
                'as'   => 'api.v1.AdvancedVarianceController.advancedVarianceWorkflow',
                'uses' => 'AdvancedVarianceController@advancedVarianceWorkflow',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}/workflow',
            [
                'as'   => 'api.v1.AdvancedVarianceController.advancedVarianceLineItemWorkflow',
                'uses' => 'AdvancedVarianceController@advancedVarianceLineItemWorkflow',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/reportTemplateAccountGroup/{report_template_account_group_id}/workflow',
            [
                'as'   => 'api.v1.AdvancedVarianceController.advancedVarianceLineItemRTAGWorkflow',
                'uses' => 'AdvancedVarianceController@advancedVarianceLineItemRTAGWorkflow',
            ]
        );

        Route::get(
            '/clients/{client_id}/properties/{property_id}/calculatedFields/{calculated_field_id}/workflow',
            [
                'as'   => 'api.v1.AdvancedVarianceController.advancedVarianceLineItemCalculatedFieldWorkflow',
                'uses' => 'AdvancedVarianceController@advancedVarianceLineItemCalculatedFieldWorkflow',
            ]
        );
    }
);
