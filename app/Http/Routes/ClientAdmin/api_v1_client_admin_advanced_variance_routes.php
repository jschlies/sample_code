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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE's and Role::WAYPOINT_ASSOCIATE_ROLE's
 * and Role::CLIENT_ADMINISTRATIVE_USER_ROLE's. Note they are
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
                ]
            ),
        ],
    ],
    function ()
    {
        Route::post(
            '/clients/{client_id}/advancedVarianceThresholds',
            [
                'as'   => 'api.v1.AdvancedVarianceThresholdController.store',
                'uses' => 'AdvancedVarianceThresholdController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/advancedVarianceThresholds/{advanced_variance_threshold_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceThresholdController.update',
                'uses' => 'AdvancedVarianceThresholdController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/advancedVarianceThresholds/{advanced_variance_threshold_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceThresholdController.destroy',
                'uses' => 'AdvancedVarianceThresholdController@destroy',
            ]
        );

        /**
         * advancedVariances
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariances',
            [
                'as'   => 'api.v1.AdvancedVarianceController.index',
                'uses' => 'AdvancedVarianceController@index',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances',
            [
                'as'   => 'api.v1.AdvancedVarianceController.store',
                'uses' => 'AdvancedVarianceController@store',
            ]
        );
        /**
         * put is not allowed
         */
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/advancedVariances/{advanced_variance_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceController.destroy',
                'uses' => 'AdvancedVarianceController@destroy',
            ]
        );

        /**
         * NOTE NOTE NOTE that Laravel has issues with long param names so we shorten things here,
         * explanation_type_id vs. advanced_variance_explanation_type_id
         * Elsewhere, explanation_type_id may be refered to as advanced_variance_explanation_type_id
         */
        Route::delete(
            '/clients/{client_id}/advancedVarianceExplanationTypes/{explanation_type_id}',
            [
                'as'   => 'api.v1.AdvancedVarianceExplanationTypeController.destroy',
                'uses' => 'AdvancedVarianceExplanationTypeController@destroy',
            ]
        );

        /**
         * calculatedFields
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields',
            [
                'as'   => 'api.v1.CalculatedFieldController.store',
                'uses' => 'CalculatedFieldController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}',
            [
                'as'   => 'api.v1.CalculatedFieldController.update',
                'uses' => 'CalculatedFieldController@update',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields',
            [
                'as'   => 'api.v1.CalculatedFieldController.index',
                'uses' => 'CalculatedFieldController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id_arr}',
            [
                'as'   => 'api.v1.CalculatedFieldController.show',
                'uses' => 'CalculatedFieldController@show',
            ]
        )->where(['calculated_field_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}',
            [
                'as'   => 'api.v1.CalculatedFieldController.destroy',
                'uses' => 'CalculatedFieldController@destroy',
            ]
        );

        /**
         * CalculatedFieldEquation
         */
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations',
            [
                'as'   => 'api.v1.CalculatedFieldEquationController.store',
                'uses' => 'CalculatedFieldEquationController@store',
            ]
        );
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations',
            [
                'as'   => 'api.v1.CalculatedFieldEquationController.indexForCalculatedField',
                'uses' => 'CalculatedFieldEquationController@indexForCalculatedField',
            ]
        )->where(['calculated_field_equation_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations/{calculated_field_equation_id_arr}',
            [
                'as'   => 'api.v1.CalculatedFieldEquationController.index',
                'uses' => 'CalculatedFieldEquationController@index',
            ]
        )->where(['calculated_field_equation_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations/{calculated_field_equation_id}',
            [
                'as'   => 'api.v1.CalculatedFieldEquationController.destroy',
                'uses' => 'CalculatedFieldEquationController@destroy',
            ]
        );
        /**
         * CalculatedFieldEquationProperty
         */
        Route::get(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations/{calculated_field_equation_id}/calculatedFieldEquationProperties',
            [
                'as'   => 'api.v1.CalculatedFieldEquationPropertyController.index',
                'uses' => 'CalculatedFieldEquationPropertyController@index',
            ]
        );
        Route::post(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations/{calculated_field_equation_id}/calculatedFieldEquationProperties',
            [
                'as'   => 'api.v1.CalculatedFieldEquationPropertyController.store',
                'uses' => 'CalculatedFieldEquationPropertyController@store',
            ]
        );
        Route::delete(
            '/clients/{client_id}/reportTemplates/{report_template_id}/calculatedFields/{calculated_field_id}/calculatedFieldEquations/{calculated_field_equation_id}/calculatedFieldEquationProperties/{cfep_id}',
            [
                'as'   => 'api.v1.CalculatedFieldEquationPropertyController.destroy',
                'uses' => 'CalculatedFieldEquationPropertyController@destroy',
            ]
        );
    }
);
